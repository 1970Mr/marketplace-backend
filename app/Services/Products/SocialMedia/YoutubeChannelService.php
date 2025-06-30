<?php

namespace App\Services\Products\SocialMedia;

use App\Exceptions\OAuthException;
use App\Models\Products\Product;
use App\Models\Products\YoutubeChannel;
use App\Models\User;
use App\Services\Products\SocialMedia\Abstracts\BaseSocialMediaService;
use Google\Client;
use Google\Service\YouTube;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class YoutubeChannelService extends BaseSocialMediaService
{
    protected array $serviceSpecificFields = [
        'url', 'business_locations', 'business_age',
        'subscribers_count', 'monthly_revenue', 'monthly_views',
        'monetization_method', 'analytics_screenshot', 'listing_images'
    ];

    protected string $fileStoragePath = 'products/social_media/youtube';

    protected function updateOrCreateMedia(Product $product, array $mediaData): YoutubeChannel
    {
        $media = $product->productable;

        if ($media instanceof YoutubeChannel) {
            $media->update($mediaData);
        } else {
            $media = YoutubeChannel::create($mediaData);
            $product->productable()->associate($media);
            $product->save();
        }

        return $media->fresh(['product']);
    }

    /**
     * Normalize YouTube URL for better processing
     *
     * @param string $url
     * @return string
     */
    private function normalizeYouTubeUrl(string $url): string
    {
        // Add https if missing
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'https://' . $url;
        }

        // Ensure www.youtube.com format
        $url = preg_replace('/^https?:\/\/(www\.)?youtube\.com/', 'https://www.youtube.com', $url);

        // Remove trailing slash
        $url = rtrim($url, '/');

        // Remove common parameters
        $url = preg_replace('/\?.*$/', '', $url);

        return $url;
    }

    /**
     * Extract channel ID from YouTube URL
     *
     * @param string $url
     * @return string|null
     */
    private function extractChannelId(string $url): ?string
    {
        // If it's already a channel ID (starts with UC), return as is
        if (preg_match('/^UC[a-zA-Z0-9_-]{22}$/', $url)) {
            return $url;
        }

        // Normalize the URL first
        $normalizedUrl = $this->normalizeYouTubeUrl($url);

        Log::info('Extracting channel ID', [
            'original_url' => $url,
            'normalized_url' => $normalizedUrl
        ]);

        // Extract from various YouTube URL formats
        $patterns = [
            '/youtube\.com\/channel\/([a-zA-Z0-9_-]+)/',  // /channel/UC...
            '/youtube\.com\/c\/([a-zA-Z0-9_-]+)/',        // /c/username
            '/youtube\.com\/@([a-zA-Z0-9_-]+)/',          // /@username
            '/youtube\.com\/user\/([a-zA-Z0-9_-]+)/',     // /user/username
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $normalizedUrl, $matches)) {
                $identifier = $matches[1];

                Log::info('Pattern matched', [
                    'pattern' => $pattern,
                    'identifier' => $identifier
                ]);

                // If it's a channel ID, return it
                if (preg_match('/^UC[a-zA-Z0-9_-]{22}$/', $identifier)) {
                    return $identifier;
                }

                // For username/handle, we'll need to resolve it via API
                return $identifier;
            }
        }

        Log::warning('No pattern matched for URL', ['url' => $normalizedUrl]);
        return null;
    }

    /**
     * Resolve channel by username to get channel ID
     *
     * @param YouTube $youtube
     * @param string $username
     * @return string|null
     */
    private function resolveChannelByUsername(YouTube $youtube, string $username): ?string
    {
        try {
            // Try to search for the channel by username
            $response = $youtube->channels->listChannels('id', ['forUsername' => $username]);

            if ($items = $response->getItems()) {
                return $items[0]->getId();
            }

            // If username search fails, try search API
            $searchResponse = $youtube->search->listSearch('snippet', [
                'q' => $username,
                'type' => 'channel',
                'maxResults' => 1
            ]);

            if ($searchItems = $searchResponse->getItems()) {
                return $searchItems[0]->getSnippet()->getChannelId();
            }

        } catch (\Exception $e) {
            Log::warning('Failed to resolve YouTube username: ' . $username, ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Check if a channel matches the target identifier
     *
     * @param \Google\Service\YouTube\Channel $channel
     * @param string $targetIdentifier
     * @param string $originalUrl
     * @return bool
     */
    private function isChannelMatch(\Google\Service\YouTube\Channel $channel, string $targetIdentifier, string $originalUrl): bool
    {
        $channelId = $channel->getId();
        $snippet = $channel->getSnippet();

        // Direct ID match
        if ($channelId === $targetIdentifier) {
            return true;
        }

        // Custom URL match (if available)
        if ($snippet->getCustomUrl()) {
            $customUrl = $snippet->getCustomUrl();

            // Remove @ prefix if present
            $customUrl = ltrim($customUrl, '@');
            $targetIdentifier = ltrim($targetIdentifier, '@');

            if (strtolower($customUrl) === strtolower($targetIdentifier)) {
                return true;
            }
        }

        // Check various URL formats that might match
        $possibleUrls = [
            'https://www.youtube.com/channel/' . $channelId,
            'https://www.youtube.com/c/' . $targetIdentifier,
            'https://www.youtube.com/@' . $targetIdentifier,
            'https://www.youtube.com/user/' . $targetIdentifier,
        ];

        if ($snippet->getCustomUrl()) {
            $possibleUrls[] = 'https://www.youtube.com/@' . ltrim($snippet->getCustomUrl(), '@');
            $possibleUrls[] = 'https://www.youtube.com/c/' . ltrim($snippet->getCustomUrl(), '@');
        }

        $normalizedOriginal = $this->normalizeYouTubeUrl($originalUrl);

        foreach ($possibleUrls as $possibleUrl) {
            if ($this->normalizeYouTubeUrl($possibleUrl) === $normalizedOriginal) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify a YouTube channel by its URL or ID.
     *
     * @param string $channelUrl The YouTube channel URL or ID.
     * @return array|null The channel info or redirect URL.
     * @throws OAuthException If the token is expired or the channel is not found.
     */
    public function verify(string $channelUrl): ?array
    {
        /** @var User $user */
        $user = Auth::user();
        $googleProvider = $user->oauthProvider('google');

        $client = new Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));

        // If no OAuth record is found, redirect the user to Google's sign-in page
        if (!$googleProvider) {
            $client->setRedirectUri(config('services.google.redirect'));
            $client->setScopes([
                'openid',
                'https://www.googleapis.com/auth/youtube.readonly'
            ]);
            $client->setAccessType('offline');
            $client->setPrompt('consent');
            $client->setState(Auth::id());
            $client->setIncludeGrantedScopes(true);

            $authUrl = $client->createAuthUrl();

            return ['redirect_url' => $authUrl];
        }

        // Check if the token is still valid, and if not, attempt to refresh it.
        $client->setAccessToken($googleProvider->access_token);
        if ($client->isAccessTokenExpired()) {
            if ($googleProvider->refresh_token) {
                $newToken = $client->fetchAccessTokenWithRefreshToken($googleProvider->refresh_token);

                $googleProvider->update([
                    'access_token' => $newToken['access_token'],
                    'expires_at' => now()->addSeconds($newToken['expires_in']),
                    'refresh_token' => $newToken['refresh_token'] ?? $googleProvider->refresh_token
                ]);

                // Update client with new token
                $client->setAccessToken($newToken);
            } else {
                $googleProvider->delete();
                throw new OAuthException(
                    message: 'Authentication expired. Reauthorize required.',
                    errorCode: 'reauth_required',
                    code: 401
                );
            }
        }

        $youtube = new YouTube($client);

        // Extract channel ID from URL
        $channelIdentifier = $this->extractChannelId($channelUrl);

        if (!$channelIdentifier) {
            Log::error('Could not extract channel identifier from URL: ' . $channelUrl);
            throw new OAuthException(
                message: 'Invalid YouTube channel URL format.',
                errorCode: 'invalid_url',
                code: 400
            );
        }

        Log::info('Attempting to verify YouTube channel', [
            'original_url' => $channelUrl,
            'extracted_identifier' => $channelIdentifier
        ]);

        // Start with getting user's owned channels for comprehensive checking
        try {
            $userChannels = $youtube->channels->listChannels('id,snippet,statistics', ['mine' => true]);

            Log::info('Retrieved user channels', [
                'channel_count' => count($userChannels->getItems()),
                'user_id' => $user->id
            ]);

            // Check if any owned channel matches
            foreach ($userChannels->getItems() as $userChannel) {
                if ($this->isChannelMatch($userChannel, $channelIdentifier, $channelUrl)) {
                    Log::info('Channel verification successful via owned channels match', [
                        'channel_id' => $userChannel->getId(),
                        'channel_title' => $userChannel->getSnippet()->getTitle(),
                        'custom_url' => $userChannel->getSnippet()->getCustomUrl()
                    ]);

                    return ['channel_info' => $userChannel];
                }
            }

            // If no match found, try to resolve identifier to channel ID and check again
            if (!preg_match('/^UC[a-zA-Z0-9_-]{22}$/', $channelIdentifier)) {
                $resolvedChannelId = $this->resolveChannelByUsername($youtube, $channelIdentifier);

                if ($resolvedChannelId) {
                    foreach ($userChannels->getItems() as $userChannel) {
                        if ($userChannel->getId() === $resolvedChannelId) {
                            Log::info('Channel verification successful via resolved ID match', [
                                'resolved_channel_id' => $resolvedChannelId,
                                'channel_title' => $userChannel->getSnippet()->getTitle()
                            ]);

                            return ['channel_info' => $userChannel];
                        }
                    }
                }
            }

            // Log all owned channels for debugging
            $ownedChannelInfo = array_map(function($ch) {
                return [
                    'id' => $ch->getId(),
                    'title' => $ch->getSnippet()->getTitle(),
                    'custom_url' => $ch->getSnippet()->getCustomUrl()
                ];
            }, $userChannels->getItems());

            Log::error('Target channel not found in user\'s owned channels', [
                'target_identifier' => $channelIdentifier,
                'target_url' => $channelUrl,
                'user_id' => $user->id,
                'owned_channels' => $ownedChannelInfo
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch user\'s channels', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
        }

        throw new OAuthException(
            message: 'Channel not found in your YouTube account. Please ensure you own this channel and it matches your authenticated Google account.',
            errorCode: 'channel_not_owned',
            code: 404
        );
    }

    /**
     * Verify and mark a product as verified
     *
     * @param Product $product
     * @return array
     * @throws OAuthException
     */
    public function verifyProduct(Product $product): array
    {
        $channelUrl = $product->productable->url;
        Log::info('Starting product verification', [
            'product_id' => $product->id,
            'channel_url' => $channelUrl
        ]);

        $result = $this->verify($channelUrl);

        if (isset($result['channel_info'])) {
            // Mark product as verified
            $product->update(['is_verified' => true]);

            Log::info('Product verification completed successfully', [
                'product_id' => $product->id
            ]);

            return [
                'verified' => true,
                'channel_info' => $result['channel_info']
            ];
        }

        return $result;
    }
}

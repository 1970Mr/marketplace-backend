<?php

namespace App\Services\Products\SocialMedia;

use App\Exceptions\OAuthException;
use App\Models\Products\Product;
use App\Models\Products\YoutubeChannel;
use App\Services\Products\SocialMedia\Abstracts\BaseSocialMediaService;
use Google\Client;
use Google\Service\YouTube;

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
     * Verify a YouTube channel by its ID.
     *
     * @param string $channelId The unique YouTube channel ID.
     * @return array|null The channel info or redirect URL.
     * @throws OAuthException If the token is expired or the channel is not found.
     */
    public function verify(string $channelId): ?array
    {
        $user = auth()->user();
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
            $client->setState(auth()->id());
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
        $response = $youtube->channels->listChannels('id,snippet,statistics', ['id' => $channelId]);

        if ($items = $response->getItems()) {
            return ['channel_info' => $items[0]];
        }

        throw new OAuthException(
            message: 'Channel not found or unauthorized access.',
            errorCode: 'channel_not_found',
            code: 404
        );
    }
}

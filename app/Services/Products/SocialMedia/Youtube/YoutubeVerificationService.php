<?php

namespace App\Services\Products\SocialMedia\Youtube;

use App\Exceptions\OAuthException;
use App\Models\Products\Product;
use App\Models\User;
use Exception;
use Google\Client;
use Google\Service\YouTube;
use Google\Service\YouTube\Channel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class YoutubeVerificationService
{
    public function verifyProduct(Product $product): void
    {
        $channelUrl = $product->productable->url;
        $result = $this->verifyChannelOwnership($channelUrl);

        if (isset($result['redirect_url'])) {
            throw new OAuthException(
                message: 'Continue with Google.',
                errorCode: 'oauth_required',
                code: 302
            );
        }

        if (!isset($result['channel_info'])) {
            throw ValidationException::withMessages([
                'verification' => 'Channel verification failed'
            ]);
        }

        $product->update(['is_verified' => true]);
    }

    private function verifyChannelOwnership(string $channelUrl): array
    {
        /** @var User $user */
        $user = Auth::user();
        $googleProvider = $user->oauthProvider('google');

        $client = $this->createGoogleClient();

        if (!$googleProvider) {
            return $this->createOAuthRedirect($client);
        }

        $client = $this->refreshTokenIfNeeded($client, $googleProvider);
        $youtube = new YouTube($client);

        $channelIdentifier = $this->extractChannelId($channelUrl);
        if (!$channelIdentifier) {
            throw ValidationException::withMessages([
                'verification' => 'Invalid YouTube channel URL format'
            ]);
        }

        return $this->checkChannelOwnership($youtube, $channelIdentifier, $channelUrl);
    }

    private function createGoogleClient(): Client
    {
        $client = new Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        return $client;
    }

    private function createOAuthRedirect(Client $client): array
    {
        $client->setRedirectUri(config('services.google.redirect'));
        $client->setScopes([
            'openid',
            'https://www.googleapis.com/auth/youtube.readonly'
        ]);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setState(Auth::id());
        $client->setIncludeGrantedScopes(true);

        return ['redirect_url' => $client->createAuthUrl()];
    }

    private function refreshTokenIfNeeded(Client $client, $googleProvider): Client
    {
        $client->setAccessToken($googleProvider->access_token);

        if ($client->isAccessTokenExpired()) {
            if (!$googleProvider->refresh_token) {
                $googleProvider->delete();
                throw new OAuthException(
                    message: 'Authentication expired. Reauthorization required.',
                    errorCode: 'reauth_required',
                    code: 401
                );
            }

            $newToken = $client->fetchAccessTokenWithRefreshToken($googleProvider->refresh_token);
            $googleProvider->update([
                'access_token' => $newToken['access_token'],
                'expires_at' => now()->addSeconds($newToken['expires_in']),
                'refresh_token' => $newToken['refresh_token'] ?? $googleProvider->refresh_token
            ]);

            $client->setAccessToken($newToken);
        }

        return $client;
    }

    private function checkChannelOwnership(YouTube $youtube, string $channelIdentifier, string $channelUrl): array
    {
        $userChannels = $youtube->channels->listChannels('id,snippet,statistics', ['mine' => true]);

        foreach ($userChannels->getItems() as $userChannel) {
            if ($this->isChannelMatch($userChannel, $channelIdentifier, $channelUrl)) {
                return ['channel_info' => $userChannel];
            }
        }

        if (!preg_match('/^UC[a-zA-Z0-9_-]{22}$/', $channelIdentifier)) {
            $resolvedChannelId = $this->resolveChannelByUsername($youtube, $channelIdentifier);

            if ($resolvedChannelId) {
                foreach ($userChannels->getItems() as $userChannel) {
                    if ($userChannel->getId() === $resolvedChannelId) {
                        return ['channel_info' => $userChannel];
                    }
                }
            }
        }

        throw ValidationException::withMessages([
            'verification' => 'Channel not found in your YouTube account. Please ensure you own this channel.'
        ]);
    }

    private function extractChannelId(string $url): ?string
    {
        if (preg_match('/^UC[a-zA-Z0-9_-]{22}$/', $url)) {
            return $url;
        }

        $normalizedUrl = $this->normalizeYouTubeUrl($url);

        $patterns = [
            '/youtube\.com\/channel\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/c\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/@([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/user\/([a-zA-Z0-9_-]+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $normalizedUrl, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    private function normalizeYouTubeUrl(string $url): string
    {
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'https://' . $url;
        }

        $url = preg_replace('/^https?:\/\/(www\.)?youtube\.com/', 'https://www.youtube.com', $url);
        $url = rtrim($url, '/');
        return preg_replace('/\?.*$/', '', $url);
    }

    private function isChannelMatch(Channel $channel, string $targetIdentifier, string $originalUrl): bool
    {
        $channelId = $channel->getId();
        $snippet = $channel->getSnippet();

        if ($channelId === $targetIdentifier) {
            return true;
        }

        if ($snippet->getCustomUrl()) {
            $customUrl = ltrim($snippet->getCustomUrl(), '@');
            $targetIdentifier = ltrim($targetIdentifier, '@');

            if (strtolower($customUrl) === strtolower($targetIdentifier)) {
                return true;
            }
        }

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

    private function resolveChannelByUsername(YouTube $youtube, string $username): ?string
    {
        try {
            $response = $youtube->channels->listChannels('id', ['forUsername' => $username]);

            if ($items = $response->getItems()) {
                return $items[0]->getId();
            }

            $searchResponse = $youtube->search->listSearch('snippet', [
                'q' => $username,
                'type' => 'channel',
                'maxResults' => 1
            ]);

            if ($searchItems = $searchResponse->getItems()) {
                return $searchItems[0]->getSnippet()->getChannelId();
            }

        } catch (Exception) {
        }

        return null;
    }
}

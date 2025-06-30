<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Exceptions\OAuthException;
use App\Http\Controllers\Controller;
use Google\Client;
use Google\Service\YouTube;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class YoutubeAuthController extends Controller
{
    /**
     * Redirect the user to Google for authentication.
     *
     * @return JsonResponse The Google authentication URL.
     */
    public function redirectToGoogle() : JsonResponse
    {
        $userId = Auth::id();

        $client = self::createGoogleClient();
        $client->setScopes([
            'openid',
            'https://www.googleapis.com/auth/youtube.readonly'
        ]);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setState($userId);
        $client->setIncludeGrantedScopes(true);

        $authUrl = $client->createAuthUrl();

        return response()->json([
            'message' => __('Continue with Google.'),
            'data' => $authUrl
        ]);
    }

    /**
     * Handle Google OAuth callback and verify the user account.
     *
     * @param Request $request The incoming HTTP request.
     * @return RedirectResponse Redirect to frontend with result.
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            $client = self::createGoogleClient();

            $accessToken = $client->fetchAccessTokenWithAuthCode($request->code);

            #1 check the access token
            if (isset($accessToken['error'])) {
                return redirect()->to(
                    config('app.frontend_url') . '/products/social-media-account/youtube/oauth-callback?status=error&message=' .
                    urlencode($accessToken['error_description'] ?? 'Google authentication failed')
                );
            }

            #2 check the state parameter
            if (!$request->filled('state')) {
                return redirect()->to(
                    config('app.frontend_url') . '/products/social-media-account/youtube/oauth-callback?status=error&message=' .
                    urlencode('Missing state parameter')
                );
            }

            #3 check granted scopes
            $requiredScopes = [
                'openid',
                'https://www.googleapis.com/auth/youtube.readonly'
            ];
            $grantedScopes = explode(' ', $accessToken['scope'] ?? '');
            $missingScopes = array_diff($requiredScopes, $grantedScopes);

            if (!empty($missingScopes)) {
                return redirect()->to(
                    config('app.frontend_url') . '/products/social-media-account/youtube/oauth-callback?status=error&message=' .
                    urlencode('Missing required scopes: ' . implode(', ', $missingScopes))
                );
            }

            #4 Find user by state parameter
            $userId = $request->state;
            $user = \App\Models\User::find($userId);

            if (!$user) {
                return redirect()->to(
                    config('app.frontend_url') . '/products/social-media-account/youtube/oauth-callback?status=error&message=' .
                    urlencode('Invalid user state')
                );
            }

            #5 store access token
            $user->oauthProviders()->updateOrCreate(
                ['provider' => 'google'],
                [
                    'access_token' => $accessToken['access_token'],
                    'refresh_token' => $accessToken['refresh_token'] ?? null,
                    'expires_at' => now()->addSeconds($accessToken['expires_in']),
                ]
            );

            #6 get associated Youtube channels
            $client->setAccessToken($accessToken);
            $youtube = new YouTube($client);
            $channels = $youtube->channels->listChannels('snippet', ['mine' => true]);

            #7 return success redirect
            $channelsList = array_map(function ($channel) {
                return [
                    'id' => $channel->getId(),
                    'title' => $channel->getSnippet()->getTitle() ?? null
                ];
            }, $channels->getItems());

            return redirect()->to(
                config('app.frontend_url') . '/products/social-media-account/youtube/oauth-callback?status=success&message=' .
                urlencode('Account linked successfully') . '&channels=' .
                urlencode(json_encode($channelsList))
            );

        } catch (OAuthException $e) {
            return redirect()->to(
                config('app.frontend_url') . '/products/social-media-account/youtube/oauth-callback?status=error&message=' .
                urlencode($e->getMessage())
            );
        } catch (\Exception $e) {
            return redirect()->to(
                config('app.frontend_url') . '/products/social-media-account/youtube/oauth-callback?status=error&message=' .
                urlencode('OAuth failed: ' . $e->getMessage())
            );
        }
    }

    /**
     * Create and configure a Google client instance.
     *
     * @return Client The configured Google client.
     */
    private static function createGoogleClient(): Client
    {
        $config = config('services.google');
        $client = new Client();
        $client->setClientId($config['client_id']);
        $client->setClientSecret($config['client_secret']);
        $client->setRedirectUri($config['redirect']);
        return $client;
    }
}

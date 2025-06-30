<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Exceptions\OAuthException;
use App\Http\Controllers\Controller;
use Google\Client;
use Google\Service\YouTube;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class YoutubeAuthController extends Controller
{
    /**
     * Redirect the user to Google for authentication.
     *
     * @return JsonResponse The Google authentication URL.
     */
    public function redirectToGoogle() : JsonResponse
    {
        $userId = auth()->id();

        $client = $this->createGoogleClient();
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
     * @return JsonResponse The account verification result.
     */
    public function handleGoogleCallback(Request $request): JsonResponse
    {
        try {
            $client = $this->createGoogleClient();

            $accessToken = $client->fetchAccessTokenWithAuthCode($request->code);

            #1 check the access token
            if (isset($accessToken['error'])) {
                throw new OAuthException(
                    message: $accessToken['error_description'] ?? 'Google authentication failed',
                    errorCode: 'reauth_required',
                    code: 401
                );
            }

            #2 check the state parameter
            if (!$request->filled('state')) {
                throw new OAuthException(
                    message: 'Missing state parameter.',
                    errorCode: 'missing_state',
                    code: 400
                );
            }

            if ($request->state != auth()->id()) {
                throw new OAuthException(
                    message: 'Invalid state parameter.',
                    errorCode: 'invalid_state',
                    code: 400
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
                throw new OAuthException(
                    message: 'Missing required scopes: ' . implode(', ', $missingScopes),
                    errorCode: 'missing_scopes',
                    code: 400
                );
            }

            #4 store access token
            $user = auth()->user();
            $user->oauthProviders()->updateOrCreate(
                ['provider' => 'google'],
                [
                    'access_token' => $accessToken['access_token'],
                    'refresh_token' => $accessToken['refresh_token'] ?? null,
                    'expires_at' => now()->addSeconds($accessToken['expires_in']),
                ]
            );

            #5 get associated Youtube channels
            $client->setAccessToken($accessToken);
            $youtube = new YouTube($client);
            $channels = $youtube->channels->listChannels('snippet', ['mine' => true]);

            #6 return the result
            $channels_list = array_map(function ($channel) {
                return [
                    'id' => $channel->getId(),
                    'title' => $channel->getSnippet()->getTitle() ?? null
                ];
            }, $channels->getItems());

            return response()->json([
                'message' => __('Account linked successfully'),
                'data' => $channels_list
            ]);

        } catch (OAuthException $e) {
            return $e->render();
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'google_oauth_failed',
                'message' => 'failed to link to account ' . $e->getMessage(),
                'redirect_url' => isset($client) ? $client->createAuthUrl() : null
            ], 400);
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

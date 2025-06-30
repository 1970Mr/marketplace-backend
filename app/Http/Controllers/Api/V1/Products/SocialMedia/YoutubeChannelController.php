<?php

namespace App\Http\Controllers\Api\V1\Products\SocialMedia;

use App\Exceptions\OAuthException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Products\SocialMedia\YoutubeChannelRequest;
use App\Http\Resources\V1\Products\SocialMedia\YoutubeChannelResource;
use App\Models\Products\Product;
use App\Models\User;
use App\Services\Products\SocialMedia\YoutubeChannelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Google\Client;
use Google\Service\YouTube;
use Illuminate\Support\Facades\Auth;

class YoutubeChannelController extends Controller
{
    public function __construct(readonly private YoutubeChannelService $service)
    {
    }

    public function store(YoutubeChannelRequest $request): YoutubeChannelResource
    {
        $youtubeChannel = $this->service->storeOrUpdate($request->validated());
        return new YoutubeChannelResource($youtubeChannel);
    }

    /**
     * Get user's YouTube channels for debugging
     *
     * @return JsonResponse
     */
    public function getUserChannels(): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            $googleProvider = $user->oauthProvider('google');

            if (!$googleProvider) {
                return response()->json([
                    'message' => 'Google account not connected',
                    'data' => []
                ]);
            }

            $client = new Client();
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->setAccessToken($googleProvider->access_token);

            $youtube = new YouTube($client);
            $userChannels = $youtube->channels->listChannels('id,snippet,statistics', ['mine' => true]);

            $channels = array_map(function($channel) {
                return [
                    'id' => $channel->getId(),
                    'title' => $channel->getSnippet()->getTitle(),
                    'custom_url' => $channel->getSnippet()->getCustomUrl(),
                    'description' => substr($channel->getSnippet()->getDescription(), 0, 100) . '...',
                    'subscriber_count' => $channel->getStatistics()->getSubscriberCount(),
                    'video_count' => $channel->getStatistics()->getVideoCount(),
                    'view_count' => $channel->getStatistics()->getViewCount(),
                    'possible_urls' => [
                        'https://www.youtube.com/channel/' . $channel->getId(),
                        $channel->getSnippet()->getCustomUrl() ? 'https://www.youtube.com/@' . ltrim($channel->getSnippet()->getCustomUrl(), '@') : null,
                        $channel->getSnippet()->getCustomUrl() ? 'https://www.youtube.com/c/' . ltrim($channel->getSnippet()->getCustomUrl(), '@') : null,
                    ]
                ];
            }, $userChannels->getItems());

            return response()->json([
                'message' => 'User channels retrieved successfully',
                'data' => array_filter($channels)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve channels: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Verify a YouTube channel by ID.
     *
     * @param Product $product The product to verify.
     * @return JsonResponse The verification response.
     * @throws ValidationException If the verification fails.
     */
    public function verify(Product $product): JsonResponse
    {
        try {
            $result = $this->service->verifyProduct($product);

            if (isset($result['redirect_url'])) {
                return response()->json([
                    'message' => __('Continue with Google.'),
                    'data' => $result
                ]);
            }

            if (isset($result['verified']) && $result['verified']) {
                return response()->json([
                    'message' => __('Channel verified successfully'),
                    'data' => [
                        'verified' => true,
                        'channel_info' => $result['channel_info']
                    ]
                ]);
            }

            return response()->json([
                'message' => __('Channel verification completed'),
                'data' => $result
            ]);

        } catch (OAuthException $e) {
            logger($e->getMessage());
            return $e->render();
        } catch (\Exception $e) {
            logger($e->getMessage());
            throw ValidationException::withMessages([
                'verification' => $e->getMessage()
            ]);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Products\SocialMedia;

use App\Exceptions\OAuthException;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Products\SocialMedia\YoutubeChannelRequest;
use App\Http\Resources\V1\Products\SocialMedia\YoutubeChannelResource;
use App\Services\Products\SocialMedia\YoutubeChannelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
     * Verify a YouTube channel by ID.
     *
     * @param Request $request The incoming HTTP request.
     * @return JsonResponse The verification response.
     * @throws ValidationException If the verification fails.
     */
    public function verify(Request $request): JsonResponse
    {
        try {
            // pseudocode ...
            $channelId = $request->input('channelId');
            $result = $this->service->verify($channelId);

            if (isset($result['redirect_url'])) {
                return response()->json([
                    'message' => __('Continue with Google.'),
                    'data' => $result
                ]);
            }

            return response()->json([
                'message' => __('Account verified successfully'),
                'data' => $result
            ]);

        } catch (OAuthException $e) {
            return $e->render();
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'verification' => $e->getMessage()
            ]);
        }
    }
}

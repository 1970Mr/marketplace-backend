<?php

namespace App\Services\Products\SocialMedia\Tiktok;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class TiktokVerificationService
{
    protected string $scriptId = '__UNIVERSAL_DATA_FOR_REHYDRATION__';

    /**
     * Verify a TikTok account by checking the presence of a UUID in the profile bio.
     *
     * @param string $profileUrl TikTok profile URL
     * @param string $verificationCode UUID to be verified
     * @return array Verification result
     * @throws ValidationException
     */
    public function verify(string $profileUrl, string $verificationCode): array
    {
        try {
            $html = $this->fetchProfileHtml($profileUrl);
            $data = $this->extractJsonData($html);

            $userInfo = $data['userInfo']['user'] ?? [];
            $userStats = $data['userInfo']['stats'] ?? [];
            $bio = $userInfo['signature'] ?? '';

            $profileData = $this->mapProfileData($userInfo, $userStats);

            return [
                'contains_uuid' => str_contains($bio, $verificationCode),
                'profile' => $profileData,
            ];

        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'verification' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Fetch the raw HTML content of a TikTok profile page.
     *
     * @param string $profileUrl
     * @return string HTML content
     * @throws \Exception if the request fails
     */
    protected function fetchProfileHtml(string $profileUrl): string
    {
        $response = Http::withOptions([
            'verify' => false,
            'timeout' => 30,
        ])->get($profileUrl);

        if (!$response->ok()) {
            throw new \Exception("Failed to fetch TikTok profile. Status: {$response->status()}");
        }

        return $response->body();
    }

    /**
     * Extract the JSON data containing user profile information from TikTok HTML.
     *
     * @param string $html
     * @return array Extracted user data
     * @throws \Exception if the data cannot be extracted
     */
    protected function extractJsonData(string $html): array
    {
        $pattern = sprintf('/<script id="%s" type="application\/json">(.*?)<\/script>/s', $this->scriptId);
        if (preg_match($pattern, $html, $matches)) {
            $jsonString = $matches[1];
            $data = json_decode($jsonString, true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($data['__DEFAULT_SCOPE__']['webapp.user-detail'])) {
                throw new \Exception('Failed to parse TikTok data.');
            }

            return $data['__DEFAULT_SCOPE__']['webapp.user-detail'];
        }

        throw new \Exception('User detail script not found in the response.');
    }

    /**
     * Map TikTok user information and stats into a standardized format.
     *
     * @param array $userInfo User information array
     * @param array $userStats User statistics array
     * @return array Mapped profile data
     */
    protected function mapProfileData(array $userInfo, array $userStats): array
    {
        return [
            'username'          => $userInfo['uniqueId'] ?? '',
            'nickname'          => $userInfo['nickname'] ?? '',
            'biography'         => $userInfo['signature'] ?? '',
            'follower_count'    => $userStats['followerCount'] ?? 0,
            'following_count'   => $userStats['followingCount'] ?? 0,
            'video_count'       => $userStats['videoCount'] ?? 0,
            'like_count'        => $userStats['heart'] ?? 0,
            'profile_pic_url'   => $userInfo['avatarMedium'] ?? '',
            'is_verified'       => $userInfo['verified'] ?? false,
            'is_private'        => $userInfo['privateAccount'] ?? false,
        ];
    }
}

<?php
namespace App\Services\Products\SocialMedia\Instagram;

use Illuminate\Support\Arr;

class InstagramVerificationService
{
    public function __construct(protected InstagramClient $client) {}

    public function searchUser(string $username): ?string
    {
        $variables = [
            'data' => [
                'context' => 'blended', 'query' => $username, 'search_surface' => 'web_top_search',
            ],
            'hasQuery' => true,
        ];

        $result = $this->client->sendRequest(config('services.instagram.search_doc_id'), $variables);

        $users = data_get($result, 'data.xdt_api__v1__fbsearch__topsearch_connection.users', []);

        foreach ($users as $userObject) {
            if (data_get($userObject, 'user.username') === $username) {
                return data_get($userObject, 'user.id');
            }
        }

        return null;
    }

    public function getProfile(string $profileId): array
    {
        $variables = ['id' => $profileId, 'render_surface' => 'PROFILE'];
        $result = $this->client->sendRequest(config('services.instagram.profile_doc_id'), $variables);
        return data_get($result, 'data.user', []);
    }

    public function verifyAccount(string $username, string $verificationCode): array
    {
        try {
            $profileId = $this->searchUser($username);
            if (!$profileId) {
                throw new \Exception("Profile ID not found for username: {$username}");
            }

            $profile = $this->getProfile($profileId);
            if (empty($profile)) {
                throw new \Exception("Profile data not found for ID: {$profileId}");
            }

            $profileData = Arr::only($profile, [
                'username', 'full_name', 'biography', 'follower_count', 'following_count', 'media_count',
                'profile_pic_url', 'is_verified', 'is_business_account', 'is_private',
            ]);

            return [
                'contains_uuid' => str_contains(data_get($profile, 'biography', ''), $verificationCode),
                'profile' => $profileData,
            ];

        } catch (\Exception $e) {
            report($e);
            return ['contains_uuid' => false, 'error' => $e->getMessage()];
        }
    }
}

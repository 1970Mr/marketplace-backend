<?php

namespace App\Jobs;

use App\Notifications\InstagramTokenRefreshFailed;
use App\Services\Products\SocialMedia\Instagram\InstagramClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class RefreshInstagramTokenJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(InstagramClient $instagramClient): void
    {
        Log::info('Running scheduled job: RefreshInstagramTokenJob to refill pool...');

        try {
            // Call the new public method to refill the entire pool
            $newTokens = $instagramClient->refillTokenPool();
            Log::info('Scheduled job successfully refilled the token pool.', ['count' => count($newTokens)]);
        } catch (\Throwable $e) {
            Log::error('Scheduled job failed to refill the token pool.', ['exception' => $e]);

            $adminEmail = config('services.admin_notification_email');
            if ($adminEmail) {
                Notification::route('mail', $adminEmail)
                    ->notify(new InstagramTokenRefreshFailed($e));
            }
        }
    }
}

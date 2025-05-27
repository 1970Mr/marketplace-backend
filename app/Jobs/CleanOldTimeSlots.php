<?php

namespace App\Jobs;

use App\Models\TimeSlot;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

class CleanOldTimeSlots implements ShouldQueue
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
    public function handle(): void
    {
        TimeSlot::where('datetime', '<', Carbon::now()->subMonth())->delete();
    }
}

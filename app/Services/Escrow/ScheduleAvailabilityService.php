<?php

namespace App\Services\Escrow;

use App\Models\Admin;
use App\Models\TimeSlot;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class ScheduleAvailabilityService
{
    private const MAX_DAYS_TO_CHECK = 21;

    public function getNextAvailableSlots(Admin $admin): array
    {
        $this->generateSlotsIfNeeded($admin);
        $slots = $this->fetchAvailableSlots($admin);
        return $this->formatSlots($slots);
    }

    private function generateSlotsIfNeeded(Admin $admin): void
    {
        $daysAhead = self::MAX_DAYS_TO_CHECK;
        $startDate = Carbon::now()->addDay();
        $endDate = Carbon::now()->addDays($daysAhead);

        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $this->createSlotsForDate($admin, $date);
        }
    }

    private function createSlotsForDate(Admin $admin, Carbon $date): void
    {
        $workingDays = config('schedule.working_days');
        $weekday = strtolower($date->englishDayOfWeek);

        if (!in_array($weekday, $workingDays, true)) {
            return;
        }

        $workingHours = config('schedule.working_hours');

        foreach ($workingHours as $time) {
            $datetime = $date->copy()->setTimeFromTimeString($time);

            TimeSlot::firstOrCreate([
                'admin_id' => $admin->id,
                'datetime' => $datetime
            ]);
        }
    }

    private function fetchAvailableSlots(Admin $admin): Collection
    {
        return TimeSlot::where('admin_id', $admin->id)
            ->where('datetime', '>', now())
            ->whereDoesntHave('escrows')
            ->orderBy('datetime')
            ->get();
    }

    private function formatSlots(Collection $slots): array
    {
        return $slots->groupBy(fn($slot) => $slot->datetime->format('Y-m-d'))
            ->map(function ($group, $date) {
                return [
                    'date' => Carbon::parse($date)->format('F j'),
                    'times' => $group->map(fn($s) => [
                        'id' => $s->id,
                        'time' => $s->datetime->format('h:i A T')
                    ])
                        ->take(2)
                        ->values()
                ];
            })
            ->take(3)
            ->values()
            ->toArray();
    }
}

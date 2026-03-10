<?php

namespace App\Services\Booking;

use App\Models\Appointment;
use App\Models\StaffSchedule;
use Carbon\Carbon;

class AvailabilityService
{
    public function getAvailability(
        int $salonId,
        string $date,
        int $serviceId,
        ?int $staffId,
        int $slotMinutes = 15,
        string $timezone = 'Europe/Sarajevo'
    ): array {
        $day = Carbon::parse($date, $timezone)->startOfDay();
        $weekday = (int)$day->dayOfWeek;

        $slots = [];

        $staffIds = [];
        if ($staffId) {
            $staffIds = [$staffId];
        } else {
            // auto-staff: staff who have any schedule that day (weekday or date override)
            $staffIds = StaffSchedule::where('salon_id',$salonId)
                ->where(function($q) use ($weekday, $day) {
                    $q->where(function($q2) use ($weekday){
                        $q2->whereNull('date')->where('weekday',$weekday);
                    })->orWhereDate('date', $day->toDateString());
                })
                ->distinct()->pluck('staff_id')->toArray();
        }

        foreach ($staffIds as $sid) {
            $windows = StaffSchedule::where('salon_id',$salonId)
                ->where('staff_id',$sid)
                ->where(function($q) use ($weekday, $day) {
                    $q->where(function($q2) use ($weekday){
                        $q2->whereNull('date')->where('weekday',$weekday);
                    })->orWhereDate('date', $day->toDateString());
                })
                ->where('is_available', true)
                ->get();

            if ($windows->isEmpty()) continue;

            // bookings for that day (UTC range)
            $fromUtc = $day->clone()->utc();
            $toUtc = $day->clone()->endOfDay()->utc();

            $booked = Appointment::where('salon_id',$salonId)
                ->where('staff_id',$sid)
                ->whereNotIn('status',['cancelled'])
                ->whereBetween('start_at', [$fromUtc, $toUtc])
                ->orderBy('start_at')
                ->get(['start_at','end_at']);

            foreach ($windows as $w) {
                $startLocal = Carbon::parse($day->toDateString().' '.$w->start_time, $timezone);
                $endLocal = Carbon::parse($day->toDateString().' '.$w->end_time, $timezone);

                for ($t = $startLocal->clone(); $t->lt($endLocal); $t->addMinutes($slotMinutes)) {
                    $slotStartLocal = $t->clone();
                    $slotEndLocal = $t->clone()->addMinutes($slotMinutes);

                    if ($slotEndLocal->gt($endLocal)) break;

                    // convert slot to UTC for comparisons
                    $slotStartUtc = $slotStartLocal->clone()->utc();
                    $slotEndUtc = $slotEndLocal->clone()->utc();

                    $isFree = true;
                    foreach ($booked as $b) {
                        // overlap check in UTC
                        if ($slotStartUtc->lt($b->end_at) && $slotEndUtc->gt($b->start_at)) {
                            $isFree = false;
                            break;
                        }
                    }

                    if ($isFree) {
                        $slots[] = [
                            'staff_id' => (int)$sid,
                            'start_at' => $slotStartLocal->toIso8601String(),
                            'end_at' => $slotEndLocal->toIso8601String(),
                        ];
                    }
                }
            }
        }

        return $slots;
    }
}

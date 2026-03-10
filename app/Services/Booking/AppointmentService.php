<?php

namespace App\Services\Booking;

use App\Models\Appointment;
use App\Models\AppointmentEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    public function create(int $salonId, array $data, string $timezone = 'Europe/Sarajevo'): Appointment
    {
        return DB::transaction(function () use ($salonId, $data, $timezone) {

            [$startUtc, $endUtc] = $this->toUtcRange($data['start_at'], $data['end_at'], $timezone);

            $staffId = $data['staff_id'] ?? null;

            // Advisory lock (MySQL) — must succeed if available
            if ($staffId) $this->acquireLockOrThrow($salonId, (int)$staffId, $startUtc->toDateString());

            // overlap check
            if ($staffId) {
                $overlap = Appointment::where('salon_id',$salonId)
                    ->where('staff_id',(int)$staffId)
                    ->whereNotIn('status',['cancelled'])
                    ->where(function($q) use ($startUtc, $endUtc){
                        $q->whereBetween('start_at', [$startUtc, $endUtc])
                          ->orWhereBetween('end_at', [$startUtc, $endUtc])
                          ->orWhere(function($q2) use ($startUtc,$endUtc){
                                $q2->where('start_at','<=',$startUtc)->where('end_at','>=',$endUtc);
                          });
                    })->exists();

                if ($overlap) {
                    abort(409, 'Time slot already booked.');
                }
            }

            $appt = Appointment::create([
                'salon_id' => $salonId,
                'service_id' => (int)$data['service_id'],
                'staff_id' => $staffId ? (int)$staffId : null,
                'user_id' => $data['user_id'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'start_at' => $startUtc,
                'end_at' => $endUtc,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            AppointmentEvent::create([
                'appointment_id' => $appt->id,
                'event_type' => 'created',
                'payload' => [
                    'start_at' => $startUtc->toIso8601String(),
                    'end_at' => $endUtc->toIso8601String(),
                ],
            ]);

            return $appt;
        });
    }

    public function reschedule(Appointment $appt, array $data, string $timezone = 'Europe/Sarajevo'): Appointment
    {
        return DB::transaction(function () use ($appt, $data, $timezone) {

            [$startUtc, $endUtc] = $this->toUtcRange($data['start_at'], $data['end_at'], $timezone);

            if ($appt->staff_id) $this->acquireLockOrThrow($appt->salon_id, (int)$appt->staff_id, $startUtc->toDateString());

            $overlap = Appointment::where('salon_id',$appt->salon_id)
                ->where('staff_id',$appt->staff_id)
                ->where('id','!=',$appt->id)
                ->whereNotIn('status',['cancelled'])
                ->where(function($q) use ($startUtc, $endUtc){
                    $q->whereBetween('start_at', [$startUtc, $endUtc])
                      ->orWhereBetween('end_at', [$startUtc, $endUtc])
                      ->orWhere(function($q2) use ($startUtc,$endUtc){
                            $q2->where('start_at','<=',$startUtc)->where('end_at','>=',$endUtc);
                      });
                })->exists();

            if ($overlap) abort(409, 'Time slot already booked.');

            $appt->update([
                'start_at' => $startUtc,
                'end_at' => $endUtc,
                'notes' => $data['notes'] ?? $appt->notes,
            ]);

            AppointmentEvent::create([
                'appointment_id' => $appt->id,
                'event_type' => 'rescheduled',
                'payload' => [
                    'start_at' => $startUtc->toIso8601String(),
                    'end_at' => $endUtc->toIso8601String(),
                ],
            ]);

            return $appt;
        });
    }

    public function setStatus(Appointment $appt, string $status): Appointment
    {
        $current = $appt->status;

        $this->validateStatusTransition($current, $status);

        $appt->update(['status'=>$status]);

        AppointmentEvent::create([
            'appointment_id' => $appt->id,
            'event_type' => 'status_changed',
            'payload' => ['from'=>$current, 'to'=>$status],
        ]);

        return $appt;
    }

    private function validateStatusTransition(string $from, string $to): void
    {
        $from = strtolower($from);
        $to = strtolower($to);

        $allowed = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['completed', 'cancelled'],
            'completed' => [],
            'cancelled' => [],
        ];

        if (!array_key_exists($from, $allowed)) {
            abort(422, 'Invalid current appointment status.');
        }

        if (!in_array($to, $allowed[$from], true)) {
            abort(409, "Invalid status transition: {$from} -> {$to}");
        }
    }


    private function toUtcRange(string $startAt, string $endAt, string $timezone): array
    {
        // If input includes TZ, Carbon respects it; otherwise interpret in provided timezone.
        $start = Carbon::parse($startAt, $timezone);
        $end = Carbon::parse($endAt, $timezone);

        return [$start->utc(), $end->utc()];
    }

    private function acquireLockOrThrow(int $salonId, int $staffId, string $date): void
    {
        try {
            $key = "appt:{$salonId}:{$staffId}:{$date}";
            $row = DB::selectOne("SELECT GET_LOCK(?, 2) as l", [$key]);
            $ok = (int)($row->l ?? 0);
            if ($ok !== 1) abort(409, 'Booking busy, please retry.');
        } catch (\Throwable $e) {
            // If DB doesn't support GET_LOCK, do nothing; overlap check still protects.
        }
    }
}

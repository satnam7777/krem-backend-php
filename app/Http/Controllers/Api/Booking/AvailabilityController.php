<?php

namespace App\Http\Controllers\Api\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\AvailabilityRequest;
use App\Services\Booking\AvailabilityService;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function __invoke(AvailabilityRequest $request, AvailabilityService $svc)
    {
        $salon = $request->attributes->get('currentSalon');
        $data = $request->validated();

        $timezone = $data['timezone'] ?? ($salon->timezone ?? 'Europe/Sarajevo');

        $slots = $svc->getAvailability(
            salonId: $salon->id,
            date: $data['date'],
            serviceId: (int)$data['service_id'],
            staffId: $data['staff_id'] ? (int)$data['staff_id'] : null,
            slotMinutes: (int)($data['slot_minutes'] ?? 15),
            timezone: $timezone
        );

        return response()->json(['data'=>$slots]);
    }
}

<?php

namespace App\Http\Controllers\Api\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\AppointmentCreateRequest;
use App\Http\Requests\Booking\AppointmentRescheduleRequest;
use App\Http\Requests\Booking\AppointmentStatusRequest;
use App\Models\Appointment;
use App\Services\Booking\AppointmentService;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $salon = $request->attributes->get('currentSalon');

        $q = Appointment::query()->where('salon_id',$salon->id);

        if ($request->filled('status')) $q->where('status', $request->query('status'));
        if ($request->filled('staff_id')) $q->where('staff_id', (int)$request->query('staff_id'));
        if ($request->filled('from')) $q->where('start_at','>=', $request->query('from'));
        if ($request->filled('to')) $q->where('start_at','<=', $request->query('to'));

        return response()->json(['data'=>$q->orderByDesc('start_at')->paginate(50)]);
    }

    public function store(AppointmentCreateRequest $request, AppointmentService $svc)
    {
        $salon = $request->attributes->get('currentSalon');
        $data = $request->validated();
        $timezone = $data['timezone'] ?? ($salon->timezone ?? 'Europe/Sarajevo');

        $appt = $svc->create($salon->id, $data, $timezone);

        return response()->json(['data'=>$appt], 201);
    }

/**
 * PATCH /appointments/{id}
 * Alias for reschedule (keeps backward compatibility with POST /appointments/{id}/reschedule)
 */
public function update(AppointmentRescheduleRequest $request, AppointmentService $svc, $id)
{
    return $this->reschedule($request, $svc, $id);
}


    public function reschedule(AppointmentRescheduleRequest $request, AppointmentService $svc, $id)
    {
        $salon = $request->attributes->get('currentSalon');
        $appt = Appointment::where('salon_id',$salon->id)->findOrFail($id);

        $data = $request->validated();
        $timezone = $data['timezone'] ?? ($salon->timezone ?? 'Europe/Sarajevo');

        $appt = $svc->reschedule($appt, $data, $timezone);

        return response()->json(['data'=>$appt]);
    }

    public function setStatus(AppointmentStatusRequest $request, AppointmentService $svc, $id)
    {
        $salon = $request->attributes->get('currentSalon');
        $appt = Appointment::where('salon_id',$salon->id)->findOrFail($id);

        $appt = $svc->setStatus($appt, $request->validated()['status']);

        return response()->json(['data'=>$appt]);
    }
}

<?php

namespace App\Http\Controllers\Api\Schedule;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\StaffScheduleUpsertRequest;
use App\Models\StaffSchedule;
use Illuminate\Http\Request;

class StaffScheduleController extends Controller
{
    public function index(Request $request)
    {
        $salon = $request->attributes->get('currentSalon');

        $q = StaffSchedule::query()->where('salon_id', $salon->id);

        if ($request->filled('staff_id')) $q->where('staff_id', (int)$request->query('staff_id'));
        if ($request->filled('date')) $q->whereDate('date', $request->query('date'));

        return response()->json(['data' => $q->orderByDesc('id')->paginate(50)]);
    }

    public function store(StaffScheduleUpsertRequest $request)
    {
        $salon = $request->attributes->get('currentSalon');
        $data = $request->validated();

        $row = StaffSchedule::create(array_merge($data, [
            'salon_id' => $salon->id,
        ]));

        return response()->json(['data'=>$row], 201);
    }

    public function update(StaffScheduleUpsertRequest $request, $id)
    {
        $salon = $request->attributes->get('currentSalon');
        $row = StaffSchedule::where('salon_id',$salon->id)->findOrFail($id);

        $row->update($request->validated());

        return response()->json(['data'=>$row]);
    }

    public function destroy(Request $request, $id)
    {
        $salon = $request->attributes->get('currentSalon');
        $row = StaffSchedule::where('salon_id',$salon->id)->findOrFail($id);
        $row->delete();

        return response()->json(['ok'=>true]);
    }
}

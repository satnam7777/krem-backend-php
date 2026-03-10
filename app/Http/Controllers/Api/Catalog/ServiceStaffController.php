<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\ServiceStaffUpsertRequest;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceStaffController extends Controller
{
    public function listByService(Request $request, $serviceId)
    {
        $salon = $request->attributes->get('currentSalon');

        $service = Service::where('salon_id', $salon->id)->findOrFail($serviceId);

        $rows = DB::table('service_staff')
            ->join('staff', 'service_staff.staff_id', '=', 'staff.id')
            ->where('service_staff.salon_id', $salon->id)
            ->where('service_staff.service_id', $service->id)
            ->select([
                'service_staff.id',
                'service_staff.staff_id',
                'staff.name as staff_name',
                'service_staff.is_active',
                'service_staff.price_cents_override',
                'service_staff.duration_min_override',
                'service_staff.created_at',
                'service_staff.updated_at',
            ])
            ->orderBy('staff.name')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function upsert(ServiceStaffUpsertRequest $request, $serviceId)
    {
        $salon = $request->attributes->get('currentSalon');

        $service = Service::where('salon_id', $salon->id)->findOrFail($serviceId);
        $staff = Staff::where('salon_id', $salon->id)->findOrFail((int) $request->validated()['staff_id']);

        $data = $request->validated();

        $key = [
            'salon_id' => $salon->id,
            'service_id' => $service->id,
            'staff_id' => $staff->id,
        ];

        $values = [
            'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true,
            'price_cents_override' => $data['price_cents_override'] ?? null,
            'duration_min_override' => $data['duration_min_override'] ?? null,
            'updated_at' => now(),
        ];

        $existing = DB::table('service_staff')->where($key)->first();

        if ($existing) {
            DB::table('service_staff')->where('id', $existing->id)->update($values);
        } else {
            DB::table('service_staff')->insert(array_merge($key, $values, ['created_at' => now()]));
        }

        return response()->json(['ok' => true], 201);
    }

    public function remove(Request $request, $serviceId, $staffId)
    {
        $salon = $request->attributes->get('currentSalon');

        $service = Service::where('salon_id', $salon->id)->findOrFail($serviceId);
        $staff = Staff::where('salon_id', $salon->id)->findOrFail($staffId);

        DB::table('service_staff')
            ->where('salon_id', $salon->id)
            ->where('service_id', $service->id)
            ->where('staff_id', $staff->id)
            ->delete();

        return response()->json(['ok' => true]);
    }
}

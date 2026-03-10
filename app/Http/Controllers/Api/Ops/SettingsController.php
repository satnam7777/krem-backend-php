<?php

namespace App\Http\Controllers\Api\Ops;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ops\SettingUpsertRequest;
use App\Models\SalonSetting;
use App\Services\Ops\Auditor;
use App\Services\Ops\SettingsService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $salon = $request->attributes->get('currentSalon');
        $rows = SalonSetting::where('salon_id',$salon->id)->orderBy('key')->get();
        return response()->json(['data'=>$rows]);
    }

    public function upsert(SettingUpsertRequest $request, SettingsService $svc, Auditor $auditor)
    {
        $salon = $request->attributes->get('currentSalon');
        $data = $request->validated();

        $row = $svc->set($salon->id, $data['key'], $data['type'], $data['value'] ?? null);

        $auditor->log($request, 'salon.setting.upserted', ['key'=>$data['key'],'type'=>$data['type']]);

        return response()->json(['data'=>$row], 201);
    }
}

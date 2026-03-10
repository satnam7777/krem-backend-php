<?php

namespace App\Http\Controllers\Api\Ops;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ops\FlagUpsertRequest;
use App\Models\FeatureFlag;
use App\Services\Ops\Auditor;
use App\Services\Ops\FeatureFlags;
use Illuminate\Http\Request;

class FeatureFlagController extends Controller
{
    public function index(Request $request)
    {
        $salon = $request->attributes->get('currentSalon');
        $rows = FeatureFlag::where('salon_id',$salon->id)->orderBy('key')->get();
        return response()->json(['data'=>$rows]);
    }

    public function upsert(FlagUpsertRequest $request, FeatureFlags $flags, Auditor $auditor)
    {
        $salon = $request->attributes->get('currentSalon');
        $data = $request->validated();

        $row = $flags->set($salon->id, $data['key'], (bool)$data['enabled'], $data['meta'] ?? []);

        $auditor->log($request, 'salon.flag.upserted', ['key'=>$data['key'],'enabled'=>$data['enabled']]);

        return response()->json(['data'=>$row], 201);
    }
}

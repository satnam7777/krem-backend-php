<?php

namespace App\Http\Controllers\Api\Compliance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Compliance\GdprUserRequest;
use App\Models\User;
use App\Services\Compliance\GdprExportService;
use App\Services\Compliance\GdprAnonymizeService;
use Illuminate\Http\Request;

class GdprController extends Controller
{
    public function export(GdprUserRequest $request, GdprExportService $svc)
    {
        $actorId = $request->user()?->id;
        $reason = $request->input('reason');

        $user = User::findOrFail((int)$request->validated()['user_id']);
        return response()->json(['data'=>$svc->export($user, $actorId, $reason)]);
    }

    public function anonymize(GdprUserRequest $request, GdprAnonymizeService $svc)
    {
        $actorId = $request->user()?->id;
        $reason = $request->input('reason');

        $user = User::findOrFail((int)$request->validated()['user_id']);
        $svc->anonymize($user, $actorId, $reason);
        return response()->json(['ok'=>true]);
    }
}

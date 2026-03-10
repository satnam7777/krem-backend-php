<?php

namespace App\Http\Controllers\Api\Ops;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $salon = $request->attributes->get('currentSalon');

        $q = AuditLog::query()->where('salon_id',$salon->id);

        if ($request->filled('action')) $q->where('action',$request->query('action'));
        if ($request->filled('actor_user_id')) $q->where('actor_user_id',(int)$request->query('actor_user_id'));

        return response()->json(['data'=>$q->orderByDesc('id')->paginate(50)]);
    }
}

<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Webhooks\EmitTestRequest;
use App\Models\WebhookDelivery;
use App\Services\Webhooks\WebhookEmitter;
use Illuminate\Http\Request;

class WebhookDeliveryController extends Controller
{
    public function index(Request $request)
    {
        $salon = $request->attributes->get('currentSalon');
        $q = WebhookDelivery::where('salon_id',$salon->id);

        if ($request->filled('status')) $q->where('status',$request->query('status'));
        if ($request->filled('event')) $q->where('event',$request->query('event'));

        return response()->json(['data'=>$q->orderByDesc('id')->paginate(50)]);
    }

    public function test(EmitTestRequest $request, WebhookEmitter $emitter)
    {
        $salon = $request->attributes->get('currentSalon');
        $data = $request->validated();

        $count = $emitter->emit($salon->id, $data['event'], $data['payload']);

        return response()->json(['enqueued'=>$count]);
    }
}

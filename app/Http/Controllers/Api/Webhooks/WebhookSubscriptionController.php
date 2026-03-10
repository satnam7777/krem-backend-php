<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Webhooks\SubscriptionUpsertRequest;
use App\Models\WebhookSubscription;
use Illuminate\Http\Request;

class WebhookSubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $salon = $request->attributes->get('currentSalon');
        $rows = WebhookSubscription::where('salon_id',$salon->id)->orderByDesc('id')->paginate(50);
        return response()->json(['data'=>$rows]);
    }

    public function store(SubscriptionUpsertRequest $request)
    {
        $salon = $request->attributes->get('currentSalon');
        $data = $request->validated();

        $row = WebhookSubscription::create(array_merge($data, [
            'salon_id' => $salon->id,
        ]));

        return response()->json(['data'=>$row], 201);
    }

    public function update(SubscriptionUpsertRequest $request, $id)
    {
        $salon = $request->attributes->get('currentSalon');
        $row = WebhookSubscription::where('salon_id',$salon->id)->findOrFail($id);
        $row->update($request->validated());
        return response()->json(['data'=>$row]);
    }

    public function destroy(Request $request, $id)
    {
        $salon = $request->attributes->get('currentSalon');
        $row = WebhookSubscription::where('salon_id',$salon->id)->findOrFail($id);
        $row->delete();
        return response()->json(['ok'=>true]);
    }
}

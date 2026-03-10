<?php

namespace App\Http\Controllers\Api\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notifications\EnqueueNotificationRequest;
use App\Models\NotificationOutbox;
use Illuminate\Http\Request;

class OutboxController extends Controller
{
    public function index(Request $request)
    {
        $salon = $request->attributes->get('currentSalon');

        $q = NotificationOutbox::query()->where('salon_id', $salon->id);

        if ($request->filled('status')) $q->where('status', $request->query('status'));

        return response()->json(['data' => $q->orderByDesc('id')->paginate(50)]);
    }

    public function enqueue(EnqueueNotificationRequest $request)
    {
        $salon = $request->attributes->get('currentSalon');
        $data = $request->validated();

        $row = NotificationOutbox::create([
            'salon_id' => $salon->id,
            'user_id' => $data['user_id'] ?? null,
            'channel' => $data['channel'],
            'template' => $data['template'],
            'payload' => $data['payload'],
            'status' => 'pending',
            'attempts' => 0,
            'send_after' => $data['send_after'] ?? null,
        ]);

        return response()->json(['data' => $row], 201);
    }

    public function retry(Request $request, $id)
    {
        $salon = $request->attributes->get('currentSalon');
        $row = NotificationOutbox::where('salon_id',$salon->id)->findOrFail($id);

        $row->update([
            'status' => 'pending',
            'send_after' => null,
            'last_error' => null,
        ]);

        return response()->json(['ok'=>true]);
    }
}

<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\OrderCreateRequest;
use App\Http\Requests\Payments\OrderMarkPaidRequest;
use App\Models\Order;
use App\Services\Payments\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $salon = $request->attributes->get('currentSalon');

        $q = Order::query()->where('salon_id',$salon->id);
        if ($request->filled('status')) $q->where('status',$request->query('status'));

        return response()->json(['data'=>$q->orderByDesc('id')->paginate(50)]);
    }

    public function show(Request $request, $id)
    {
        $salon = $request->attributes->get('currentSalon');
        $order = Order::where('salon_id',$salon->id)->with('items')->findOrFail($id);
        return response()->json(['data'=>$order]);
    }

    public function store(OrderCreateRequest $request, OrderService $svc)
    {
        $salon = $request->attributes->get('currentSalon');
        $order = $svc->create($salon->id, $request->validated());
        return response()->json(['data'=>$order],201);
    }

    public function markPaid(OrderMarkPaidRequest $request, OrderService $svc, $id)
    {
        $salon = $request->attributes->get('currentSalon');
        $order = Order::where('salon_id',$salon->id)->findOrFail($id);

        $data = $request->validated();
        $order = $svc->markPaidManual($order, $data['note'] ?? null, $data['paid_at'] ?? null);

        return response()->json(['data'=>$order]);
    }
}

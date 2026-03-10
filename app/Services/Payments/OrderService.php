<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function create(int $salonId, array $data): Order
    {
        return DB::transaction(function () use ($salonId, $data) {

            $currency = $data['currency'] ?? 'EUR';
            $itemsIn = $data['items'];

            $total = 0;
            foreach ($itemsIn as $it) {
                $total += (int)$it['qty'] * (int)$it['unit_price_cents'];
            }

            $order = Order::create([
                'salon_id' => $salonId,
                'appointment_id' => $data['appointment_id'] ?? null,
                'user_id' => $data['user_id'] ?? null,
                'total_cents' => $total,
                'currency' => $currency,
                'status' => 'pending',
                'reference' => $data['reference'] ?? null,
                'meta' => null,
            ]);

            foreach ($itemsIn as $it) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'service_id' => $it['service_id'] ?? null,
                    'name' => $it['name'],
                    'qty' => (int)$it['qty'],
                    'unit_price_cents' => (int)$it['unit_price_cents'],
                    'total_cents' => (int)$it['qty'] * (int)$it['unit_price_cents'],
                ]);
            }

            return $order->load('items');
        });
    }

    public function markPaidManual(Order $order, ?string $note = null, ?string $paidAt = null): Order
    {
        return DB::transaction(function () use ($order, $note, $paidAt) {
            if ($order->status === 'paid') return $order;

            $paidAtDt = $paidAt ? Carbon::parse($paidAt) : now();

            $order->update([
                'status' => 'paid',
                'paid_at' => $paidAtDt,
                'meta' => array_merge($order->meta ?? [], $note ? ['manual_note'=>$note] : []),
            ]);

            PaymentTransaction::create([
                'salon_id' => $order->salon_id,
                'order_id' => $order->id,
                'payment_intent_id' => null,
                'provider' => 'manual',
                'provider_txn_id' => null,
                'amount_cents' => $order->total_cents,
                'currency' => $order->currency,
                'type' => 'charge',
                'status' => 'succeeded',
                'failure_reason' => null,
                'provider_payload' => $note ? ['note'=>$note] : null,
            ]);

            return $order;
        });
    }
}

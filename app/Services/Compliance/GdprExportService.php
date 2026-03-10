<?php

namespace App\Services\Compliance;

use App\Models\LegalLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GdprExportService
{
    public function export(User $user, ?int $actorUserId = null, ?string $reason = null): array
    {
        $orderIds = DB::table('orders')->where('user_id',$user->id)->pluck('id')->toArray();

        $data = [
            'user' => $user->only(['id','email','name','created_at','updated_at']),
            'appointments' => DB::table('appointments')->where('user_id',$user->id)->get(),
            'orders' => DB::table('orders')->where('user_id',$user->id)->get(),
            'transactions' => empty($orderIds)
                ? collect([])
                : DB::table('payment_transactions')->whereIn('order_id',$orderIds)->get(),
            'consents' => DB::table('user_consents')->where('user_id',$user->id)->get(),
        ];

        LegalLog::create([
            'user_id' => $user->id,
            'action' => 'gdpr.export',
            'meta' => [
                'actor_user_id' => $actorUserId,
                'reason' => $reason,
                'counts' => [
                    'appointments' => $data['appointments']->count(),
                    'orders' => $data['orders']->count(),
                    'transactions' => $data['transactions']->count(),
                    'consents' => $data['consents']->count(),
                ],
            ],
        ]);

        return $data;
    }
}

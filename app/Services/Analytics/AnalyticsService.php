<?php

namespace App\Services\Analytics;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function __construct(private AnalyticsCache $cache) {}

    public function summary(int $salonId, string $from, string $to, string $timezone = 'Europe/Sarajevo'): array
    {
        $key = "a:summary:{$salonId}:{$from}:{$to}:{$timezone}";
        $ttl = $this->cache->ttlSeconds();

        $fn = function () use ($salonId, $from, $to, $timezone) {
            [$fromUtc, $toUtc] = $this->rangeToUtc($from, $to, $timezone);

            $paidOrders = DB::table('orders')
                ->where('salon_id',$salonId)
                ->where('status','paid')
                ->whereBetween('paid_at', [$fromUtc, $toUtc])
                ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(total_cents),0) as sum_cents')
                ->first();

            $refunds = DB::table('payment_transactions')
                ->where('salon_id',$salonId)
                ->where('type','refund')
                ->where('status','succeeded')
                ->whereBetween('created_at', [$fromUtc, $toUtc])
                ->selectRaw('COALESCE(SUM(amount_cents),0) as refund_cents, COUNT(*) as refund_count')
                ->first();

            $appointments = DB::table('appointments')
                ->where('salon_id',$salonId)
                ->whereBetween('start_at', [$fromUtc, $toUtc])
                ->selectRaw(
                    "COUNT(*) as total,
                     SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) as cancelled,
                     SUM(CASE WHEN status='no_show' THEN 1 ELSE 0 END) as no_show,
                     SUM(CASE WHEN status='done' THEN 1 ELSE 0 END) as done,
                     SUM(CASE WHEN status='confirmed' THEN 1 ELSE 0 END) as confirmed,
                     SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending"
                )
                ->first();

            $revenueCents = (int)($paidOrders->sum_cents ?? 0);
            $orderCount = (int)($paidOrders->cnt ?? 0);
            $aov = $orderCount > 0 ? (int) round($revenueCents / $orderCount) : 0;

            $refundCents = (int)($refunds->refund_cents ?? 0);
            $netRevenueCents = $revenueCents - $refundCents;

            $apptTotal = (int)($appointments->total ?? 0);
            $cancelled = (int)($appointments->cancelled ?? 0);
            $noShow = (int)($appointments->no_show ?? 0);

            $cancelRate = $apptTotal > 0 ? $cancelled / $apptTotal : 0.0;
            $noShowRate = $apptTotal > 0 ? $noShow / $apptTotal : 0.0;

            return [
                'range' => ['from'=>$from,'to'=>$to,'timezone'=>$timezone],
                'revenue_cents' => $revenueCents,
                'refund_cents' => $refundCents,
                'net_revenue_cents' => $netRevenueCents,
                'orders_paid' => $orderCount,
                'aov_cents' => $aov,

                'appointments_total' => $apptTotal,
                'appointments_done' => (int)($appointments->done ?? 0),
                'appointments_confirmed' => (int)($appointments->confirmed ?? 0),
                'appointments_pending' => (int)($appointments->pending ?? 0),
                'appointments_cancelled' => $cancelled,
                'appointments_no_show' => $noShow,
                'cancel_rate' => $cancelRate,
                'no_show_rate' => $noShowRate,
            ];
        };

        if ($ttl === 0) return $fn();
        return Cache::remember($key, $ttl, $fn);
    }

    /**
     * Timezone-correct daily series.
     * Groups by salon-local date (not DB DATE()).
     */
    public function dailySeries(int $salonId, string $from, string $to, string $timezone = 'Europe/Sarajevo'): array
    {
        $key = "a:series:{$salonId}:{$from}:{$to}:{$timezone}";
        $ttl = $this->cache->ttlSeconds();

        $fn = function () use ($salonId, $from, $to, $timezone) {
            [$fromUtc, $toUtc] = $this->rangeToUtc($from, $to, $timezone);

            // Pull minimal rows and group in PHP by salon-local date.
            $orders = DB::table('orders')
                ->where('salon_id',$salonId)
                ->where('status','paid')
                ->whereBetween('paid_at', [$fromUtc, $toUtc])
                ->orderBy('paid_at')
                ->get(['paid_at','total_cents']);

            $appts = DB::table('appointments')
                ->where('salon_id',$salonId)
                ->whereBetween('start_at', [$fromUtc, $toUtc])
                ->orderBy('start_at')
                ->get(['start_at','status']);

            $map = [];

            foreach ($orders as $o) {
                $d = Carbon::parse($o->paid_at, 'UTC')->setTimezone($timezone)->toDateString();
                if (!isset($map[$d])) $map[$d] = $this->blankDay($d);
                $map[$d]['revenue_cents'] += (int)$o->total_cents;
                $map[$d]['orders'] += 1;
            }

            foreach ($appts as $a) {
                $d = Carbon::parse($a->start_at, 'UTC')->setTimezone($timezone)->toDateString();
                if (!isset($map[$d])) $map[$d] = $this->blankDay($d);
                $map[$d]['appointments'] += 1;
                if ($a->status === 'cancelled') $map[$d]['cancelled'] += 1;
                if ($a->status === 'no_show') $map[$d]['no_show'] += 1;
            }

            // Ensure full date range exists (continuous series).
            $cursor = Carbon::parse($from, $timezone)->startOfDay();
            $end = Carbon::parse($to, $timezone)->startOfDay();
            while ($cursor->lte($end)) {
                $d = $cursor->toDateString();
                if (!isset($map[$d])) $map[$d] = $this->blankDay($d);
                $cursor->addDay();
            }

            ksort($map);
            return array_values($map);
        };

        if ($ttl === 0) return $fn();
        return Cache::remember($key, $ttl, $fn);
    }

    public function staffPerformance(int $salonId, string $from, string $to, string $timezone = 'Europe/Sarajevo'): array
    {
        // unchanged from COMPLETE version
        $key = "a:staff:{$salonId}:{$from}:{$to}:{$timezone}";
        $ttl = $this->cache->ttlSeconds();

        $fn = function () use ($salonId, $from, $to, $timezone) {
            [$fromUtc, $toUtc] = $this->rangeToUtc($from, $to, $timezone);

            $appts = DB::table('appointments')
                ->join('staff','appointments.staff_id','=','staff.id')
                ->where('appointments.salon_id',$salonId)
                ->whereBetween('appointments.start_at', [$fromUtc, $toUtc])
                ->selectRaw("
                    staff.id as staff_id,
                    staff.name as staff_name,
                    COUNT(*) as appointments,
                    SUM(CASE WHEN appointments.status='done' THEN 1 ELSE 0 END) as done,
                    SUM(CASE WHEN appointments.status='cancelled' THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN appointments.status='no_show' THEN 1 ELSE 0 END) as no_show
                ")
                ->groupBy('staff.id','staff.name')
                ->orderBy('appointments','desc')
                ->get();

            $rev = DB::table('orders')
                ->join('appointments','orders.appointment_id','=','appointments.id')
                ->where('orders.salon_id',$salonId)
                ->where('orders.status','paid')
                ->whereNotNull('orders.appointment_id')
                ->whereBetween('orders.paid_at', [$fromUtc, $toUtc])
                ->selectRaw("
                    appointments.staff_id as staff_id,
                    COALESCE(SUM(orders.total_cents),0) as revenue_cents,
                    COUNT(*) as orders
                ")
                ->groupBy('appointments.staff_id')
                ->get()
                ->keyBy('staff_id');

            $out = [];
            foreach ($appts as $a) {
                $r = $rev[$a->staff_id] ?? null;
                $out[] = [
                    'staff_id' => (int)$a->staff_id,
                    'staff_name' => $a->staff_name,
                    'appointments' => (int)$a->appointments,
                    'done' => (int)$a->done,
                    'cancelled' => (int)$a->cancelled,
                    'no_show' => (int)$a->no_show,
                    'revenue_cents' => $r ? (int)$r->revenue_cents : 0,
                    'orders' => $r ? (int)$r->orders : 0,
                ];
            }

            return $out;
        };

        if ($ttl === 0) return $fn();
        return Cache::remember($key, $ttl, $fn);
    }

    public function servicePerformance(int $salonId, string $from, string $to, string $timezone = 'Europe/Sarajevo'): array
    {
        // unchanged from COMPLETE version
        $key = "a:service:{$salonId}:{$from}:{$to}:{$timezone}";
        $ttl = $this->cache->ttlSeconds();

        $fn = function () use ($salonId, $from, $to, $timezone) {
            [$fromUtc, $toUtc] = $this->rangeToUtc($from, $to, $timezone);

            $appts = DB::table('appointments')
                ->join('services','appointments.service_id','=','services.id')
                ->where('appointments.salon_id',$salonId)
                ->whereBetween('appointments.start_at', [$fromUtc, $toUtc])
                ->selectRaw("
                    services.id as service_id,
                    services.name as service_name,
                    COUNT(*) as appointments,
                    SUM(CASE WHEN appointments.status='done' THEN 1 ELSE 0 END) as done,
                    SUM(CASE WHEN appointments.status='cancelled' THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN appointments.status='no_show' THEN 1 ELSE 0 END) as no_show
                ")
                ->groupBy('services.id','services.name')
                ->orderBy('appointments','desc')
                ->get();

            $rev = DB::table('orders')
                ->join('appointments','orders.appointment_id','=','appointments.id')
                ->where('orders.salon_id',$salonId)
                ->where('orders.status','paid')
                ->whereNotNull('orders.appointment_id')
                ->whereBetween('orders.paid_at', [$fromUtc, $toUtc])
                ->selectRaw("
                    appointments.service_id as service_id,
                    COALESCE(SUM(orders.total_cents),0) as revenue_cents,
                    COUNT(*) as orders
                ")
                ->groupBy('appointments.service_id')
                ->get()
                ->keyBy('service_id');

            $out = [];
            foreach ($appts as $a) {
                $r = $rev[$a->service_id] ?? null;
                $out[] = [
                    'service_id' => (int)$a->service_id,
                    'service_name' => $a->service_name,
                    'appointments' => (int)$a->appointments,
                    'done' => (int)$a->done,
                    'cancelled' => (int)$a->cancelled,
                    'no_show' => (int)$a->no_show,
                    'revenue_cents' => $r ? (int)$r->revenue_cents : 0,
                    'orders' => $r ? (int)$r->orders : 0,
                ];
            }

            return $out;
        };

        if ($ttl === 0) return $fn();
        return Cache::remember($key, $ttl, $fn);
    }

    private function blankDay(string $date): array
    {
        return [
            'date' => $date,
            'revenue_cents' => 0,
            'orders' => 0,
            'appointments' => 0,
            'cancelled' => 0,
            'no_show' => 0,
        ];
    }

    private function rangeToUtc(string $from, string $to, string $timezone): array
    {
        $tz = $timezone ?: 'UTC';

        $fromTz = Carbon::parse($from, $tz)->startOfDay();
        $toTz = Carbon::parse($to, $tz)->endOfDay();

        return [$fromTz->clone()->utc(), $toTz->clone()->utc()];
    }
}

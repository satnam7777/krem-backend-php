<?php

namespace App\Services\Compliance;

use App\Models\LegalLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GdprAnonymizeService
{
    public function anonymize(User $user, ?int $actorUserId = null, ?string $reason = null): void
    {
        DB::transaction(function () use ($user, $actorUserId, $reason) {

            $anonEmail = 'anon+'.Str::random(12).'@example.com';
            $oldEmail = $user->email;

            // 1) User core fields
            $user->update([
                'email' => $anonEmail,
                'name' => 'Anonymized User',
            ]);

            // 2) Appointments (guest fields)
            DB::table('appointments')->where('user_id',$user->id)->update([
                'customer_name' => 'Anonymized',
                'customer_phone' => null,
                'customer_email' => null,
            ]);

            // 3) Orders meta: DB-portable merge
            $orders = DB::table('orders')->where('user_id',$user->id)->get(['id','meta']);
            foreach ($orders as $o) {
                $meta = $o->meta ? json_decode($o->meta, true) : [];
                if (!is_array($meta)) $meta = [];
                $meta['gdpr'] = 'anonymized';
                DB::table('orders')->where('id',$o->id)->update(['meta'=>json_encode($meta)]);
            }

            // 4) Notification outbox payload scrub (best-effort)
            if (DB::getSchemaBuilder()->hasTable('notification_outbox')) {
                $rows = DB::table('notification_outbox')->where('payload','like',"%{$oldEmail}%")->get(['id','payload']);
                foreach ($rows as $r) {
                    $payload = $r->payload ? json_decode($r->payload, true) : null;
                    if (is_array($payload)) {
                        $payload = $this->scrubArray($payload, $oldEmail, $anonEmail);
                        DB::table('notification_outbox')->where('id',$r->id)->update(['payload'=>json_encode($payload)]);
                    }
                }
            }

            // 5) Audit logs context scrub (best-effort)
            if (DB::getSchemaBuilder()->hasTable('audit_logs')) {
                $rows = DB::table('audit_logs')->where('context','like',"%{$oldEmail}%")->get(['id','context']);
                foreach ($rows as $r) {
                    $ctx = $r->context ? json_decode($r->context, true) : null;
                    if (is_array($ctx)) {
                        $ctx = $this->scrubArray($ctx, $oldEmail, $anonEmail);
                        DB::table('audit_logs')->where('id',$r->id)->update(['context'=>json_encode($ctx)]);
                    }
                }
            }

            LegalLog::create([
                'user_id' => $user->id,
                'action' => 'gdpr.anonymize',
                'meta' => [
                    'actor_user_id' => $actorUserId,
                    'reason' => $reason,
                    'old_email' => $oldEmail,
                    'new_email' => $anonEmail,
                ],
            ]);
        });
    }

    private function scrubArray(array $arr, string $oldEmail, string $newEmail): array
    {
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $arr[$k] = $this->scrubArray($v, $oldEmail, $newEmail);
            } elseif (is_string($v)) {
                $arr[$k] = str_replace($oldEmail, $newEmail, $v);
            }
        }
        return $arr;
    }
}

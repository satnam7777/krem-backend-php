<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TenantMembership;
use App\Models\User;
use App\Models\PlatformAuditLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TenantMembersController extends Controller
{
    private function currentTenantOrAbort(Request $request)
    {
        $tenant = $request->attributes->get('currentTenant');
        if (!$tenant) {
            abort(400, 'Tenant context missing');
        }
        return $tenant;
    }

    private function requireOwnerOrSuperadmin(Request $request, int $tenantId): void
    {
        $user = $request->user();

        if ($user?->is_superadmin) {
            return;
        }

        $membership = TenantMembership::where('tenant_id', $tenantId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$membership || $membership->role !== 'OWNER') {
            abort(403, 'Only OWNER or superadmin can manage members');
        }
    }

    public function index(Request $request)
    {
        $tenant = $this->currentTenantOrAbort($request);
        $this->requireOwnerOrSuperadmin($request, $tenant->id);

        $members = TenantMembership::query()
            ->where('tenant_id', $tenant->id)
            ->orderBy('id')
            ->get()
            ->map(function (TenantMembership $m) {
                $u = User::find($m->user_id);
                return [
                    'id' => $m->id,
                    'user_id' => $m->user_id,
                    'role' => $m->role,
                    'status' => $m->status,
                    'user' => $u ? [
                        'name' => $u->name,
                        'email' => $u->email,
                        'phone' => $u->phone,
                        'is_active' => (bool) $u->is_active,
                    ] : null,
                    'created_at' => $m->created_at,
                    'updated_at' => $m->updated_at,
                ];
            });

        return response()->json(['data' => $members]);
    }

    public function upsert(Request $request)
    {
        $tenant = $this->currentTenantOrAbort($request);
        $this->requireOwnerOrSuperadmin($request, $tenant->id);

        $data = $request->validate([
            'email' => ['required','email'],
            'role' => ['required', Rule::in(['OWNER','ADMIN','STAFF','RECEPTION'])],
            'status' => ['nullable', Rule::in(['active','suspended'])],
        ]);

        $user = User::where('email', strtolower($data['email']))->first();
        if (!$user) {
            abort(404, 'User not found');
        }

        $membership = TenantMembership::updateOrCreate(
            ['tenant_id' => $tenant->id, 'user_id' => $user->id],
            ['role' => $data['role'], 'status' => $data['status'] ?? 'active']
        );

        PlatformAuditLog::create([
            'tenant_id' => $tenant->id,
            'actor_user_id' => $request->user()->id,
            'action' => 'tenant_membership.upsert',
            'target_type' => 'user',
            'target_id' => (string) $user->id,
            'meta' => [
                'role' => $membership->role,
                'status' => $membership->status,
            ],
            'request_id' => $request->header('X-Request-Id'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['data' => [
            'id' => $membership->id,
            'tenant_id' => $membership->tenant_id,
            'user_id' => $membership->user_id,
            'role' => $membership->role,
            'status' => $membership->status,
        ]], 200);
    }

    public function destroy(Request $request, int $userId)
    {
        $tenant = $this->currentTenantOrAbort($request);
        $this->requireOwnerOrSuperadmin($request, $tenant->id);

        $actor = $request->user();
        if (!$actor->is_superadmin && $actor->id === $userId) {
            abort(409, 'Owner cannot remove self');
        }

        $deleted = TenantMembership::where('tenant_id', $tenant->id)
            ->where('user_id', $userId)
            ->delete();

        PlatformAuditLog::create([
            'tenant_id' => $tenant->id,
            'actor_user_id' => $actor->id,
            'action' => 'tenant_membership.remove',
            'target_type' => 'user',
            'target_id' => (string) $userId,
            'meta' => ['deleted' => $deleted],
            'request_id' => $request->header('X-Request-Id'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['data' => ['deleted' => (int)$deleted]], 200);
    }
}

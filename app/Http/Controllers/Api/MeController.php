<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TenantMembership;
use App\Models\Salon;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function me(Request $request)
    {
        $tenant = $request->attributes->get('currentTenant');
        $user = $request->user();

        $membership = null;
        if ($tenant && $user) {
            $membership = TenantMembership::where('tenant_id', $tenant->id)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();
        }

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'is_superadmin' => (bool) $user->is_superadmin,
                ],
                'tenant' => $tenant ? [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                ] : null,
                'membership' => $membership ? [
                    'role' => $membership->role,
                    'status' => $membership->status,
                ] : null,
            ],
        ]);
    }

    public function salons(Request $request)
    {
        $tenant = $request->attributes->get('currentTenant');
        $user = $request->user();

        if (!$tenant) {
            abort(400, 'Tenant context missing');
        }

        $membership = TenantMembership::where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$membership) {
            abort(403, 'Tenant access denied');
        }

        // Tenant DB query
        $salons = Salon::query()
            ->join('salon_members', 'salon_members.salon_id', '=', 'salons.id')
            ->where('salon_members.user_id', $user->id)
            ->where('salon_members.status', 'active')
            ->select(['salons.id','salons.name','salons.status'])
            ->orderBy('salons.name')
            ->get();

        return response()->json([
            'data' => [
                'role' => $membership->role,
                'salons' => $salons,
            ],
        ]);
    }
}

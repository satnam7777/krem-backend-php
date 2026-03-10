<?php

namespace App\Http\Middleware;

use App\Models\Salon;
use App\Models\SalonMember;
use Closure;
use Illuminate\Http\Request;

class ResolveSalon
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        $salonId = $request->header('X-Salon-Id');
        if (!$salonId) {
            abort(409, 'Salon not selected');
        }

        $salon = Salon::findOrFail($salonId);

        if ($salon->status === 'suspended') {
            // 423 Locked
            abort(423, 'Salon account suspended');
        }

        $membership = SalonMember::where('salon_id', $salonId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$membership) {
            abort(403, 'Access denied');
        }

        $request->attributes->set('currentSalon', $salon);
        $request->attributes->set('currentRole', $membership->role);

        return $next($request);
    }
}

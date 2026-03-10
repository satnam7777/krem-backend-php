<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Salon\CreateSalonRequest;
use App\Http\Requests\Salon\SwitchSalonRequest;
use App\Models\Salon;
use App\Models\SalonMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class SalonController extends Controller
{
    public function create(CreateSalonRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        return DB::transaction(function () use ($data, $user) {
            $name = $data['name'];
            $salon = Salon::create([
                'name' => $name,
                'slug' => Str::slug($name) . '-' . Str::lower(Str::random(4)),
                'currency' => $data['currency'] ?? 'EUR',
                'timezone' => $data['timezone'] ?? 'Europe/Sarajevo',
                'status' => 'active',
            ]);

            SalonMember::create([
                'salon_id' => $salon->id,
                'user_id' => $user->id,
                'role' => 'owner',
                'status' => 'active',
                'joined_at' => now(),
            ]);

                        return response()->json(['data' => $salon], 201);
        });
    }

    public function switch(SwitchSalonRequest $request)
    {
        $salonId = (int) $request->validated()['salon_id'];
        $user = $request->user();

        $ok = SalonMember::where('salon_id', $salonId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if (!$ok) {
            abort(403, 'Access denied');
        }

        return response()->json(['ok' => true]);
    }

    public function current(Request $request)
    {
        $salon = $request->attributes->get('currentSalon');
        $role = $request->attributes->get('currentRole');

        return response()->json([
            'salon' => $salon,
            'role' => $role,
        ]);
    }
}

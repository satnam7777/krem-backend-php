<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invite\CreateInviteRequest;
use App\Http\Requests\Invite\AcceptInviteRequest;
use App\Models\SalonInvite;
use App\Services\SalonInviteService;
use Illuminate\Http\Request;

class SalonInviteController extends Controller
{
    public function invite(CreateInviteRequest $request, SalonInviteService $service)
    {
        $salon = $request->attributes->get('currentSalon');
        $actor = $request->user();
        $data = $request->validated();

        $token = $service->createInvite(
            $salon->id,
            $data['email'],
            $data['role'],
            $actor->id
        );

        $frontend = config('app.frontend_url') ?? env('FRONTEND_URL') ?? '';
        $frontend = rtrim($frontend, '/');

        return response()->json([
            'invite_link' => $frontend ? ($frontend . '/invite/' . $token) : $token,
            'expires_at' => now()->addDays(7)->toISOString(),
        ], 201);
    }

    public function accept(AcceptInviteRequest $request, SalonInviteService $service)
    {
        $data = $request->validated();
        $user = $service->acceptInvite($data['token'], $data['password'] ?? null);

        return response()->json(['user' => $user]);
    }

    public function list(Request $request)
    {
        $salon = $request->attributes->get('currentSalon');

        $rows = SalonInvite::where('salon_id', $salon->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $rows]);
    }
}

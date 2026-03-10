<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\SalonMember;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $creds = $request->validated();

        if (!Auth::attempt($creds)) {
            abort(401, 'Invalid credentials');
        }

        /** @var \App\Models\User $user */
        $user = $request->user();

        return response()->json([
            'token' => $user->createToken('spa')->plainTextToken,
            'user' => $user,
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        $salons = SalonMember::where('user_id', $user->id)
            ->with('salon:id,name,slug,status,currency,timezone')
            ->get();

        return response()->json([
            'user' => $user,
            'salons' => $salons,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['ok' => true]);
    }
}

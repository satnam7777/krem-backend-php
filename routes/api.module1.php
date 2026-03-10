<?php
/**
 * Module 1 routes — merge into routes/api.php
 */

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SalonController;
use App\Http\Controllers\Api\SalonInviteController;
use Illuminate\Support\Facades\Route;

Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum','requireTenantMembership'])->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    Route::post('salons', [SalonController::class, 'create']);
    Route::post('salons/switch', [SalonController::class, 'switch']);

    Route::middleware(['resolveSalon'])->group(function () {
        Route::get('salons/current', [SalonController::class, 'current']);

        Route::middleware(['requireRole:owner,admin'])->group(function () {
            Route::post('salons/current/invites', [SalonInviteController::class, 'invite']);
            Route::get('salons/current/invites', [SalonInviteController::class, 'list']);
        });
    });
});

// Invite acceptance is public (token-based)
Route::post('invites/accept', [SalonInviteController::class, 'accept']);

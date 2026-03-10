<?php
/**
 * Module 7 routes — merge into routes/api.php
 */

use App\Http\Controllers\Api\Ops\AuditLogController;
use App\Http\Controllers\Api\Ops\SettingsController;
use App\Http\Controllers\Api\Ops\FeatureFlagController;
use App\Http\Controllers\Api\SuperAdmin\SuperAdminController;
use Illuminate\Support\Facades\Route;

// Salon ops (owner/admin)
Route::middleware(['auth:sanctum', 'requireTenantMembership','resolveSalon','requireRole:owner,admin'])->group(function () {
    Route::get('ops/audit-logs', [AuditLogController::class,'index']);
    Route::get('ops/settings', [SettingsController::class,'index']);
    Route::post('ops/settings', [SettingsController::class,'upsert']);
    Route::get('ops/feature-flags', [FeatureFlagController::class,'index']);
    Route::post('ops/feature-flags', [FeatureFlagController::class,'upsert']);
});

// SuperAdmin (global, no salon context required)
Route::middleware(['auth:sanctum', 'requireTenantMembership','requireSuperAdmin'])->group(function () {
    Route::get('superadmin/stats', [SuperAdminController::class,'stats']);
    Route::get('superadmin/salons', [SuperAdminController::class,'salons']);
    Route::get('superadmin/users', [SuperAdminController::class,'users']);
    Route::post('superadmin/salons/{id}/suspend', [SuperAdminController::class,'suspendSalon']);
    Route::post('superadmin/salons/{id}/unsuspend', [SuperAdminController::class,'unsuspendSalon']);
});

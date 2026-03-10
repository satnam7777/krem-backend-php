<?php
/**
 * Module 2 routes — merge into routes/api.php
 */

use App\Http\Controllers\Api\Catalog\ServiceController;
use App\Http\Controllers\Api\Catalog\StaffController;
use App\Http\Controllers\Api\Catalog\ServiceStaffController;
use Illuminate\Support\Facades\Route;

// All catalog routes are tenant-aware
Route::middleware(['auth:sanctum', 'requireTenantMembership','resolveSalon'])->group(function () {

    // Public-ish (still authenticated): read-only
    Route::get('catalog/services', [ServiceController::class, 'index']);
    Route::get('catalog/services/{id}', [ServiceController::class, 'show']);
    Route::get('catalog/staff', [StaffController::class, 'index']);
    Route::get('catalog/staff/{id}', [StaffController::class, 'show']);

    // Admin write access
    Route::middleware(['requireRole:owner,admin'])->group(function () {
        Route::post('catalog/services', [ServiceController::class, 'store']);
        Route::patch('catalog/services/{id}', [ServiceController::class, 'update']);
        Route::delete('catalog/services/{id}', [ServiceController::class, 'destroy']);

        Route::post('catalog/staff', [StaffController::class, 'store']);
        Route::patch('catalog/staff/{id}', [StaffController::class, 'update']);
        Route::delete('catalog/staff/{id}', [StaffController::class, 'destroy']);

        // Service↔Staff mapping
        Route::get('catalog/services/{serviceId}/staff', [ServiceStaffController::class, 'listByService']);
        Route::put('catalog/services/{serviceId}/staff', [ServiceStaffController::class, 'upsert']);
        Route::delete('catalog/services/{serviceId}/staff/{staffId}', [ServiceStaffController::class, 'remove']);
    });
});

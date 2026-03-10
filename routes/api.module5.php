<?php
/**
 * Module 5 routes — merge into routes/api.php
 */

use App\Http\Controllers\Api\Payments\OrderController;
use App\Http\Controllers\Api\Payments\WebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'requireTenantMembership','resolveSalon'])->group(function () {
    Route::middleware(['requireRole:owner,admin'])->group(function () {
        Route::get('payments/orders', [OrderController::class,'index']);
        Route::get('payments/orders/{id}', [OrderController::class,'show']);
        Route::post('payments/orders', [OrderController::class,'store']);
        Route::post('payments/orders/{id}/mark-paid', [OrderController::class,'markPaid']);
    });
});

Route::post('payments/webhooks', [WebhookController::class,'receive']);

<?php
/**
 * Module 9 routes — merge into routes/api.php
 */

use App\Http\Controllers\Api\Webhooks\WebhookSubscriptionController;
use App\Http\Controllers\Api\Webhooks\WebhookDeliveryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'requireTenantMembership','resolveSalon','requireRole:owner,admin'])->group(function () {
    // Subscriptions
    Route::get('integrations/webhooks', [WebhookSubscriptionController::class,'index']);
    Route::post('integrations/webhooks', [WebhookSubscriptionController::class,'store']);
    Route::put('integrations/webhooks/{id}', [WebhookSubscriptionController::class,'update']);
    Route::delete('integrations/webhooks/{id}', [WebhookSubscriptionController::class,'destroy']);

    // Deliveries (debug) + test emit
    Route::get('integrations/webhook-deliveries', [WebhookDeliveryController::class,'index']);
    Route::post('integrations/webhooks/test', [WebhookDeliveryController::class,'test']);
});

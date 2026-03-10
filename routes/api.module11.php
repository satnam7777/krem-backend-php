<?php
/**
 * Module 11 routes — Clients & Medical (tenant + salon scoped)
 */

use App\Http\Controllers\Api\Clients\ClientController;
use App\Http\Controllers\Api\Clients\ClientConsentController;
use App\Http\Controllers\Api\Clients\ClientMedicalController;
use App\Http\Controllers\Api\Clients\ClientTreatmentController;
use App\Http\Controllers\Api\Clients\ClientAttachmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'requireTenantMembership','resolveSalon'])->prefix('clients')->group(function () {
    Route::get('/', [ClientController::class, 'index']);
    Route::post('/', [ClientController::class, 'store']);
    Route::get('/{client}', [ClientController::class, 'show']);
    Route::patch('/{client}', [ClientController::class, 'update']);
    Route::delete('/{client}', [ClientController::class, 'archive']);

    Route::post('/{client}/consents', [ClientConsentController::class, 'store']);

    Route::get('/{client}/medical', [ClientMedicalController::class, 'show']);
    Route::put('/{client}/medical', [ClientMedicalController::class, 'upsert']);

    Route::get('/{client}/treatments', [ClientTreatmentController::class, 'index']);
    Route::post('/{client}/treatments', [ClientTreatmentController::class, 'store']);

    Route::post('/{client}/attachments', [ClientAttachmentController::class, 'upload']);
});

Route::middleware(['auth:sanctum', 'requireTenantMembership','resolveSalon'])->group(function () {
    Route::get('/attachments/{attachment}/download', [ClientAttachmentController::class, 'download']);
});

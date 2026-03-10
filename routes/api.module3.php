<?php
/**
 * Module 3 routes — merge into routes/api.php
 */

use App\Http\Controllers\Api\Schedule\StaffScheduleController;
use App\Http\Controllers\Api\Booking\AvailabilityController;
use App\Http\Controllers\Api\Booking\AppointmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'requireTenantMembership','resolveSalon'])->group(function () {

    // Admin/Owner schedule management
    Route::middleware(['requireRole:owner,admin'])->group(function () {
        Route::get('schedules', [StaffScheduleController::class, 'index']);
        Route::post('schedules', [StaffScheduleController::class, 'store']);
        Route::put('schedules/{id}', [StaffScheduleController::class, 'update']);
        Route::delete('schedules/{id}', [StaffScheduleController::class, 'destroy']);
    });

    // Availability
    Route::get('availability', AvailabilityController::class);

    // Appointments
    Route::middleware(['requireRole:owner,admin,staff'])->group(function () {
        Route::get('appointments', [AppointmentController::class, 'index']);
        Route::post('appointments', [AppointmentController::class, 'store']);
        Route::patch('appointments/{id}', [AppointmentController::class, 'update']);
        Route::post('appointments/{id}/reschedule', [AppointmentController::class, 'reschedule']);
        Route::patch('appointments/{id}/reschedule', [AppointmentController::class, 'reschedule']);
        Route::post('appointments/{id}/status', [AppointmentController::class, 'setStatus']);
        Route::patch('appointments/{id}/status', [AppointmentController::class, 'setStatus']);
    });
});

<?php
use Illuminate\Support\Facades\Route;

Route::middleware(['resolveTenant'])->group(function () {
    Route::get('/tenant/ping', function () {
        $tenant = request()->attributes->get('currentTenant');
        return response()->json([
            'ok' => true,
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'db' => $tenant->db_name,
            ],
        ]);
    });
});

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_staff', function (Blueprint $table) {
            $table->id();

            // Tenancy scope
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();

            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();

            // Assignment controls
            $table->boolean('is_active')->default(true);

            // Optional per-staff overrides for a service
            $table->integer('price_cents_override')->nullable();
            $table->integer('duration_min_override')->nullable();

            $table->timestamps();

            // Defense-in-depth: enforce uniqueness within a salon
            $table->unique(['salon_id','service_id','staff_id']);

            $table->index(['salon_id','service_id']);
            $table->index(['salon_id','staff_id']);
            $table->index(['salon_id','is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_staff');
    }
};

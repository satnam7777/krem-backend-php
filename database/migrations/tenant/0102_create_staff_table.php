<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('title')->nullable();

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('avatar_url')->nullable();

            $table->timestamps();

            $table->index(['salon_id','is_active']);
            $table->index(['salon_id','sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};

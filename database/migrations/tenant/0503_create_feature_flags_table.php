<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();

            $table->string('key');
            $table->boolean('enabled')->default(false);
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['salon_id','key']);
            $table->index(['salon_id','enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flags');
    }
};

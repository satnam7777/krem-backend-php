<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('salon_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();

            $table->string('key');
            $table->string('type')->default('string'); // string,int,bool,json
            $table->json('value')->nullable();

            $table->timestamps();

            $table->unique(['salon_id','key']);
            $table->index(['salon_id','key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salon_settings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('webhook_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();

            $table->string('name')->nullable();
            $table->string('target_url', 500);
            $table->boolean('enabled')->default(true);

            // HMAC secret for signing (store hashed or encrypted if desired)
            $table->string('secret', 255);

            // events array, empty/null means all
            $table->json('events')->nullable();

            $table->timestamps();

            $table->index(['salon_id','enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_subscriptions');
    }
};

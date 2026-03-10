<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained('webhook_subscriptions')->cascadeOnDelete();

            $table->string('event', 120);
            $table->string('delivery_id', 64)->unique(); // idempotency key for receiver
            $table->json('payload');

            $table->string('status')->default('pending'); // pending, sent, failed
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('next_attempt_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('last_error', 1000)->nullable();
            $table->unsignedSmallInteger('last_http_status')->nullable();

            $table->timestamps();

            $table->index(['salon_id','status','next_attempt_at']);
            $table->index(['subscription_id','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
    }
};

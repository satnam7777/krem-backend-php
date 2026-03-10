<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('payment_intents', function (Blueprint $table) {
      $table->id();
      $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
      $table->foreignId('order_id')->constrained()->cascadeOnDelete();

      $table->string('provider');
      $table->string('provider_intent_id')->nullable()->index();

      $table->integer('amount_cents');
      $table->char('currency',3)->default('EUR');
      $table->enum('status',['requires_payment_method','requires_confirmation','processing','succeeded','failed','cancelled'])
            ->default('requires_payment_method');

      $table->json('provider_payload')->nullable();
      $table->timestamps();

      $table->index(['salon_id','provider','status']);
    });
  }
  public function down(): void {
    Schema::dropIfExists('payment_intents');
  }
};

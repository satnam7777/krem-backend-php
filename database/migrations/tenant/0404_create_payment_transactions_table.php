<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('payment_transactions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
      $table->foreignId('order_id')->constrained()->cascadeOnDelete();
      $table->foreignId('payment_intent_id')->nullable()->constrained()->nullOnDelete();

      $table->string('provider');
      $table->string('provider_txn_id')->nullable()->index();

      $table->integer('amount_cents');
      $table->char('currency',3)->default('EUR');

      $table->enum('type',['charge','refund'])->default('charge');
      $table->enum('status',['pending','succeeded','failed'])->default('pending');

      $table->text('failure_reason')->nullable();
      $table->json('provider_payload')->nullable();
      $table->timestamps();

      $table->index(['salon_id','provider','status']);
    });
  }
  public function down(): void {
    Schema::dropIfExists('payment_transactions');
  }
};

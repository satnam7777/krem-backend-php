<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('orders', function (Blueprint $table) {
      $table->id();
      $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
      $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
      $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

      $table->integer('total_cents');
      $table->char('currency',3)->default('EUR');

      $table->enum('status',['pending','paid','refunded','failed','cancelled'])->default('pending');

      $table->string('reference')->nullable()->index();
      $table->json('meta')->nullable();

      $table->timestamp('paid_at')->nullable();
      $table->timestamps();

      $table->index(['salon_id','status','created_at']);
    });
  }
  public function down(): void {
    Schema::dropIfExists('orders');
  }
};

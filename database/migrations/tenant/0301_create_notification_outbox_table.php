<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_outbox', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('channel');   // email, sms
            $table->string('template');  // template key
            $table->json('payload');     // template variables + routing

            $table->enum('status', ['pending','sent','failed'])->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('send_after')->nullable();

            $table->text('last_error')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->index(['status','send_after']);
            $table->index(['salon_id','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_outbox');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('salon_invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->enum('role', ['admin', 'staff', 'support']);
            $table->string('token_hash')->unique();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['salon_id', 'email']);
            $table->index(['salon_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salon_invites');
    }
};

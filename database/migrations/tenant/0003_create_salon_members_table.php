<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('salon_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['owner', 'admin', 'staff', 'support']);
            $table->enum('status', ['active', 'invited', 'suspended'])->default('active');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['salon_id', 'user_id']);
            $table->index(['salon_id', 'role', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salon_members');
    }
};

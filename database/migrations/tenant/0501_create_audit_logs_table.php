<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('actor_role')->nullable();
            $table->string('action'); // e.g. salon.invite.created
            $table->json('context')->nullable(); // ids, diffs, ip, user-agent

            $table->string('ip')->nullable();
            $table->string('user_agent', 512)->nullable();

            $table->timestamps();

            $table->index(['salon_id','created_at']);
            $table->index(['actor_user_id','created_at']);
            $table->index(['action','created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('platform_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('action', 80);
            $table->string('ip', 80)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['tenant_id','action']);
            $table->index(['user_id','action']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('platform_audit_logs');
    }
};

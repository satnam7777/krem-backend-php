<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('slug', 120)->unique();
            $table->string('db_name', 63)->unique();
            $table->string('status', 20)->default('active'); // active, suspended
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status']);
        });
    }
    public function down(): void { Schema::dropIfExists('tenants'); }
};

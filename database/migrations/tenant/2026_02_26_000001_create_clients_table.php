<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained('salons')->cascadeOnDelete();

            $table->string('first_name', 80);
            $table->string('last_name', 80)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email', 160)->nullable();
            $table->string('gender', 20)->nullable(); // male/female/other
            $table->date('date_of_birth')->nullable();

            $table->string('status', 20)->default('active'); // active/archived
            $table->timestamps();
            $table->softDeletes();

            $table->index(['salon_id','status']);
            $table->index(['salon_id','phone']);
            $table->index(['salon_id','email']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('clients');
    }
};

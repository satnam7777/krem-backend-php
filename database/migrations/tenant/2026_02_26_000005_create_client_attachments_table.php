<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('client_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('treatment_id')->nullable()->constrained('client_treatments')->nullOnDelete();

            $table->string('kind', 40)->default('document'); // before, after, document
            $table->string('disk', 40)->default('public');
            $table->string('path', 600);
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('original_name', 255)->nullable();
            $table->string('sha256', 64)->nullable();

            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['client_id','kind']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('client_attachments');
    }
};

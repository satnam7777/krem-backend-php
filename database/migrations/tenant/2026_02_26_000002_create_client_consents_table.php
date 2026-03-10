<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('client_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();

            $table->string('type', 60); // gdpr, medical, marketing...
            $table->string('version', 30); // e.g. v1.0
            $table->string('text_hash', 64); // sha256 of consent text
            $table->timestamp('accepted_at');
            $table->string('source', 40)->default('in_person'); // web/in_person/phone
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->jsonb('meta')->nullable();
            $table->timestamps();

            $table->index(['client_id','type']);
            $table->index(['type','version']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('client_consents');
    }
};

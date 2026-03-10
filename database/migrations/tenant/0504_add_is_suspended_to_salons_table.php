<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            if (!Schema::hasColumn('salons', 'is_suspended')) {
                $table->boolean('is_suspended')->default(false)->after('name')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('salons', function (Blueprint $table) {
            if (Schema::hasColumn('salons', 'is_suspended')) {
                $table->dropIndex(['is_suspended']);
                $table->dropColumn('is_suspended');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // image_url already exists; only add leader if missing
        if (!Schema::hasColumn('community_link', 'leader')) {
            Schema::table('community_link', function (Blueprint $table) {
                $table->string('leader')->nullable()->after('image_url');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('community_link', 'leader')) {
            Schema::table('community_link', function (Blueprint $table) {
                $table->dropColumn('leader');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_bases', function (Blueprint $table) {
            $table->id('event_id');
            $table->string('department');
            $table->string('semester');
            $table->string('section');
            $table->timestamps();

            $table->index(['department', 'semester', 'section']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_bases');
    }
};

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
        Schema::create('community_link', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); 
            $table->string('url');
            $table->text('description')->nullable();
            $table->string('category');
            $table->string('image_url', 2048)->nullable();
            $table->string('logo_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_link');
    }
};

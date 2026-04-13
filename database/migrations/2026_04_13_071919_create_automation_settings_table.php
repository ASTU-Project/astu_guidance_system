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
        Schema::create('automation_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Global safety toggles
            $table->boolean('enable_write_tools')->default(false);
            $table->boolean('confirm_destructive_actions')->default(true);

            // Tool group toggles
            $table->json('enabled_tool_groups')->default('[]'); // ['students', 'departments', 'calendar', 'policies']

            // System prompt
            $table->longText('system_prompt')->nullable();

            $table->timestamps();

            // User-scoped settings only (not per chat session).
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_settings');
    }
};

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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();

            // Time
            $table->integer('day');                 
            $table->integer('start_hour');                   // 0-23
            $table->integer('start_minute');                 // 0-59
            $table->integer('end_hour');                     // 0-23
            $table->integer('end_minute');                   // 0-59

            // Academic dimensions
            $table->string('semester');                    
            $table->integer('year');                    
            $table->string('department');
            $table->string('section')->nullable();           // null = all sections

            // Permissions
            $table->enum('source', ['admin', 'student'])->default('student');
            $table->boolean('editable')->default(true);
            $table->boolean('deletable')->default(true);

            // Style
            $table->integer('color_id')->default(1);         // 1-6

            // Ownership
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('student_id')->nullable()->constrained('students'); // if student-owned

            $table->timestamps();

            // Indexes
            $table->index(['semester', 'year', 'department', 'section']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

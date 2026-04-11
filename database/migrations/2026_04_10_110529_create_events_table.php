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
            $table->foreignId('event_id')->constrained('event_bases', 'event_id')->cascadeOnDelete();
            $table->string('task');
            $table->enum('day', [
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
                'saturday',
                'sunday',
            ]);
            $table->unsignedTinyInteger('start_hour');
            $table->unsignedTinyInteger('start_min');
            $table->unsignedTinyInteger('end_hour');
            $table->unsignedTinyInteger('end_min');
            $table->enum('source', ['admin', 'student'])->default('student');
            $table->boolean('editable')->default(false);
            $table->boolean('deletable')->default(false);
            $table->string('color')->default('#2563eb');
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();

            $table->timestamps();
            $table->index(['event_id', 'day']);
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

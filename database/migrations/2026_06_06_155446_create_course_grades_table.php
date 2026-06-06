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
        Schema::create('course_grades', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('course_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('users');
            $table->decimal('exam_raw_score', 6, 2)->nullable();
            $table->decimal('exam_raw_max', 6, 2)->nullable();
            $table->decimal('computed_score', 6, 2)->nullable(); // final normalized score out of 100
            $table->timestamps();

            $table->unique(['course_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_grades');
    }
};

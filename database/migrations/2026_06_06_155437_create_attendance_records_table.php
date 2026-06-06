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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('session_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('users');
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->enum('status', ['present', 'absent', 'excused'])->default('absent');
            $table->timestamps();

            $table->unique(['session_id', 'student_id']); // one record per student per session
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};

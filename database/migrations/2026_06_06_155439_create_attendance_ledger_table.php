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
        Schema::create('attendance_ledger', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('cohort_id')->constrained()->cascadeOnDelete();
            $table->integer('balance')->default(250); // starts at 250 per ATT-4
            $table->timestamps();

            $table->unique(['student_id', 'cohort_id']); // one ledger per student per cohort
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_ledger');
    }
};

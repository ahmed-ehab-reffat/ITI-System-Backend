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
        Schema::create('courses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cohort_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('lab_weight')->default(40);   // % of 100
            $table->unsignedInteger('exam_weight')->default(60);  // % of 100
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};

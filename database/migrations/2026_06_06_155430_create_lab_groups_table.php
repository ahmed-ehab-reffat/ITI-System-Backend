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
        Schema::create('lab_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cohort_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. "Group A", "Group 1"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_groups');
    }
};

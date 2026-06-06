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
        Schema::create('engagements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cohort_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('instructor_id')->constrained('users');
            $table->foreignUuid('lab_group_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['lecture', 'lab', 'business_session']);
            $table->date('starts_at');
            $table->date('ends_at');
            $table->decimal('hours_per_session', 4, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('engagements');
    }
};

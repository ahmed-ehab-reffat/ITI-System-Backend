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
        Schema::create('submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('session_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('users');
            $table->string('url')->nullable();         // repo or drive link
            $table->string('file_path')->nullable();   // direct upload
            $table->timestamp('submitted_at');
            $table->decimal('raw_score', 5, 2)->nullable();
            $table->decimal('late_penalty', 5, 2)->default(0); // computed at grade time
            $table->timestamps();

            $table->unique(['session_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};

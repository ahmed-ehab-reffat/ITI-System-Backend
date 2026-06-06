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
        Schema::create('excuse_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('attendance_record_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('users');
            $table->text('reason');
            $table->string('attachment_path')->nullable(); // max 1MB, PDF or image (EXC-2)
            $table->enum('status', ['requested', 'approved', 'rejected'])->default('requested');
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reviewer_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('excuse_requests');
    }
};

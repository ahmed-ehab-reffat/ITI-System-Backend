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
        Schema::create('billing_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();    // instructor or track admin
            $table->foreignUuid('session_id')->constrained()->cascadeOnDelete();
            $table->decimal('hours', 5, 2);
            $table->enum('person_type', ['external', 'internal']); // per BIL-2
            $table->timestamps();

            $table->unique(['user_id', 'session_id']); // one billing record per person per session
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_records');
    }
};

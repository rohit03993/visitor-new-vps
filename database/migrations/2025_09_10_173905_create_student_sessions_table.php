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
        Schema::create('student_sessions', function (Blueprint $table) {
            $table->id('session_id');
            $table->unsignedBigInteger('visitor_id');
            $table->string('purpose'); // Admission, Fee Issue, Complaint, etc.
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->enum('outcome', ['success', 'failed', 'pending'])->nullable();
            $table->text('outcome_notes')->nullable();
            $table->datetime('started_at');
            $table->datetime('completed_at')->nullable();
            $table->unsignedBigInteger('started_by'); // Staff who started the session
            $table->unsignedBigInteger('completed_by')->nullable(); // Staff who completed the session
            $table->timestamps();
            
            $table->foreign('visitor_id')->references('visitor_id')->on('visitors')->onDelete('cascade');
            $table->foreign('started_by')->references('user_id')->on('vms_users')->onDelete('cascade');
            $table->foreign('completed_by')->references('user_id')->on('vms_users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_sessions');
    }
};

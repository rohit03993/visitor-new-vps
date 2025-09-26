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
        Schema::create('file_management', function (Blueprint $table) {
            $table->id();
            $table->string('original_filename');
            $table->string('server_path');
            $table->string('file_type');
            $table->bigInteger('file_size');
            $table->string('google_drive_file_id')->nullable();
            $table->text('google_drive_url')->nullable();
            $table->enum('status', ['server', 'drive', 'pending', 'failed'])->default('server');
            $table->unsignedBigInteger('uploaded_by');
            $table->unsignedBigInteger('transferred_by')->nullable();
            $table->unsignedBigInteger('interaction_id')->nullable();
            $table->string('mime_type')->nullable();
            $table->timestamp('transferred_at')->nullable();
            $table->text('transfer_notes')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('uploaded_by')->references('user_id')->on('vms_users')->onDelete('cascade');
            $table->foreign('transferred_by')->references('user_id')->on('vms_users')->onDelete('set null');
            $table->foreign('interaction_id')->references('interaction_id')->on('interaction_history')->onDelete('cascade');
            
            // Indexes for better performance
            $table->index('status');
            $table->index('uploaded_by');
            $table->index('interaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_management');
    }
};

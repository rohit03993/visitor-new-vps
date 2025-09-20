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
        Schema::create('interaction_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('interaction_id');
            $table->string('original_filename');
            $table->string('file_type'); // pdf, jpg, png, mp3, wav
            $table->integer('file_size'); // in bytes
            $table->string('google_drive_file_id');
            $table->string('google_drive_url');
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('interaction_id')->references('interaction_id')->on('interaction_history')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('user_id')->on('vms_users')->onDelete('cascade');
            
            // Indexes for performance
            $table->index('interaction_id');
            $table->index('file_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interaction_attachments');
    }
};

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
        Schema::create('homework', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('school_classes')->onDelete('cascade');
            $table->unsignedBigInteger('teacher_id'); // References vms_users.user_id
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['pdf', 'image', 'text', 'link']); // Type of homework
            $table->string('file_path')->nullable(); // For PDF and image files
            $table->text('content')->nullable(); // For text content
            $table->string('external_link')->nullable(); // For external links
            $table->timestamps();
            
            // Foreign key to vms_users table
            $table->foreign('teacher_id')->references('user_id')->on('vms_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homework');
    }
};


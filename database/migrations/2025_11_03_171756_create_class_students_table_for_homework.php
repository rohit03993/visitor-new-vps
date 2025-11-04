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
        Schema::create('class_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('school_classes')->onDelete('cascade');
            $table->unsignedBigInteger('student_id'); // References visitors.visitor_id
            $table->timestamps();
            
            // Ensure unique combination of class and student
            $table->unique(['class_id', 'student_id']);
            
            // Foreign key to visitors table
            $table->foreign('student_id')->references('visitor_id')->on('visitors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_students');
    }
};

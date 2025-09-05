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
        Schema::create('visitors', function (Blueprint $table) {
            $table->id('visitor_id');
            $table->string('mobile_number')->unique()->index();
            $table->string('name');
            $table->unsignedBigInteger('last_updated_by');
            $table->timestamps();
            
            $table->foreign('last_updated_by')->references('user_id')->on('vms_users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};

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
        Schema::create('remarks', function (Blueprint $table) {
            $table->id('remark_id');
            $table->unsignedBigInteger('visit_id');
            $table->text('remark_text');
            $table->unsignedBigInteger('added_by');
            $table->unsignedBigInteger('is_editable_by');
            $table->timestamps();
            
            $table->foreign('visit_id')->references('visit_id')->on('visit_history');
            $table->foreign('added_by')->references('user_id')->on('vms_users');
            $table->foreign('is_editable_by')->references('user_id')->on('vms_users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remarks');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Table for managing who gets notified for which interactions
        Schema::create('interaction_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('interaction_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('subscribed_by', ['system', 'creator', 'assignee', 'manual', 'admin'])->default('system');
            $table->enum('privacy_level', ['public', 'private'])->default('public');
            $table->boolean('is_active')->default(true);
            $table->timestamp('subscribed_at')->useCurrent();
            $table->timestamps();

            $table->foreign('interaction_id')->references('interaction_id')->on('interaction_history')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('vms_users')->onDelete('cascade');
            $table->unique(['interaction_id', 'user_id']);
        });

        // Table for storing actual notification logs
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('interaction_id');
            $table->unsignedBigInteger('user_id'); // Who gets the notification
            $table->unsignedBigInteger('triggered_by'); // Who triggered the action
            $table->string('notification_type'); // 'assignment', 'remark', 'file_upload', 'status_change'
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('interaction_id')->references('interaction_id')->on('interaction_history')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('vms_users')->onDelete('cascade');
            $table->foreign('triggered_by')->references('user_id')->on('vms_users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('interaction_notifications');
    }
};
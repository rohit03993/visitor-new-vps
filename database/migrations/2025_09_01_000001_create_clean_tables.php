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
        // 1. Create vms_users table
        Schema::create('vms_users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('name');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('role', 50)->default('staff');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('mobile_number')->nullable();
            $table->boolean('can_view_remarks')->default(false);
            $table->boolean('can_download_excel')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Create branches table
        Schema::create('branches', function (Blueprint $table) {
            $table->id('branch_id');
            $table->string('branch_name');
            $table->string('branch_code')->unique();
            $table->text('address')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('created_by')->references('user_id')->on('vms_users')->onDelete('set null');
        });

        // 3. Create addresses table
        Schema::create('addresses', function (Blueprint $table) {
            $table->id('address_id');
            $table->string('address_name')->unique();
            $table->text('full_address')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('created_by')->references('user_id')->on('vms_users')->onDelete('set null');
        });

        // 4. Create visitors table
        Schema::create('visitors', function (Blueprint $table) {
            $table->id('visitor_id');
            $table->string('mobile_number')->unique();
            $table->string('name');
            $table->unsignedBigInteger('last_updated_by')->nullable();
            $table->timestamps();
            
            $table->foreign('last_updated_by')->references('user_id')->on('vms_users')->onDelete('set null');
        });

        // 5. Create interaction_history table
        Schema::create('interaction_history', function (Blueprint $table) {
            $table->id('interaction_id');
            $table->unsignedBigInteger('visitor_id');
            $table->string('name_entered');
            $table->string('mobile_number');
            $table->string('purpose', 255);
            $table->unsignedBigInteger('meeting_with');
            $table->unsignedBigInteger('address_id')->nullable();
            $table->enum('mode', ['In-Campus', 'Out-Campus', 'Telephonic']);
            $table->unsignedBigInteger('created_by');
            $table->string('created_by_role', 50)->default('staff');
            $table->timestamps();
            
            $table->foreign('visitor_id')->references('visitor_id')->on('visitors')->onDelete('cascade');
            $table->foreign('meeting_with')->references('user_id')->on('vms_users')->onDelete('cascade');
            $table->foreign('address_id')->references('address_id')->on('addresses')->onDelete('set null');
            $table->foreign('created_by')->references('user_id')->on('vms_users')->onDelete('cascade');
        });

        // 6. Create remarks table
        Schema::create('remarks', function (Blueprint $table) {
            $table->id('remark_id');
            $table->unsignedBigInteger('interaction_id');
            $table->text('remark_text');
            $table->unsignedBigInteger('added_by');
            $table->string('added_by_name');
            $table->unsignedBigInteger('is_editable_by')->nullable();
            $table->timestamps();
            
            $table->foreign('interaction_id')->references('interaction_id')->on('interaction_history')->onDelete('cascade');
            $table->foreign('added_by')->references('user_id')->on('vms_users')->onDelete('cascade');
            $table->foreign('is_editable_by')->references('user_id')->on('vms_users')->onDelete('set null');
        });

        // 7. Create user_branch_permissions table
        Schema::create('user_branch_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('branch_id');
            $table->boolean('can_view_remarks')->default(false);
            $table->boolean('can_download_excel')->default(false);
            $table->timestamps();
            
            $table->foreign('user_id')->references('user_id')->on('vms_users')->onDelete('cascade');
            $table->foreign('branch_id')->references('branch_id')->on('branches')->onDelete('cascade');
            $table->unique(['user_id', 'branch_id']);
        });

        // 8. Add foreign key for vms_users.branch_id
        Schema::table('vms_users', function (Blueprint $table) {
            $table->foreign('branch_id')->references('branch_id')->on('branches')->onDelete('set null');
        });

        // 9. Create sessions table (required by Laravel)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 10. Create cache table (required by Laravel for database caching)
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        // 11. Create cache_locks table (required by Laravel for cache locking)
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('user_branch_permissions');
        Schema::dropIfExists('remarks');
        Schema::dropIfExists('interaction_history');
        Schema::dropIfExists('visitors');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('vms_users');
    }
};

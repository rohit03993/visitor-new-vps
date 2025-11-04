<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * IMPORTANT: This migration updates foreign keys from visitors/vms_users to homework_users
     * This ensures complete isolation between homework CRM and main visitor management system
     */
    public function up(): void
    {
        // ========== STEP 1: Drop old foreign keys ==========
        
        // Drop foreign key from class_students table (find actual name first)
        $classStudentsFK = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'class_students' 
            AND COLUMN_NAME = 'student_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        foreach ($classStudentsFK as $fk) {
            DB::statement("ALTER TABLE class_students DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }
        
        // Drop foreign key from homework table (teacher_id) - find actual name first
        $homeworkFK = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'homework' 
            AND COLUMN_NAME = 'teacher_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        foreach ($homeworkFK as $fk) {
            DB::statement("ALTER TABLE homework DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }
        
        // Drop ALL foreign keys from homework_views table (could be on homework_id or visitor_id)
        // MySQL requires dropping ALL foreign keys before we can drop unique index
        $allForeignKeys = DB::select("
            SELECT DISTINCT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'homework_views' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        foreach ($allForeignKeys as $fk) {
            try {
                DB::statement("ALTER TABLE homework_views DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            } catch (\Exception $e) {
                // Foreign key might already be dropped, continue
            }
        }
        
        // Double-check: Find and drop any remaining foreign keys by checking REFERENTIAL_CONSTRAINTS
        $remainingFKs = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.REFERENTIAL_CONSTRAINTS 
            WHERE CONSTRAINT_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'homework_views'
        ");
        
        foreach ($remainingFKs as $fk) {
            try {
                DB::statement("ALTER TABLE homework_views DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            } catch (\Exception $e) {
                // Continue if already dropped
            }
        }
        
        // Now we can safely drop the unique constraint
        $uniqueConstraints = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'homework_views' 
            AND CONSTRAINT_TYPE = 'UNIQUE'
            AND (CONSTRAINT_NAME LIKE '%homework_id%visitor_id%' OR CONSTRAINT_NAME LIKE '%homework_views_homework_id_visitor_id%')
        ");
        
        foreach ($uniqueConstraints as $uc) {
            try {
                DB::statement("ALTER TABLE homework_views DROP INDEX `{$uc->CONSTRAINT_NAME}`");
            } catch (\Exception $e) {
                // Index might already be dropped, continue
            }
        }
        
        // Drop foreign key from homework_notifications table - find actual name first
        $homeworkNotificationsFK = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'homework_notifications' 
            AND COLUMN_NAME = 'visitor_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        foreach ($homeworkNotificationsFK as $fk) {
            DB::statement("ALTER TABLE homework_notifications DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }
        
        // ========== STEP 2: Clear existing data (since we're changing structure) ==========
        // This ensures no orphaned data exists - IMPORTANT: This will delete all existing homework data
        // Since we're creating a completely isolated system, this is expected
        DB::table('class_students')->truncate();
        DB::table('homework_views')->truncate();
        DB::table('homework_notifications')->truncate();
        // Note: We keep homework records but they'll need teacher_id updated manually if needed
        
        // ========== STEP 3: Rename columns for clarity (visitor_id -> student_id) ==========
        // Check if columns exist before renaming
        $homeworkViewsColumns = DB::select("SHOW COLUMNS FROM homework_views LIKE 'visitor_id'");
        if (!empty($homeworkViewsColumns)) {
            DB::statement('ALTER TABLE homework_views CHANGE visitor_id student_id BIGINT UNSIGNED NOT NULL');
        } else {
            // Column might already be renamed, check if student_id exists
            $studentIdExists = DB::select("SHOW COLUMNS FROM homework_views LIKE 'student_id'");
            if (empty($studentIdExists)) {
                // Neither exists, add student_id column
                DB::statement('ALTER TABLE homework_views ADD student_id BIGINT UNSIGNED NOT NULL AFTER homework_id');
            }
        }
        
        $homeworkNotificationsColumns = DB::select("SHOW COLUMNS FROM homework_notifications LIKE 'visitor_id'");
        if (!empty($homeworkNotificationsColumns)) {
            DB::statement('ALTER TABLE homework_notifications CHANGE visitor_id student_id BIGINT UNSIGNED NOT NULL');
        } else {
            // Column might already be renamed, check if student_id exists
            $studentIdExists = DB::select("SHOW COLUMNS FROM homework_notifications LIKE 'student_id'");
            if (empty($studentIdExists)) {
                // Neither exists, add student_id column
                DB::statement('ALTER TABLE homework_notifications ADD student_id BIGINT UNSIGNED NOT NULL AFTER id');
            }
        }
        
        // ========== STEP 4: Add new foreign keys pointing to homework_users ==========
        
        // Update class_students.student_id to point to homework_users.id
        Schema::table('class_students', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('homework_users')->onDelete('cascade');
        });
        
        // Update homework.teacher_id to point to homework_users.id
        Schema::table('homework', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('homework_users')->onDelete('cascade');
        });
        
        // Update homework_views.student_id to point to homework_users.id
        Schema::table('homework_views', function (Blueprint $table) {
            // Add unique constraint first
            $table->unique(['homework_id', 'student_id']);
            // Then add foreign key
            $table->foreign('student_id')->references('id')->on('homework_users')->onDelete('cascade');
        });
        
        // Update homework_notifications.student_id to point to homework_users.id
        Schema::table('homework_notifications', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('homework_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new foreign keys
        Schema::table('homework_notifications', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
        });
        
        Schema::table('homework_views', function (Blueprint $table) {
            $table->dropUnique(['homework_id', 'student_id']);
            $table->dropForeign(['student_id']);
        });
        
        Schema::table('homework', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
        });
        
        Schema::table('class_students', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
        });
        
        // Rename columns back using raw SQL
        DB::statement('ALTER TABLE homework_notifications CHANGE student_id visitor_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE homework_views CHANGE student_id visitor_id BIGINT UNSIGNED NOT NULL');
        
        // Restore unique constraint for homework_views
        Schema::table('homework_views', function (Blueprint $table) {
            $table->unique(['homework_id', 'visitor_id']);
        });
        
        // Restore old foreign keys
        Schema::table('homework_notifications', function (Blueprint $table) {
            $table->foreign('visitor_id')->references('visitor_id')->on('visitors')->onDelete('cascade');
        });
        
        Schema::table('homework_views', function (Blueprint $table) {
            $table->foreign('visitor_id')->references('visitor_id')->on('visitors')->onDelete('cascade');
        });
        
        Schema::table('homework', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('user_id')->on('vms_users')->onDelete('cascade');
        });
        
        Schema::table('class_students', function (Blueprint $table) {
            $table->foreign('student_id')->references('visitor_id')->on('visitors')->onDelete('cascade');
        });
    }
};

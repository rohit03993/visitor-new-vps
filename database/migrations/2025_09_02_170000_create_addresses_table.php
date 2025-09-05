<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create new addresses table
        Schema::create('addresses', function (Blueprint $table) {
            $table->id('address_id');
            $table->string('address_name')->unique();
            $table->text('full_address')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('created_by')->references('user_id')->on('vms_users')->onDelete('set null');
            $table->index('address_name'); // For faster searches
        });

        // Migrate existing locations data
        $locations = DB::table('locations')->get();
        foreach ($locations as $location) {
            DB::table('addresses')->insert([
                'address_name' => $location->location_name,
                'full_address' => $location->location_name,
                'created_by' => $location->created_by,
                'created_at' => $location->created_at,
                'updated_at' => $location->updated_at,
            ]);
        }

        // Update interaction_history table to use address_id instead of location_id
        if (!Schema::hasColumn('interaction_history', 'address_id')) {
            Schema::table('interaction_history', function (Blueprint $table) {
                $table->unsignedBigInteger('address_id')->nullable()->after('location_id');
            });
        }

        // Migrate the data
        $interactions = DB::table('interaction_history')->whereNotNull('location_id')->get();
        foreach ($interactions as $interaction) {
            $address = DB::table('addresses')->where('address_name', function($query) use ($interaction) {
                $query->select('location_name')->from('locations')->where('location_id', $interaction->location_id);
            })->first();
            
            if ($address) {
                DB::table('interaction_history')
                    ->where('interaction_id', $interaction->interaction_id)
                    ->update(['address_id' => $address->address_id]);
            }
        }

        // Drop the old location_id column and add foreign key
        Schema::table('interaction_history', function (Blueprint $table) {
            // Get all foreign keys for this table and column
            $foreignKeys = $this->getForeignKeyConstraints('interaction_history', 'location_id');
            if (!empty($foreignKeys)) {
                foreach ($foreignKeys as $foreignKey) {
                    try {
                        $table->dropForeign($foreignKey);
                    } catch (\Exception $e) {
                        // Log the error but continue
                        echo "Warning: Could not drop foreign key {$foreignKey}: " . $e->getMessage() . "\n";
                    }
                }
            }
            $table->dropColumn('location_id');
            $table->foreign('address_id')->references('address_id')->on('addresses')->onDelete('set null');
        });

        // Drop the old locations table
        Schema::dropIfExists('locations');
    }

    public function down(): void
    {
        // Recreate locations table
        Schema::create('locations', function (Blueprint $table) {
            $table->id('location_id');
            $table->string('location_name')->unique();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('created_by')->references('user_id')->on('vms_users')->onDelete('set null');
        });

        // Migrate addresses back to locations
        $addresses = DB::table('addresses')->get();
        foreach ($addresses as $address) {
            DB::table('locations')->insert([
                'location_name' => $address->address_name,
                'created_by' => $address->created_by,
                'created_at' => $address->created_at,
                'updated_at' => $address->updated_at,
            ]);
        }

        // Update interaction_history table back to location_id
        Schema::table('interaction_history', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->nullable()->after('address_id');
        });

        // Migrate the data back
        $interactions = DB::table('interaction_history')->whereNotNull('address_id')->get();
        foreach ($interactions as $interaction) {
            $location = DB::table('locations')->where('location_name', function($query) use ($interaction) {
                $query->select('address_name')->from('addresses')->where('address_id', $interaction->address_id);
            })->first();
            
            if ($location) {
                DB::table('interaction_history')
                    ->where('interaction_id', $interaction->interaction_id)
                    ->update(['location_id' => $location->location_id]);
            }
        }

        // Drop address_id and add location_id foreign key
        Schema::table('interaction_history', function (Blueprint $table) {
            // Get all foreign keys for this table and column
            $foreignKeys = $this->getForeignKeyConstraints('interaction_history', 'address_id');
            if (!empty($foreignKeys)) {
                foreach ($foreignKeys as $foreignKey) {
                    try {
                        $table->dropForeign($foreignKey);
                    } catch (\Exception $e) {
                        // Log the error but continue
                        echo "Warning: Could not drop foreign key {$foreignKey}: " . $e->getMessage() . "\n";
                    }
                }
            }
            $table->dropColumn('address_id');
            $table->foreign('location_id')->references('location_id')->on('locations')->onDelete('set null');
        });

        // Drop addresses table
        Schema::dropIfExists('addresses');
    }

    /**
     * Get foreign key constraints for a column
     */
    private function getForeignKeyConstraints($table, $column)
    {
        $foreignKeys = [];
        $constraints = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ? 
            AND COLUMN_NAME = ? 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$table, $column]);
        
        foreach ($constraints as $constraint) {
            $foreignKeys[] = $constraint->CONSTRAINT_NAME;
        }
        
        return $foreignKeys;
    }
};

<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\VmsUser;
use App\Models\Location;
use App\Models\Visitor;
use App\Models\VisitHistory;
use App\Models\Remark;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test database tables exist
     */
    public function test_database_tables_exist()
    {
        $this->assertTrue(Schema::hasTable('vms_users'));
        $this->assertTrue(Schema::hasTable('locations'));
        $this->assertTrue(Schema::hasTable('visitors'));
        $this->assertTrue(Schema::hasTable('visit_history'));
        $this->assertTrue(Schema::hasTable('remarks'));
    }

    /**
     * Test VmsUser table structure
     */
    public function test_vms_users_table_structure()
    {
        $columns = Schema::getColumnListing('vms_users');
        
        $expectedColumns = ['id', 'name', 'email', 'password', 'role', 'created_at', 'updated_at'];
        
        foreach ($expectedColumns as $column) {
            $this->assertContains($column, $columns);
        }
    }

    /**
     * Test Locations table structure
     */
    public function test_locations_table_structure()
    {
        $columns = Schema::getColumnListing('locations');
        
        $expectedColumns = ['id', 'name', 'address', 'contact_person', 'contact_number', 'created_at', 'updated_at'];
        
        foreach ($expectedColumns as $column) {
            $this->assertContains($column, $column, $columns);
        }
    }

    /**
     * Test Visitors table structure
     */
    public function test_visitors_table_structure()
    {
        $columns = Schema::getColumnListing('visitors');
        
        $expectedColumns = ['id', 'name', 'mobile', 'email', 'purpose', 'location_id', 'expected_duration', 'created_at', 'updated_at'];
        
        foreach ($expectedColumns as $column) {
            $this->assertContains($column, $columns);
        }
    }

    /**
     * Test VisitHistory table structure
     */
    public function test_visit_history_table_structure()
    {
        $columns = Schema::getColumnListing('visit_history');
        
        $expectedColumns = ['id', 'visitor_id', 'status', 'check_in_time', 'check_out_time', 'expected_duration', 'created_at', 'updated_at'];
        
        foreach ($expectedColumns as $column) {
            $this->assertContains($column, $columns);
        }
    }

    /**
     * Test Remarks table structure
     */
    public function test_remarks_table_structure()
    {
        $columns = Schema::getColumnListing('remarks');
        
        $expectedColumns = ['id', 'visit_history_id', 'remark', 'status', 'created_at', 'updated_at'];
        
        foreach ($expectedColumns as $column) {
            $this->assertContains($column, $columns);
        }
    }

    /**
     * Test data insertion and retrieval
     */
    public function test_data_insertion_and_retrieval()
    {
        // Create test data
        $user = VmsUser::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'admin'
        ]);

        $location = Location::factory()->create([
            'name' => 'Test Office',
            'address' => '123 Test Street'
        ]);

        $visitor = Visitor::factory()->create([
            'name' => 'John Doe',
            'mobile' => '9876543210',
            'location_id' => $location->id
        ]);

        // Test retrieval
        $retrievedUser = VmsUser::find($user->id);
        $this->assertEquals('Test User', $retrievedUser->name);

        $retrievedLocation = Location::find($location->id);
        $this->assertEquals('Test Office', $retrievedLocation->name);

        $retrievedVisitor = Visitor::find($visitor->id);
        $this->assertEquals('John Doe', $retrievedVisitor->name);
    }

    /**
     * Test foreign key relationships
     */
    public function test_foreign_key_relationships()
    {
        $location = Location::factory()->create();
        $visitor = Visitor::factory()->create(['location_id' => $location->id]);
        $visitHistory = VisitHistory::factory()->create(['visitor_id' => $visitor->id]);
        $remark = Remark::factory()->create(['visit_history_id' => $visitHistory->id]);

        // Test relationships
        $this->assertEquals($location->id, $visitor->location->id);
        $this->assertEquals($visitor->id, $visitHistory->visitor->id);
        $this->assertEquals($visitHistory->id, $remark->visitHistory->id);
    }

    /**
     * Test data validation constraints
     */
    public function test_data_validation_constraints()
    {
        // Test unique email constraint
        VmsUser::factory()->create(['email' => 'test@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        VmsUser::factory()->create(['email' => 'test@example.com']);
    }

    /**
     * Test cascade delete relationships
     */
    public function test_cascade_delete_relationships()
    {
        $location = Location::factory()->create();
        $visitor = Visitor::factory()->create(['location_id' => $location->id]);
        $visitHistory = VisitHistory::factory()->create(['visitor_id' => $visitor->id]);
        $remark = Remark::factory()->create(['visit_history_id' => $visitHistory->id]);

        // Delete location should cascade to visitors
        $location->delete();
        
        $this->assertDatabaseMissing('visitors', ['id' => $visitor->id]);
        $this->assertDatabaseMissing('visit_history', ['id' => $visitHistory->id]);
        $this->assertDatabaseMissing('remarks', ['id' => $remark->id]);
    }
}

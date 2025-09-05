<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\VmsUser;
use App\Models\Location;
use App\Models\Visitor;
use App\Models\VisitHistory;
use App\Models\Remark;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test VmsUser model attributes and relationships
     */
    public function test_vms_user_model()
    {
        $user = VmsUser::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'admin'
        ]);

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('admin', $user->role);
        $this->assertTrue($user->isAdmin());
    }

    /**
     * Test Location model attributes and relationships
     */
    public function test_location_model()
    {
        $location = Location::factory()->create([
            'name' => 'Test Office',
            'address' => '123 Test Street',
            'contact_person' => 'John Doe',
            'contact_number' => '9876543210'
        ]);

        $this->assertEquals('Test Office', $location->name);
        $this->assertEquals('123 Test Street', $location->address);
        $this->assertEquals('John Doe', $location->contact_person);
        $this->assertEquals('9876543210', $location->contact_number);
    }

    /**
     * Test Visitor model attributes and relationships
     */
    public function test_visitor_model()
    {
        $location = Location::factory()->create();
        $visitor = Visitor::factory()->create([
            'name' => 'Jane Doe',
            'mobile' => '9876543210',
            'email' => 'jane@example.com',
            'purpose' => 'Business Meeting',
            'location_id' => $location->id
        ]);

        $this->assertEquals('Jane Doe', $visitor->name);
        $this->assertEquals('9876543210', $visitor->mobile);
        $this->assertEquals('jane@example.com', $visitor->email);
        $this->assertEquals('Business Meeting', $visitor->purpose);
        $this->assertEquals($location->id, $visitor->location_id);

        // Test relationship
        $this->assertInstanceOf(Location::class, $visitor->location);
    }

    /**
     * Test VisitHistory model attributes and relationships
     */
    public function test_visit_history_model()
    {
        $visitor = Visitor::factory()->create();
        $visitHistory = VisitHistory::factory()->create([
            'visitor_id' => $visitor->id,
            'status' => 'in_progress',
            'check_in_time' => now(),
            'expected_duration' => '2 hours'
        ]);

        $this->assertEquals($visitor->id, $visitHistory->visitor_id);
        $this->assertEquals('in_progress', $visitHistory->status);
        $this->assertEquals('2 hours', $visitHistory->expected_duration);

        // Test relationship
        $this->assertInstanceOf(Visitor::class, $visitHistory->visitor);
    }

    /**
     * Test Remark model attributes and relationships
     */
    public function test_remark_model()
    {
        $visitHistory = VisitHistory::factory()->create();
        $remark = Remark::factory()->create([
            'visit_history_id' => $visitHistory->id,
            'remark' => 'Meeting completed successfully',
            'status' => 'completed'
        ]);

        $this->assertEquals($visitHistory->id, $remark->visit_history_id);
        $this->assertEquals('Meeting completed successfully', $remark->remark);
        $this->assertEquals('completed', $remark->status);

        // Test relationship
        $this->assertInstanceOf(VisitHistory::class, $remark->visitHistory);
    }

    /**
     * Test model relationships work correctly
     */
    public function test_model_relationships()
    {
        $location = Location::factory()->create();
        $visitor = Visitor::factory()->create(['location_id' => $location->id]);
        $visitHistory = VisitHistory::factory()->create(['visitor_id' => $visitor->id]);
        $remark = Remark::factory()->create(['visit_history_id' => $visitHistory->id]);

        // Test Location -> Visitors relationship
        $this->assertCount(1, $location->visitors);
        $this->assertInstanceOf(Visitor::class, $location->visitors->first());

        // Test Visitor -> VisitHistory relationship
        $this->assertCount(1, $visitor->visitHistories);
        $this->assertInstanceOf(VisitHistory::class, $visitor->visitHistories->first());

        // Test VisitHistory -> Remarks relationship
        $this->assertCount(1, $visitHistory->remarks);
        $this->assertInstanceOf(Remark::class, $visitHistory->remarks->first());
    }
}

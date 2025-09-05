<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\VmsUser;
use App\Models\Location;
use App\Models\Visitor;
use App\Models\VisitHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $frontdesk;
    protected $employee;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = VmsUser::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('password123')
        ]);

        $this->frontdesk = VmsUser::factory()->create([
            'role' => 'frontdesk',
            'password' => Hash::make('password123')
        ]);

        $this->employee = VmsUser::factory()->create([
            'role' => 'employee',
            'password' => Hash::make('password123')
        ]);
    }

    /**
     * Test mobile check API endpoint
     */
    public function test_mobile_check_api()
    {
        $visitor = Visitor::factory()->create([
            'mobile' => '9876543210'
        ]);

        $response = $this->actingAs($this->frontdesk)->postJson('/frontdesk/check-mobile', [
            'mobile' => '9876543210'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'exists' => true,
                    'visitor' => [
                        'id' => $visitor->id,
                        'name' => $visitor->name,
                        'mobile' => $visitor->mobile
                    ]
                ]);
    }

    /**
     * Test mobile check API for non-existent mobile
     */
    public function test_mobile_check_api_not_found()
    {
        $response = $this->actingAs($this->frontdesk)->postJson('/frontdesk/check-mobile', [
            'mobile' => '9999999999'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'exists' => false,
                    'visitor' => null
                ]);
    }

    /**
     * Test visitor search API
     */
    public function test_visitor_search_api()
    {
        $visitor = Visitor::factory()->create([
            'name' => 'John Doe',
            'mobile' => '9876543210'
        ]);

        $response = $this->actingAs($this->frontdesk)->postJson('/frontdesk/search-visitors', [
            'search_term' => 'John Doe'
        ]);

        $response->assertStatus(200)
                ->assertJsonCount(1)
                ->assertJsonFragment([
                    'name' => 'John Doe',
                    'mobile' => '9876543210'
                ]);
    }

    /**
     * Test location creation API
     */
    public function test_location_creation_api()
    {
        $locationData = [
            'name' => 'New Office',
            'address' => '456 Business Ave',
            'contact_person' => 'Jane Smith',
            'contact_number' => '9876543210'
        ];

        $response = $this->actingAs($this->frontdesk)->postJson('/frontdesk/add-location', $locationData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Location added successfully'
                ]);

        $this->assertDatabaseHas('locations', $locationData);
    }

    /**
     * Test visitor registration API
     */
    public function test_visitor_registration_api()
    {
        $location = Location::factory()->create();

        $visitorData = [
            'name' => 'John Doe',
            'mobile' => '9876543210',
            'email' => 'john@example.com',
            'purpose' => 'Business Meeting',
            'location_id' => $location->id,
            'expected_duration' => '2 hours'
        ];

        $response = $this->actingAs($this->frontdesk)->postJson('/frontdesk/store-visitor', $visitorData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Visitor registered successfully'
                ]);

        $this->assertDatabaseHas('visitors', [
            'name' => 'John Doe',
            'mobile' => '9876543210',
            'email' => 'john@example.com'
        ]);
    }

    /**
     * Test remark update API
     */
    public function test_remark_update_api()
    {
        $visitor = Visitor::factory()->create();
        $visitHistory = VisitHistory::factory()->create([
            'visitor_id' => $visitor->id,
            'status' => 'in_progress'
        ]);

        $remarkData = [
            'remark' => 'Meeting completed successfully',
            'status' => 'completed'
        ];

        $response = $this->actingAs($this->employee)->postJson("/employee/update-remark/{$visitHistory->id}", $remarkData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Remark updated successfully'
                ]);

        $this->assertDatabaseHas('remarks', [
            'visit_history_id' => $visitHistory->id,
            'remark' => 'Meeting completed successfully',
            'status' => 'completed'
        ]);
    }

    /**
     * Test API validation errors
     */
    public function test_api_validation_errors()
    {
        $response = $this->actingAs($this->frontdesk)->postJson('/frontdesk/store-visitor', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'mobile', 'purpose']);
    }

    /**
     * Test unauthorized API access
     */
    public function test_unauthorized_api_access()
    {
        $response = $this->postJson('/frontdesk/check-mobile', [
            'mobile' => '9876543210'
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test role-based API access
     */
    public function test_role_based_api_access()
    {
        // Employee trying to access frontdesk API
        $response = $this->actingAs($this->employee)->postJson('/frontdesk/check-mobile', [
            'mobile' => '9876543210'
        ]);

        $response->assertStatus(403);
    }
}

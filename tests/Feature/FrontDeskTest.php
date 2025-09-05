<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\VmsUser;
use App\Models\Location;
use App\Models\Visitor;
use App\Models\VisitHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class FrontDeskTest extends TestCase
{
    use RefreshDatabase;

    protected $frontdesk;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->frontdesk = VmsUser::factory()->create([
            'role' => 'frontdesk',
            'password' => Hash::make('password123')
        ]);
    }

    /**
     * Test frontdesk can access dashboard
     */
    public function test_frontdesk_can_access_dashboard()
    {
        $response = $this->actingAs($this->frontdesk)->get('/frontdesk/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Front Desk Dashboard');
    }

    /**
     * Test frontdesk can access visitor form
     */
    public function test_frontdesk_can_access_visitor_form()
    {
        $response = $this->actingAs($this->frontdesk)->get('/frontdesk/visitor-form');
        $response->assertStatus(200);
        $response->assertSee('Visitor Registration');
    }

    /**
     * Test frontdesk can check mobile number
     */
    public function test_frontdesk_can_check_mobile()
    {
        $visitor = Visitor::factory()->create([
            'mobile' => '9876543210'
        ]);

        $response = $this->actingAs($this->frontdesk)->post('/frontdesk/check-mobile', [
            'mobile' => '9876543210'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['exists' => true]);
    }

    /**
     * Test frontdesk can add new location
     */
    public function test_frontdesk_can_add_location()
    {
        $locationData = [
            'name' => 'New Office',
            'address' => '456 Business Ave',
            'contact_person' => 'Jane Smith',
            'contact_number' => '9876543210'
        ];

        $response = $this->actingAs($this->frontdesk)->post('/frontdesk/add-location', $locationData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('locations', [
            'name' => 'New Office',
            'address' => '456 Business Ave'
        ]);
    }

    /**
     * Test frontdesk can store new visitor
     */
    public function test_frontdesk_can_store_visitor()
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

        $response = $this->actingAs($this->frontdesk)->post('/frontdesk/store-visitor', $visitorData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('visitors', [
            'name' => 'John Doe',
            'mobile' => '9876543210',
            'email' => 'john@example.com'
        ]);
    }

    /**
     * Test frontdesk can access search visitors page
     */
    public function test_frontdesk_can_access_search_visitors()
    {
        $response = $this->actingAs($this->frontdesk)->get('/frontdesk/search-visitors');
        $response->assertStatus(200);
        $response->assertSee('Search Visitors');
    }

    /**
     * Test frontdesk can search visitors
     */
    public function test_frontdesk_can_search_visitors()
    {
        $visitor = Visitor::factory()->create([
            'name' => 'John Doe',
            'mobile' => '9876543210'
        ]);

        $response = $this->actingAs($this->frontdesk)->post('/frontdesk/search-visitors', [
            'search_term' => 'John Doe'
        ]);

        $response->assertStatus(200);
        $response->assertSee($visitor->name);
    }

    /**
     * Test non-frontdesk users cannot access frontdesk routes
     */
    public function test_non_frontdesk_users_cannot_access_frontdesk_routes()
    {
        $admin = VmsUser::factory()->create(['role' => 'admin']);
        
        $response = $this->actingAs($admin)->get('/frontdesk/dashboard');
        $response->assertStatus(403);
    }

    /**
     * Test visitor registration with invalid data
     */
    public function test_visitor_registration_validation()
    {
        $response = $this->actingAs($this->frontdesk)->post('/frontdesk/store-visitor', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'mobile', 'purpose']);
    }
}

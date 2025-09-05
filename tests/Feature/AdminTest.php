<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\VmsUser;
use App\Models\Location;
use App\Models\Visitor;
use App\Models\VisitHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = VmsUser::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('password123')
        ]);
    }

    /**
     * Test admin can access dashboard
     */
    public function test_admin_can_access_dashboard()
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Admin Dashboard');
    }

    /**
     * Test admin can access search mobile form
     */
    public function test_admin_can_access_search_mobile_form()
    {
        $response = $this->actingAs($this->admin)->get('/admin/search-mobile');
        $response->assertStatus(200);
        $response->assertSee('Search by Mobile');
    }

    /**
     * Test admin can search visitors by mobile
     */
    public function test_admin_can_search_visitors_by_mobile()
    {
        $visitor = Visitor::factory()->create([
            'mobile' => '9876543210'
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/search-mobile', [
            'mobile' => '9876543210'
        ]);

        $response->assertStatus(200);
        $response->assertSee($visitor->name);
    }

    /**
     * Test admin can access manage users page
     */
    public function test_admin_can_access_manage_users()
    {
        $response = $this->actingAs($this->admin)->get('/admin/manage-users');
        $response->assertStatus(200);
        $response->assertSee('Manage Users');
    }

    /**
     * Test admin can create new user
     */
    public function test_admin_can_create_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'role' => 'employee',
            'password' => 'password123'
        ];

        $response = $this->actingAs($this->admin)->post('/admin/create-user', $userData);

        $response->assertRedirect();
        $this->assertDatabaseHas('vms_users', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'role' => 'employee'
        ]);
    }

    /**
     * Test admin can access manage locations page
     */
    public function test_admin_can_access_manage_locations()
    {
        $response = $this->actingAs($this->admin)->get('/admin/manage-locations');
        $response->assertStatus(200);
        $response->assertSee('Manage Locations');
    }

    /**
     * Test admin can create new location
     */
    public function test_admin_can_create_location()
    {
        $locationData = [
            'name' => 'Test Location',
            'address' => '123 Test Street',
            'contact_person' => 'John Doe',
            'contact_number' => '9876543210'
        ];

        $response = $this->actingAs($this->admin)->post('/admin/create-location', $locationData);

        $response->assertRedirect();
        $this->assertDatabaseHas('locations', [
            'name' => 'Test Location',
            'address' => '123 Test Street'
        ]);
    }

    /**
     * Test admin can access analytics page
     */
    public function test_admin_can_access_analytics()
    {
        $response = $this->actingAs($this->admin)->get('/admin/analytics');
        $response->assertStatus(200);
        $response->assertSee('Analytics');
    }

    /**
     * Test admin can export visitor profile
     */
    public function test_admin_can_export_visitor_profile()
    {
        $visitor = Visitor::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/visitor-profile/{$visitor->id}");

        $response->assertStatus(200);
        $response->assertSee($visitor->name);
    }

    /**
     * Test non-admin users cannot access admin routes
     */
    public function test_non_admin_users_cannot_access_admin_routes()
    {
        $frontdesk = VmsUser::factory()->create(['role' => 'frontdesk']);
        
        $response = $this->actingAs($frontdesk)->get('/admin/dashboard');
        $response->assertStatus(403);
    }
}

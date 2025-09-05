<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\VmsUser;
use App\Models\VisitHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class PaginationTest extends TestCase
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
     * Test that pagination component renders correctly
     */
    public function test_pagination_component_renders()
    {
        // Create more than 20 visits to trigger pagination
        VisitHistory::factory()->count(25)->create();

        $response = $this->actingAs($this->admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        
        // Check that pagination is present
        $response->assertSee('Previous');
        $response->assertSee('Next');
        $response->assertSee('1');
        $response->assertSee('2');
        
        // Check that results info is displayed
        $response->assertSee('Showing 1 to 20 of 25 results');
    }

    /**
     * Test pagination navigation works
     */
    public function test_pagination_navigation_works()
    {
        // Create more than 20 visits to trigger pagination
        VisitHistory::factory()->count(25)->create();

        // Test first page
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Showing 1 to 20 of 25 results');

        // Test second page
        $response = $this->actingAs($this->admin)->get('/admin/dashboard?page=2');
        $response->assertStatus(200);
        $response->assertSee('Showing 21 to 25 of 25 results');
    }
}

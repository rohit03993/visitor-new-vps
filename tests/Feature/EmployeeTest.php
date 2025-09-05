<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\VmsUser;
use App\Models\Visitor;
use App\Models\VisitHistory;
use App\Models\Remark;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected $employee;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->employee = VmsUser::factory()->create([
            'role' => 'employee',
            'password' => Hash::make('password123')
        ]);
    }

    /**
     * Test employee can access dashboard
     */
    public function test_employee_can_access_dashboard()
    {
        $response = $this->actingAs($this->employee)->get('/employee/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Employee Dashboard');
    }

    /**
     * Test employee can update remark for a visit
     */
    public function test_employee_can_update_remark()
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

        $response = $this->actingAs($this->employee)->post("/employee/update-remark/{$visitHistory->id}", $remarkData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('remarks', [
            'visit_history_id' => $visitHistory->id,
            'remark' => 'Meeting completed successfully'
        ]);
    }

    /**
     * Test employee can view visitor history
     */
    public function test_employee_can_view_visitor_history()
    {
        $visitor = Visitor::factory()->create();
        $visitHistory = VisitHistory::factory()->create([
            'visitor_id' => $visitor->id
        ]);

        $response = $this->actingAs($this->employee)->get("/employee/visitor-history/{$visitor->id}");

        $response->assertStatus(200);
        $response->assertSee($visitor->name);
    }

    /**
     * Test employee cannot access other role routes
     */
    public function test_employee_cannot_access_other_role_routes()
    {
        // Try to access admin route
        $response = $this->actingAs($this->employee)->get('/admin/dashboard');
        $response->assertStatus(403);

        // Try to access frontdesk route
        $response = $this->actingAs($this->employee)->get('/frontdesk/dashboard');
        $response->assertStatus(403);
    }

    /**
     * Test remark update validation
     */
    public function test_remark_update_validation()
    {
        $visitHistory = VisitHistory::factory()->create();

        $response = $this->actingAs($this->employee)->post("/employee/update-remark/{$visitHistory->id}", []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['remark']);
    }

    /**
     * Test employee can only update remarks for valid visit history
     */
    public function test_employee_cannot_update_invalid_visit_history()
    {
        $response = $this->actingAs($this->employee)->post('/employee/update-remark/99999', [
            'remark' => 'Test remark'
        ]);

        $response->assertStatus(404);
    }
}

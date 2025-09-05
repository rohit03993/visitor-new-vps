<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\VmsUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login page loads correctly
     */
    public function test_login_page_loads()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Login');
    }

    /**
     * Test successful login for admin user
     */
    public function test_admin_can_login_successfully()
    {
        $admin = VmsUser::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('password123'),
            'email' => 'admin@test.com'
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticated();
    }

    /**
     * Test successful login for frontdesk user
     */
    public function test_frontdesk_can_login_successfully()
    {
        $frontdesk = VmsUser::factory()->create([
            'role' => 'frontdesk',
            'password' => Hash::make('password123'),
            'email' => 'frontdesk@test.com'
        ]);

        $response = $this->post('/login', [
            'email' => 'frontdesk@test.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/frontdesk/dashboard');
        $this->assertAuthenticated();
    }

    /**
     * Test successful login for employee user
     */
    public function test_employee_can_login_successfully()
    {
        $employee = VmsUser::factory()->create([
            'role' => 'employee',
            'password' => Hash::make('password123'),
            'email' => 'employee@test.com'
        ]);

        $response = $this->post('/login', [
            'email' => 'employee@test.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/employee/dashboard');
        $this->assertAuthenticated();
    }

    /**
     * Test login fails with invalid credentials
     */
    public function test_login_fails_with_invalid_credentials()
    {
        $user = VmsUser::factory()->create([
            'password' => Hash::make('password123')
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrongpassword'
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /**
     * Test logout functionality
     */
    public function test_user_can_logout()
    {
        $user = VmsUser::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * Test unauthenticated users are redirected to login
     */
    public function test_unauthenticated_users_redirected_to_login()
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    /**
     * Test root route redirects to login
     */
    public function test_root_route_redirects_to_login()
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }
}

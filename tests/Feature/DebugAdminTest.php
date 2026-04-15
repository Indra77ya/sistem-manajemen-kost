<?php

namespace Tests\Feature;

use Tests\TestCase;

class DebugAdminTest extends TestCase
{
    public function test_admin_login_page_loads()
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
    }

    public function test_admin_dashboard_redirects_to_login()
    {
        $response = $this->get('/admin');
        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
    }

    public function test_developer_can_access_dashboard()
    {
        $user = \App\Models\User::withoutGlobalScopes()->where('role', 'developer')->first();
        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(200);
    }
}

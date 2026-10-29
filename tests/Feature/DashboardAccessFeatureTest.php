<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAccessFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_dashboard_or_graphs(): void
    {
        foreach ([
            'public.dashboard',
            'dashboard.graph.byday',
            'dashboard.graph.topvehicles',
            'dashboard.graph.topkm',
            'dashboard.graph.topdrivers',
            'dashboard.graph.status',
        ] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
    }

    public function test_dashboard_remains_the_home_page_after_login(): void
    {
        User::factory()->create(['username' => 'dashboard-user', 'active' => true]);

        $this->post(route('login.post'), [
            'username' => 'dashboard-user',
            'password' => 'password',
        ])->assertRedirect(route('public.dashboard'));

    }

    public function test_authenticated_users_can_view_dashboard(): void
    {
        $user = User::factory()->create(['active' => true]);

        $this->actingAs($user)->get(route('public.dashboard'))
            ->assertOk()
            ->assertSee('Panel principal');
    }
}

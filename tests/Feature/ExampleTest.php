<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_guest_tv_monitor_dashboard(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Alpha Theraphy');
        $response->assertSee('Live Display');
        $response->assertSee('Kursi Terapi');
    }

    public function test_homepage_json_sync_endpoint_works(): void
    {
        $response = $this->getJson('/?json=1');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'kursis',
            'kursiTerpakai',
            'kursiKosong',
            'totalKursi',
            'serverTime',
        ]);
    }

    public function test_login_page_returns_successful_response(): void
    {
        $response = $this->get('/althe/login');

        $response->assertStatus(200);
        $response->assertSee('Alpha Theraphy');
    }
}

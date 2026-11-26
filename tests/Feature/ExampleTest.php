<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('Masuk sebagai Pembeli')
            ->assertSee(route('dashboard.pembeli'), false);
    }

    public function test_buyer_can_open_the_dashboard_without_logging_in(): void
    {
        $this->get(route('dashboard.pembeli'))
            ->assertOk()
            ->assertSee('Dashboard Pembeli')
            ->assertSee('Selamat datang, Pembeli!');

        $this->assertGuest();
    }
}

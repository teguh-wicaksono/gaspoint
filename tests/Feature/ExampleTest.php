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

    public function test_buyer_sees_only_the_order_form_without_logging_in(): void
    {
        $this->get(route('dashboard.pembeli'))
            ->assertOk()
            ->assertSee('Pesan Gas &amp; Galon', false)
            ->assertSee(route('pembeli.store'), false)
            ->assertSee('name="pembayaran"', false)
            ->assertDontSee('Riwayat Pesanan')
            ->assertSee(route('welcome'), false);

        $this->assertGuest();
    }
}

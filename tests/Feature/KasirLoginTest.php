<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasirLoginTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role, string $email): User
    {
        return User::create([
            'name' => ucfirst($role),
            'email' => $email,
            'password' => 'password123',
            'role' => $role,
        ]);
    }

    public function test_landing_page_has_kasir_entry(): void
    {
        $this->get(route('welcome'))
            ->assertOk()
            ->assertSee('Masuk sebagai Kasir')
            ->assertSee(route('login', ['sebagai' => 'kasir']), false);
    }

    public function test_kasir_login_page_shows_kasir_form(): void
    {
        $this->get(route('login', ['sebagai' => 'kasir']))
            ->assertOk()
            ->assertSee('Login Kasir')
            ->assertSee('name="sebagai"', false);
    }

    public function test_kasir_can_login_through_kasir_entry(): void
    {
        $this->makeUser(User::ROLE_KASIR, 'kasir@rumahgas.test');

        $this->post(route('login.post'), [
            'email' => 'kasir@rumahgas.test',
            'password' => 'password123',
            'sebagai' => 'kasir',
        ])->assertRedirect(route('kasir.index'));

        $this->assertAuthenticated();
    }

    public function test_non_kasir_cannot_login_through_kasir_entry(): void
    {
        $this->makeUser(User::ROLE_PENGELOLA, 'admin@rumahgas.test');

        $this->post(route('login.post'), [
            'email' => 'admin@rumahgas.test',
            'password' => 'password123',
            'sebagai' => 'kasir',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_button_is_on_pages_and_returns_to_landing_page(): void
    {
        $kasir = $this->makeUser(User::ROLE_KASIR, 'kasir@rumahgas.test');

        foreach (['kasir.index', 'dashboard', 'pesanan.index', 'barang.index', 'profile.index'] as $name) {
            $this->actingAs($kasir)->get(route($name))
                ->assertOk()
                ->assertSee(route('logout'), false);
        }

        $this->actingAs($kasir)->post(route('logout'))->assertRedirect(route('welcome'));
        $this->assertGuest();
    }

    public function test_forbidden_page_has_logout_button(): void
    {
        $kasir = $this->makeUser(User::ROLE_KASIR, 'kasir@rumahgas.test');

        $this->actingAs($kasir)->get(route('pengguna.index'))
            ->assertForbidden()
            ->assertSee(route('logout'), false);
    }
}

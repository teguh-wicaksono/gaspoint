<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasirPageTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::create(['name' => ucfirst($role), 'email' => "$role@rumahgas.test", 'password' => 'password123', 'role' => $role]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('kasir.index'))->assertRedirect(route('login'));
    }

    public function test_kasir_sees_products_and_pengelola_may_too(): void
    {
        Barang::create(['nama_barang' => 'Gas 3 kg', 'jenis' => 'gas', 'harga' => 22000, 'stok' => 5]);

        $this->actingAs($this->user(User::ROLE_KASIR))->get(route('kasir.index'))
            ->assertOk()->assertSee('Gas 3 kg')->assertSee(route('pesanan.store'), false);

        $this->actingAs($this->user(User::ROLE_PENGELOLA))->get(route('kasir.index'))->assertOk();
    }

    public function test_kurir_cannot_open_kasir_page(): void
    {
        $this->actingAs($this->user(User::ROLE_KURIR))->get(route('kasir.index'))->assertForbidden();
    }

    public function test_logged_in_kasir_visiting_login_goes_to_kasir_page(): void
    {
        $this->actingAs($this->user(User::ROLE_KASIR))->get(route('login'))->assertRedirect(route('kasir.index'));
    }

    public function test_pengelola_login_still_goes_to_dashboard(): void
    {
        $this->user(User::ROLE_PENGELOLA);

        $this->post(route('login.post'), ['email' => 'pengelola@rumahgas.test', 'password' => 'password123'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_order_from_kasir_page_returns_to_kasir_page(): void
    {
        $gas = Barang::create(['nama_barang' => 'Gas 3 kg', 'jenis' => 'gas', 'harga' => 22000, 'stok' => 5]);

        $this->actingAs($this->user(User::ROLE_KASIR))->post(route('pesanan.store'), [
            'dari'         => 'kasir',
            'nama_pembeli' => 'Budi',
            'no_hp'        => '081234567890',
            'alamat'       => 'Jl. Mawar 1',
            'tanggal'      => now()->toDateString(),
            'items'        => [['id_barang' => $gas->id_barang, 'jumlah' => 2]],
        ])->assertRedirect(route('kasir.index'))->assertSessionHas('success');

        $this->assertSame(44000, Pesanan::firstOrFail()->total_harga);
        $this->assertSame(3, $gas->fresh()->stok);
    }
}

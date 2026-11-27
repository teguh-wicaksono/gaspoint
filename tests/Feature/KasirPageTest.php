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

    public function test_kasir_page_has_cash_qris_and_no_antar_or_langsung_choice(): void
    {
        $this->actingAs($this->user(User::ROLE_KASIR))->get(route('kasir.index'))
            ->assertOk()
            ->assertSee('value="cash"', false)
            ->assertSee('value="qris"', false)
            ->assertSee('<input type="hidden" name="metode" value="langsung">', false)
            ->assertDontSee('metode-antar', false)
            ->assertDontSee('Beli langsung')
            ->assertDontSee('name="alamat"', false);
    }

    public function test_order_from_kasir_page_is_completed_at_once_and_returns_to_kasir_page(): void
    {
        $gas = Barang::create(['nama_barang' => 'Gas 3 kg', 'jenis' => 'gas', 'harga' => 22000, 'stok' => 5]);

        $this->actingAs($this->user(User::ROLE_KASIR))->post(route('pesanan.store'), [
            'dari'       => 'kasir',
            'metode'     => 'langsung',
            'pembayaran' => 'qris',
            'tanggal'    => now()->toDateString(),
            'items'      => [['id_barang' => $gas->id_barang, 'jumlah' => 2]],
        ])->assertRedirect(route('kasir.index'))->assertSessionHas('success');

        $pesanan = Pesanan::firstOrFail();
        $this->assertSame(44000, $pesanan->total_harga);
        $this->assertSame('qris', $pesanan->pembayaran);
        $this->assertSame('selesai', $pesanan->status);
        $this->assertFalse($pesanan->menunggu_persetujuan);
        $this->assertSame(3, $gas->fresh()->stok);
    }

    public function test_kasir_order_requires_cash_or_qris(): void
    {
        $gas = Barang::create(['nama_barang' => 'Gas 3 kg', 'jenis' => 'gas', 'harga' => 22000, 'stok' => 5]);
        $kasir = $this->actingAs($this->user(User::ROLE_KASIR));
        $data = ['dari' => 'kasir', 'metode' => 'langsung', 'tanggal' => now()->toDateString(),
                 'items' => [['id_barang' => $gas->id_barang, 'jumlah' => 1]]];

        $kasir->post(route('pesanan.store'), $data)->assertSessionHasErrors('pembayaran');
        $kasir->post(route('pesanan.store'), $data + ['pembayaran' => 'cod'])->assertSessionHasErrors('pembayaran');

        $this->assertSame(0, Pesanan::count());
        $this->assertSame(5, $gas->fresh()->stok);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KurirPageTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::create(['name' => ucfirst($role), 'email' => "$role@rumahgas.test", 'password' => 'password123', 'role' => $role]);
    }

    private function pesanan(string $status, string $nama = 'Budi'): Pesanan
    {
        return Pesanan::create([
            'nama_pembeli' => $nama, 'no_hp' => '081234567890', 'alamat' => 'Jl. Mawar 1',
            'tanggal' => now()->toDateString(), 'total_harga' => 44000, 'status' => $status,
        ]);
    }

    public function test_kurir_login_entry_goes_to_kurir_page(): void
    {
        $this->user(User::ROLE_KURIR);

        $this->get(route('login', ['sebagai' => 'kurir']))
            ->assertOk()->assertSee('Login Kurir')->assertSee('name="sebagai"', false);

        $this->post(route('login.post'), ['email' => 'kurir@rumahgas.test', 'password' => 'password123', 'sebagai' => 'kurir'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_non_kurir_cannot_login_through_kurir_entry(): void
    {
        $this->user(User::ROLE_KASIR);

        $this->post(route('login.post'), ['email' => 'kasir@rumahgas.test', 'password' => 'password123', 'sebagai' => 'kurir'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_kurir_page_lists_orders_to_deliver(): void
    {
        $menunggu = $this->pesanan('pending');
        $this->pesanan('selesai', 'Sari');

        $this->actingAs($this->user(User::ROLE_KURIR))->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Budi')
            ->assertSee('Jl. Mawar 1')
            ->assertSee(route('pesanan.status', $menunggu), false)
            ->assertSee('google.com/maps/search', false);
    }

    public function test_kurir_can_advance_order_status(): void
    {
        $pesanan = $this->pesanan('pending');
        $this->actingAs($this->user(User::ROLE_KURIR));

        $this->post(route('pesanan.status', $pesanan))->assertSessionHas('success');
        $this->assertSame('diproses', $pesanan->fresh()->status);

        $this->post(route('pesanan.status', $pesanan));
        $this->assertSame('selesai', $pesanan->fresh()->status);
    }

    public function test_rute_page_lists_orders_being_delivered_with_maps_link(): void
    {
        $this->pesanan('diproses');
        $this->pesanan('pending', 'Sari');

        $kurir = $this->user(User::ROLE_KURIR);

        $this->actingAs($kurir)->get(route('kurir.rute'))
            ->assertOk()
            ->assertSee('Budi')
            ->assertDontSee('Sari')
            ->assertSee('google.com/maps/dir', false);

        $this->actingAs($kurir)->get(route('dashboard'))->assertSee(route('kurir.rute'), false);
    }

    public function test_kasir_cannot_open_rute_page(): void
    {
        $this->actingAs($this->user(User::ROLE_KASIR))->get(route('kurir.rute'))->assertForbidden();
    }

    private function barang(): \App\Models\Barang
    {
        return \App\Models\Barang::create(['nama_barang' => 'Gas 3 kg', 'jenis' => 'gas', 'harga' => 22000, 'stok' => 10]);
    }

    public function test_antar_order_from_kasir_goes_to_kurir_queue_and_needs_address(): void
    {
        $gas = $this->barang();
        $kasir = $this->user(User::ROLE_KASIR);
        $data = ['metode' => 'antar', 'nama_pembeli' => 'Budi', 'no_hp' => '081234567890', 'tanggal' => now()->toDateString(),
                 'items' => [['id_barang' => $gas->id_barang, 'jumlah' => 1]]];

        $this->actingAs($kasir)->post(route('pesanan.store'), $data)->assertSessionHasErrors('alamat');

        $this->actingAs($kasir)->post(route('pesanan.store'), $data + ['alamat' => 'Jl. Mawar 1']);
        $this->assertSame('pending', Pesanan::firstOrFail()->status);

        $this->actingAs($this->user(User::ROLE_KURIR))->get(route('dashboard'))->assertSee('Jl. Mawar 1');
    }

    public function test_langsung_order_is_completed_at_once_and_skips_kurir(): void
    {
        $gas = $this->barang();

        $this->actingAs($this->user(User::ROLE_KASIR))->post(route('pesanan.store'), [
            'metode' => 'langsung', 'tanggal' => now()->toDateString(),
            'items' => [['id_barang' => $gas->id_barang, 'jumlah' => 2]],
        ])->assertSessionHasNoErrors();

        $pesanan = Pesanan::firstOrFail();
        $this->assertSame('selesai', $pesanan->status);
        $this->assertSame('langsung', $pesanan->metode);
        $this->assertSame(8, $gas->fresh()->stok);

        $this->actingAs($this->user(User::ROLE_KURIR))->get(route('dashboard'))->assertDontSee('Pembeli langsung');
    }
}

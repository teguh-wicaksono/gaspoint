<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembeliPesanTest extends TestCase
{
    use RefreshDatabase;

    private Barang $gas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gas = Barang::create(['nama_barang' => 'Gas 3 kg', 'jenis' => 'gas', 'harga' => 22000, 'stok' => 10]);
    }

    private function user(string $role): User
    {
        return User::create(['name' => ucfirst($role), 'email' => "$role@rumahgas.test", 'password' => 'password123', 'role' => $role]);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'nama_pembeli' => 'Budi',
            'no_hp'        => '081234567890',
            'alamat'       => 'Jl. Mawar 1',
            'pembayaran'   => 'transfer',
            'jumlah'       => [$this->gas->id_barang => 2],
        ], $override);
    }

    private function pesananOnline(): Pesanan
    {
        $this->post(route('pembeli.store'), $this->payload())->assertSessionHasNoErrors();

        return Pesanan::firstOrFail();
    }

    public function test_form_lists_products_and_cod_transfer_qris(): void
    {
        $this->get(route('dashboard.pembeli'))
            ->assertOk()
            ->assertSee('Gas 3 kg')
            ->assertSee('value="cod"', false)
            ->assertSee('value="transfer"', false)
            ->assertSee('value="qris"', false)
            ->assertDontSee('value="cash"', false);
    }

    public function test_guest_order_waits_for_kasir_and_reserves_stock(): void
    {
        $this->post(route('pembeli.store'), $this->payload())
            ->assertRedirect(route('dashboard.pembeli'))
            ->assertSessionHas('pesanan_baru');

        $p = Pesanan::firstOrFail();
        $this->assertSame('antar', $p->metode);
        $this->assertSame('transfer', $p->pembayaran);
        $this->assertSame('pending', $p->status);
        $this->assertTrue($p->menunggu_persetujuan);
        $this->assertSame(44000, $p->total_harga);
        $this->assertSame(8, $this->gas->fresh()->stok);
        $this->assertGuest();

        $this->followingRedirects()->get(route('dashboard.pembeli'))->assertSee('Pesanan #' . $p->kode . ' diterima');
    }

    public function test_zero_quantity_rows_are_ignored_and_price_comes_from_database(): void
    {
        $galon = Barang::create(['nama_barang' => 'Galon', 'jenis' => 'galon', 'harga' => 20000, 'stok' => 5]);

        $this->post(route('pembeli.store'), $this->payload(['jumlah' => [$this->gas->id_barang => 1, $galon->id_barang => 0], 'total_harga' => 1]))
            ->assertSessionHasNoErrors();

        $p = Pesanan::firstOrFail();
        $this->assertSame(22000, $p->total_harga);
        $this->assertSame(1, $p->detail()->count());
        $this->assertSame(5, $galon->fresh()->stok);
    }

    public function test_validation_rejects_bad_orders_without_touching_stock(): void
    {
        $this->post(route('pembeli.store'), $this->payload(['pembayaran' => 'cash']))->assertSessionHasErrors('pembayaran');
        $this->post(route('pembeli.store'), $this->payload(['pembayaran' => null]))->assertSessionHasErrors('pembayaran');
        $this->post(route('pembeli.store'), $this->payload(['alamat' => '']))->assertSessionHasErrors('alamat');
        $this->post(route('pembeli.store'), $this->payload(['jumlah' => [$this->gas->id_barang => 0]]))->assertSessionHasErrors('items');
        $this->post(route('pembeli.store'), $this->payload(['jumlah' => [$this->gas->id_barang => 11]]))->assertSessionHasErrors('items');
        $this->post(route('pembeli.store'), $this->payload(['jumlah' => [999 => 1]]))->assertSessionHasErrors('items.0.id_barang');

        $this->assertSame(0, Pesanan::count());
        $this->assertSame(10, $this->gas->fresh()->stok);
    }

    public function test_online_order_is_hidden_from_kurir_until_kasir_approves(): void
    {
        $p = $this->pesananOnline();
        $kurir = $this->user(User::ROLE_KURIR);

        $this->actingAs($kurir)->get(route('dashboard'))->assertDontSee('Jl. Mawar 1');
        $this->actingAs($kurir)->post(route('pesanan.status', $p))->assertSessionHas('error');
        $this->assertSame('pending', $p->fresh()->status);

        $this->actingAs($kurir)->post(route('pesanan.setujui', $p))->assertForbidden();

        $this->actingAs($this->user(User::ROLE_KASIR))->post(route('pesanan.setujui', $p))->assertSessionHas('success');
        $this->assertFalse($p->fresh()->menunggu_persetujuan);

        $this->actingAs($kurir)->get(route('dashboard'))->assertSee('Jl. Mawar 1');
        $this->actingAs($kurir)->post(route('pesanan.status', $p));
        $this->assertSame('diproses', $p->fresh()->status);
    }

    public function test_kasir_sees_waiting_orders_and_can_reject_to_restore_stock(): void
    {
        $p = $this->pesananOnline();
        $kasir = $this->user(User::ROLE_KASIR);

        $this->actingAs($kasir)->get(route('kasir.index'))
            ->assertSee('Pesanan online menunggu persetujuan')
            ->assertSee(route('pesanan.setujui', $p), false)
            ->assertSee('Transfer');
        $this->actingAs($kasir)->get(route('kasir.online'))->assertOk()->assertSee('Budi');

        $this->actingAs($kasir)->delete(route('pesanan.destroy', $p))->assertSessionHas('success');
        $this->assertSame(0, Pesanan::count());
        $this->assertSame(10, $this->gas->fresh()->stok);

        $this->actingAs($kasir)->get(route('kasir.online'))->assertDontSee('Budi');
    }

    public function test_kurir_cannot_open_kasir_online_list_and_approval_only_works_once(): void
    {
        $p = $this->pesananOnline();

        $this->actingAs($this->user(User::ROLE_KURIR))->get(route('kasir.online'))->assertForbidden();

        $kasir = $this->actingAs($this->user(User::ROLE_KASIR));
        $kasir->post(route('pesanan.setujui', $p))->assertSessionHas('success');
        $kasir->post(route('pesanan.setujui', $p))->assertSessionHas('error');
    }

    public function test_staff_created_delivery_order_needs_no_approval(): void
    {
        $this->actingAs($this->user(User::ROLE_KASIR))->post(route('pesanan.store'), [
            'metode' => 'antar', 'nama_pembeli' => 'Sari', 'no_hp' => '081234567890', 'alamat' => 'Jl. Melati 2',
            'tanggal' => now()->toDateString(), 'items' => [['id_barang' => $this->gas->id_barang, 'jumlah' => 1]],
        ])->assertSessionHasNoErrors();

        $this->assertFalse(Pesanan::firstOrFail()->menunggu_persetujuan);
        $this->actingAs($this->user(User::ROLE_KURIR))->get(route('dashboard'))->assertSee('Jl. Melati 2');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesananTest extends TestCase
{
    use RefreshDatabase;

    private function login(): User
    {
        $user = User::create(['name' => 'Kasir', 'email' => 'kasir@rumahgas.test', 'password' => 'password123']);
        $this->actingAs($user);

        return $user;
    }

    private function payload(array $items, array $override = []): array
    {
        return array_merge([
            'nama_pembeli' => 'Budi',
            'no_hp'        => '081234567890',
            'alamat'       => 'Jl. Mawar No. 1',
            'tanggal'      => now()->toDateString(),
            'items'        => $items,
        ], $override);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('pesanan.index'))->assertRedirect(route('login'));
    }

    public function test_index_and_create_pages_load(): void
    {
        $this->login();

        $this->get(route('pesanan.index'))->assertOk();
        $this->get(route('pesanan.create'))->assertOk();
    }

    public function test_store_creates_order_and_reduces_stock(): void
    {
        $this->login();
        $gas = Barang::create(['nama_barang' => 'Gas 3 kg', 'jenis' => 'gas', 'harga' => 22000, 'stok' => 10]);
        $galon = Barang::create(['nama_barang' => 'Galon', 'jenis' => 'galon', 'harga' => 20000, 'stok' => 5]);

        $response = $this->post(route('pesanan.store'), $this->payload([
            ['id_barang' => $gas->id_barang, 'jumlah' => 2],
            ['id_barang' => $galon->id_barang, 'jumlah' => 1],
        ]));

        $pesanan = Pesanan::firstOrFail();
        $response->assertRedirect(route('pesanan.show', $pesanan));

        $this->assertSame('pending', $pesanan->status);
        $this->assertSame(64000, $pesanan->total_harga);
        $this->assertCount(2, $pesanan->detail);
        $this->assertSame(8, $gas->fresh()->stok);
        $this->assertSame(4, $galon->fresh()->stok);
    }

    public function test_price_comes_from_database_not_from_request(): void
    {
        $this->login();
        $gas = Barang::create(['nama_barang' => 'Gas 3 kg', 'jenis' => 'gas', 'harga' => 22000, 'stok' => 10]);

        $this->post(route('pesanan.store'), $this->payload([
            ['id_barang' => $gas->id_barang, 'jumlah' => 1, 'harga' => 1],
        ]));

        $this->assertSame(22000, Pesanan::firstOrFail()->total_harga);
    }

    public function test_duplicate_lines_are_merged_before_stock_check(): void
    {
        $this->login();
        $gas = Barang::create(['nama_barang' => 'Gas 3 kg', 'jenis' => 'gas', 'harga' => 22000, 'stok' => 5]);

        $response = $this->from(route('pesanan.create'))->post(route('pesanan.store'), $this->payload([
            ['id_barang' => $gas->id_barang, 'jumlah' => 3],
            ['id_barang' => $gas->id_barang, 'jumlah' => 3],
        ]));

        $response->assertSessionHasErrors('items');
        $this->assertSame(0, Pesanan::count());
        $this->assertSame(5, $gas->fresh()->stok);
    }

    public function test_store_rejects_insufficient_stock(): void
    {
        $this->login();
        $gas = Barang::create(['nama_barang' => 'Gas 3 kg', 'jenis' => 'gas', 'harga' => 22000, 'stok' => 2]);

        $this->post(route('pesanan.store'), $this->payload([
            ['id_barang' => $gas->id_barang, 'jumlah' => 3],
        ]))->assertSessionHasErrors('items');

        $this->assertSame(0, Pesanan::count());
        $this->assertSame(2, $gas->fresh()->stok);
    }

    public function test_store_requires_items_and_buyer_data(): void
    {
        $this->login();

        $this->post(route('pesanan.store'), [])
            ->assertSessionHasErrors(['nama_pembeli', 'no_hp', 'alamat', 'tanggal', 'items']);
    }

    public function test_status_moves_forward_only(): void
    {
        $this->login();
        $gas = Barang::create(['nama_barang' => 'Gas 3 kg', 'jenis' => 'gas', 'harga' => 22000, 'stok' => 10]);
        $this->post(route('pesanan.store'), $this->payload([['id_barang' => $gas->id_barang, 'jumlah' => 1]]));
        $pesanan = Pesanan::firstOrFail();

        $this->post(route('pesanan.status', $pesanan));
        $this->assertSame('diproses', $pesanan->fresh()->status);

        $this->post(route('pesanan.status', $pesanan));
        $this->assertSame('selesai', $pesanan->fresh()->status);

        $this->post(route('pesanan.status', $pesanan))->assertSessionHas('error');
        $this->assertSame('selesai', $pesanan->fresh()->status);
    }

    public function test_cancelling_order_restores_stock(): void
    {
        $this->login();
        $gas = Barang::create(['nama_barang' => 'Gas 3 kg', 'jenis' => 'gas', 'harga' => 22000, 'stok' => 10]);
        $this->post(route('pesanan.store'), $this->payload([['id_barang' => $gas->id_barang, 'jumlah' => 4]]));
        $pesanan = Pesanan::firstOrFail();
        $this->assertSame(6, $gas->fresh()->stok);

        $this->delete(route('pesanan.destroy', $pesanan))->assertRedirect(route('pesanan.index'));

        $this->assertSame(0, Pesanan::count());
        $this->assertSame(10, $gas->fresh()->stok);
    }

    public function test_completed_order_cannot_be_deleted(): void
    {
        $this->login();
        $gas = Barang::create(['nama_barang' => 'Gas 3 kg', 'jenis' => 'gas', 'harga' => 22000, 'stok' => 10]);
        $this->post(route('pesanan.store'), $this->payload([['id_barang' => $gas->id_barang, 'jumlah' => 1]]));
        $pesanan = Pesanan::firstOrFail();
        $pesanan->update(['status' => 'selesai']);

        $this->delete(route('pesanan.destroy', $pesanan))->assertSessionHas('error');

        $this->assertSame(1, Pesanan::count());
        $this->assertSame(9, $gas->fresh()->stok);
    }

    public function test_show_page_and_dashboard_render(): void
    {
        $this->login();
        $gas = Barang::create(['nama_barang' => 'Gas 3 kg', 'jenis' => 'gas', 'harga' => 22000, 'stok' => 10]);
        $this->post(route('pesanan.store'), $this->payload([['id_barang' => $gas->id_barang, 'jumlah' => 1]]));
        $pesanan = Pesanan::firstOrFail();

        $this->get(route('pesanan.show', $pesanan))->assertOk()->assertSee('Budi');
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('pesanan.index', ['search' => 'Budi', 'status' => 'pending']))->assertOk()->assertSee('Budi');
    }
}

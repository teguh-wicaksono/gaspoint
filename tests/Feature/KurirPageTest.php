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
}

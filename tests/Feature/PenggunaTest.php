<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenggunaTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role, ?string $email = null): User
    {
        return User::create([
            'name' => ucfirst($role) . ' Test',
            'email' => $email ?? $role . '@rumahgas.test',
            'password' => 'password123',
            'role' => $role,
        ]);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'name' => 'Pengguna Baru',
            'email' => 'baru@rumahgas.test',
            'role' => User::ROLE_KASIR,
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ], $override);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('pengguna.index'))->assertRedirect(route('login'));
    }

    public function test_kasir_and_kurir_cannot_access_kelola_pengguna(): void
    {
        foreach ([User::ROLE_KASIR, User::ROLE_KURIR] as $role) {
            $this->actingAs($this->makeUser($role));

            $this->get(route('pengguna.index'))->assertForbidden();
            $this->get(route('pengguna.create'))->assertForbidden();
            $this->post(route('pengguna.store'), $this->payload())->assertForbidden();
        }

        $this->assertDatabaseMissing('users', ['email' => 'baru@rumahgas.test']);
    }

    public function test_pengelola_sees_menu_and_pages(): void
    {
        $this->actingAs($this->makeUser(User::ROLE_PENGELOLA));

        $this->get(route('dashboard'))->assertOk()->assertSee('Kelola Pengguna');
        $this->get(route('pengguna.index'))->assertOk();
        $this->get(route('pengguna.create'))->assertOk();
    }

    public function test_menu_is_hidden_for_kasir(): void
    {
        $this->actingAs($this->makeUser(User::ROLE_KASIR));

        $this->get(route('dashboard'))->assertOk()->assertDontSee('Kelola Pengguna');
    }

    public function test_pengelola_can_create_each_role_and_password_is_hashed(): void
    {
        $this->actingAs($this->makeUser(User::ROLE_PENGELOLA));

        foreach (array_keys(User::ROLES) as $role) {
            $this->post(route('pengguna.store'), $this->payload([
                'email' => "$role.baru@rumahgas.test",
                'role' => $role,
            ]))->assertRedirect(route('pengguna.index'));

            $this->assertDatabaseHas('users', ['email' => "$role.baru@rumahgas.test", 'role' => $role]);
        }

        $created = User::where('email', 'kasir.baru@rumahgas.test')->first();
        $this->assertNotSame('rahasia123', $created->password);
        $this->assertTrue(\Hash::check('rahasia123', $created->password));
    }

    public function test_pembeli_is_not_a_valid_role(): void
    {
        $this->actingAs($this->makeUser(User::ROLE_PENGELOLA));

        $this->post(route('pengguna.store'), $this->payload(['role' => 'pembeli']))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'baru@rumahgas.test']);
    }

    public function test_store_validates_unique_email_and_password(): void
    {
        $this->actingAs($this->makeUser(User::ROLE_PENGELOLA));
        $this->makeUser(User::ROLE_KASIR, 'baru@rumahgas.test');

        $this->post(route('pengguna.store'), $this->payload())->assertSessionHasErrors('email');
        $this->post(route('pengguna.store'), $this->payload([
            'email' => 'lain@rumahgas.test',
            'password_confirmation' => 'beda',
        ]))->assertSessionHasErrors('password');
    }

    public function test_update_changes_role_and_keeps_password_when_blank(): void
    {
        $this->actingAs($this->makeUser(User::ROLE_PENGELOLA));
        $kasir = $this->makeUser(User::ROLE_KASIR);
        $oldHash = $kasir->password;

        $this->put(route('pengguna.update', $kasir), [
            'name' => 'Kasir Diubah',
            'email' => $kasir->email,
            'role' => User::ROLE_KURIR,
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect(route('pengguna.index'));

        $kasir->refresh();
        $this->assertSame('Kasir Diubah', $kasir->name);
        $this->assertSame(User::ROLE_KURIR, $kasir->role);
        $this->assertSame($oldHash, $kasir->password);
    }

    public function test_update_can_reset_password(): void
    {
        $this->actingAs($this->makeUser(User::ROLE_PENGELOLA));
        $kurir = $this->makeUser(User::ROLE_KURIR);

        $this->put(route('pengguna.update', $kurir), [
            'name' => $kurir->name,
            'email' => $kurir->email,
            'role' => $kurir->role,
            'password' => 'passwordbaru1',
            'password_confirmation' => 'passwordbaru1',
        ])->assertRedirect(route('pengguna.index'));

        $this->assertTrue(\Hash::check('passwordbaru1', $kurir->fresh()->password));
    }

    public function test_pengelola_cannot_change_own_role(): void
    {
        $admin = $this->makeUser(User::ROLE_PENGELOLA);
        $this->actingAs($admin);

        $this->put(route('pengguna.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => User::ROLE_KASIR,
        ])->assertSessionHas('error');

        $this->assertSame(User::ROLE_PENGELOLA, $admin->fresh()->role);
    }

    public function test_pengelola_can_delete_others_but_not_self(): void
    {
        $admin = $this->makeUser(User::ROLE_PENGELOLA);
        $kasir = $this->makeUser(User::ROLE_KASIR);
        $this->actingAs($admin);

        $this->delete(route('pengguna.destroy', $admin))->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        $this->delete(route('pengguna.destroy', $kasir))->assertRedirect(route('pengguna.index'));
        $this->assertDatabaseMissing('users', ['id' => $kasir->id]);
    }

    public function test_index_filters_by_role_and_search(): void
    {
        $this->actingAs($this->makeUser(User::ROLE_PENGELOLA));
        $this->makeUser(User::ROLE_KASIR, 'andi@rumahgas.test');
        $this->makeUser(User::ROLE_KURIR, 'budi@rumahgas.test');

        $this->get(route('pengguna.index', ['role' => 'kurir']))
            ->assertOk()->assertSee('budi@rumahgas.test')->assertDontSee('andi@rumahgas.test');

        $this->get(route('pengguna.index', ['search' => 'andi']))
            ->assertOk()->assertSee('andi@rumahgas.test')->assertDontSee('budi@rumahgas.test');
    }

    public function test_self_registration_creates_kasir_not_pengelola(): void
    {
        $this->post(route('register.post'), [
            'name' => 'Orang Baru',
            'email' => 'orang@rumahgas.test',
            'password' => 'abc123',
            'password_confirmation' => 'abc123',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', ['email' => 'orang@rumahgas.test', 'role' => User::ROLE_KASIR]);
        $this->get(route('pengguna.index'))->assertForbidden();
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_PENGELOLA = 'pengelola';
    public const ROLE_KASIR = 'kasir';
    public const ROLE_KURIR = 'kurir';

    /** Pembeli tidak punya akun/login, jadi tidak ada di daftar role. */
    public const ROLES = [
        self::ROLE_PENGELOLA => 'Pengelola',
        self::ROLE_KASIR => 'Kasir',
        self::ROLE_KURIR => 'Kurir',
    ];

    /** Sama dengan default di database, supaya model baru langsung punya role. */
    protected $attributes = [
        'role' => self::ROLE_PENGELOLA,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isPengelola(): bool
    {
        return $this->hasRole(self::ROLE_PENGELOLA);
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? ucfirst((string) $this->role);
    }
}

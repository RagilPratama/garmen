<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'toko_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // Role constants
    const ROLE_SUPERADMIN = 'superadmin';
    const ROLE_ADMIN_GUDANG = 'admingarmen';
    const ROLE_ADMIN_KANTOR = 'adminkantor';
    const ROLE_ADMIN_JOMEI = 'adminjomei';
    const ROLE_ADMIN_KAMIKO = 'adminkamiko';

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

    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class);
    }

    // Role check methods
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    public function isAdminGudang(): bool
    {
        return $this->role === self::ROLE_ADMIN_GUDANG;
    }

    public function isAdminKantor(): bool
    {
        return $this->role === self::ROLE_ADMIN_KANTOR;
    }

    public function isAdminJomei(): bool
    {
        return $this->role === self::ROLE_ADMIN_JOMEI;
    }

    public function isAdminKamiko(): bool
    {
        return $this->role === self::ROLE_ADMIN_KAMIKO;
    }

    public function isAdminToko(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN_JOMEI, self::ROLE_ADMIN_KAMIKO]);
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN_GUDANG,
            self::ROLE_ADMIN_KANTOR
        ]);
    }

    public function isToko(): bool
    {
        return $this->isAdminToko();
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    // Get role display name
    public function getRoleNameAttribute(): string
    {
        return match($this->role) {
            self::ROLE_SUPERADMIN => 'Super Admin',
            self::ROLE_ADMIN_GUDANG => 'Admin Garmen',
            self::ROLE_ADMIN_KANTOR => 'Admin Kantor',
            self::ROLE_ADMIN_JOMEI => 'Admin Jomei',
            self::ROLE_ADMIN_KAMIKO => 'Admin Kamiko',
            default => 'Unknown',
        };
    }
}


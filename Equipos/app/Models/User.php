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

    public const ROLE_USUARIO = 'usuario';
    public const ROLE_ADMINISTRADOR = 'administrador';
    public const ROLE_SUPERADMINISTRADOR = 'superadministrador';

    public static function roles(): array
    {
        return [
            self::ROLE_USUARIO,
            self::ROLE_ADMINISTRADOR,
            self::ROLE_SUPERADMINISTRADOR,
        ];
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdministrador(): bool
    {
        return $this->hasRole(self::ROLE_ADMINISTRADOR, self::ROLE_SUPERADMINISTRADOR);
    }

    public function isSuperadministrador(): bool
    {
        return $this->hasRole(self::ROLE_SUPERADMINISTRADOR);
    }
}

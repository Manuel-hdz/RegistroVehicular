<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const MODULE_PERMISSION_LABELS = [
        'administracion' => 'Administracion',
        'mantenimiento' => 'Mantenimiento',
        'rrhh' => 'Recursos Humanos',
        'almacen' => 'Almacen',
        'compras' => 'Compras',
        'configuracion' => 'Configuracion',
    ];

    private const SECTION_DEFAULTS = [
        'administracion' => '__all__',
        'mantenimiento' => ['mantenimiento'],
        'rrhh' => ['rrhh', 'recursos humanos'],
        'almacen' => ['almacen'],
        'compras' => ['compras'],
        'refacciones' => ['almacen', 'mantenimiento'],
        'pendientes' => ['compras', 'almacen', 'mantenimiento'],
        'configuracion' => ['sistemas'],
    ];

    private const SECTION_PERMISSION_LINKS = [
        'administracion' => ['administracion'],
        'mantenimiento' => ['mantenimiento'],
        'rrhh' => ['rrhh'],
        'almacen' => ['almacen'],
        'compras' => ['compras'],
        'refacciones' => ['almacen', 'mantenimiento'],
        'pendientes' => ['compras', 'almacen', 'mantenimiento'],
        'configuracion' => ['configuracion'],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'role',
        'department',
        'module_permissions',
        'active',
        'current_session_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'active' => 'boolean',
            'module_permissions' => 'array',
        ];
    }

    public static function modulePermissionLabels(): array
    {
        return self::MODULE_PERMISSION_LABELS;
    }

    public function normalizedDepartment(): string
    {
        $value = Str::ascii(trim((string) $this->department));
        $value = mb_strtolower($value, 'UTF-8');

        return preg_replace('/\s+/', ' ', $value) ?? $value;
    }

    public function isSystemsUser(): bool
    {
        return $this->role === 'superadmin' || $this->normalizedDepartment() === 'sistemas';
    }

    public function belongsToDepartment(string ...$departments): bool
    {
        $department = $this->normalizedDepartment();

        foreach ($departments as $candidate) {
            $normalized = Str::ascii(trim($candidate));
            $normalized = mb_strtolower($normalized, 'UTF-8');
            $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;

            if ($department === $normalized) {
                return true;
            }
        }

        return false;
    }

    public function canManageOwnedDepartment(string ...$departments): bool
    {
        if ($this->isSystemsUser() || $this->belongsToDepartment(...$departments)) {
            return true;
        }

        foreach ($departments as $department) {
            if ($this->hasModulePermission($department)) {
                return true;
            }
        }

        return false;
    }

    public function grantedModules(): array
    {
        $allowed = array_keys(self::MODULE_PERMISSION_LABELS);
        $permissions = is_array($this->module_permissions) ? $this->module_permissions : [];

        return array_values(array_intersect(
            array_map(fn ($permission) => $this->normalizeValue((string) $permission), $permissions),
            $allowed
        ));
    }

    public function hasModulePermission(string $module): bool
    {
        return in_array($this->normalizeValue($module), $this->grantedModules(), true);
    }

    public function canAccessSection(string $section): bool
    {
        if ($this->active === false) {
            return false;
        }

        if ($this->role === 'user') {
            return false;
        }

        $normalizedSection = $this->normalizeValue($section);

        if ($this->isSystemsUser()) {
            return true;
        }

        if ($this->belongsToDepartment('gerencia') && $normalizedSection !== 'configuracion') {
            return true;
        }

        foreach (self::SECTION_PERMISSION_LINKS[$normalizedSection] ?? [] as $permission) {
            if ($this->hasModulePermission($permission)) {
                return true;
            }
        }

        $defaults = self::SECTION_DEFAULTS[$normalizedSection] ?? [];
        if ($defaults === '__all__') {
            return true;
        }

        return $this->belongsToDepartment(...$defaults);
    }

    public function canManageParts(): bool
    {
        return $this->canManageOwnedDepartment('almacen');
    }

    public function canManageRequisitionStatus(): bool
    {
        return $this->canManageOwnedDepartment('compras');
    }

    public function canManageRequisitionItems(): bool
    {
        return $this->canManageOwnedDepartment('almacen');
    }

    private function normalizeValue(string $value): string
    {
        $value = Str::ascii(trim($value));
        $value = mb_strtolower($value, 'UTF-8');

        return preg_replace('/\s+/', ' ', $value) ?? $value;
    }
}

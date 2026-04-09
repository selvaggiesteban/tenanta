<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'role',
        'contracted_hours',
        'billable_rate',
        'timezone',
        'avatar_url',
        'last_login_at',
        'accepted_privacy_at',
        'subscribed_to_newsletter',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Mapeo de Roles a Español Latinoamericano
     */
    const ROLES = [
        'super_admin' => 'Superadministrador',
        'admin'       => 'Administrador',
        'manager'     => 'Gerente',
        'member'      => 'Inquilino',
        'teacher'     => 'Profesor',
        'reseller'    => 'Distribuidor',
    ];

    public function isReseller(): bool
    {
        return $this->role === 'reseller';
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'contracted_hours' => 'decimal:2',
            'billable_rate' => 'decimal:2',
            'accepted_privacy_at' => 'datetime',
            'subscribed_to_newsletter' => 'boolean',
        ];
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'tenant_id' => $this->tenant_id,
            'role' => $this->role,
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'manager']);
    }

    public function isTeacher(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'teacher']);
    }

    public function isTenant(): bool
    {
        return $this->role === 'member';
    }

    public function getRoleNameAttribute(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    /**
     * Relación: Inquilinos gestionados por un Distribuidor.
     */
    public function managedTenants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Tenant::class, 'reseller_id');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)->withTimestamps();
    }
}

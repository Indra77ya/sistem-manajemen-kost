<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;

use App\Models\Scopes\HideDeveloperScope;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;

#[ScopedBy([HideDeveloperScope::class, BranchScope::class])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    const ROLE_DEVELOPER = 'developer';
    const ROLE_OWNER = 'owner';
    const ROLE_ADMIN = 'admin';
    const ROLE_TECHNICIAN = 'technician';
    const ROLE_TENANT = 'tenant';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true; // Simple for now, can be restricted later
    }

    public function isDeveloper(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isOwner(): bool
    {
        return $this->hasRole('owner');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin_cabang');
    }

    public function isTenant(): bool
    {
        return $this->hasRole('tenant');
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'estado',
        'password',
    ];

    public function voluntario()
    {
        return $this->hasOne(Voluntario::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)->with('permissions')->withTimestamps();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function hasRole(string $roleSlug): bool
    {
        return $this->roles->contains(fn (Role $role) => $role->clave === $roleSlug);
    }

    public function hasPermission(string $permissionSlug): bool
    {
        return $this->roles
            ->flatMap(fn (Role $role) => $role->permissions)
            ->contains(fn ($permission) => $permission->clave === $permissionSlug);
    }
}

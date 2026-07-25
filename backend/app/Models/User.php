<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'correo_notificaciones',
        'must_change_password',
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
            'must_change_password' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function getNameAttribute(): string
    {
        $voluntario = $this->relationLoaded('voluntario')
            ? $this->getRelation('voluntario')
            : $this->voluntario;

        if ($voluntario) {
            $fullName = trim(implode(' ', array_filter([
                $voluntario->nombres ?? null,
                $voluntario->apellidos ?? null,
            ])));

            if ($fullName !== '') {
                return $fullName;
            }
        }

        return $this->username;
    }

    public function getEmailForPasswordReset(): string
    {
        return $this->username;
    }

    public function getEmailAttribute(): ?string
    {
        if (! empty($this->attributes['correo_notificaciones'])) {
            return $this->attributes['correo_notificaciones'];
        }

        $voluntario = $this->relationLoaded('voluntario')
            ? $this->getRelation('voluntario')
            : $this->voluntario;

        return $voluntario?->correo_electronico;
    }

    public function getEstadoAttribute(): bool
    {
        return true;
    }

    public function hasRole(string $roleSlug): bool
    {
        return $this->roles->contains(fn (Role $role) => $role->clave === $roleSlug);
    }

}

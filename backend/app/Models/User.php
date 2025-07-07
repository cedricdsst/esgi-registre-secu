<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;

/**
 * @method bool hasRole(string|array $roles)
 * @method \Illuminate\Database\Eloquent\Collection getRoleNames()
 * @method \Illuminate\Database\Eloquent\Collection getAllPermissions()
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'nom',          // ← Nouveau
        'prenom',       // ← Nouveau
        'email',
        'password',
        'role',         // ← Nouveau
        'organisation', // ← Nouveau
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Isolation par organisation
    public function scopeFromSameOrganisation($query, $user = null)
    {
        $user = $user ?: Auth::user();
        return $query->where('organisation', $user->organisation);
    }

    // Nom complet pour l'affichage
    public function getFullNameAttribute()
    {
        return $this->prenom . ' ' . $this->nom;
    }
}
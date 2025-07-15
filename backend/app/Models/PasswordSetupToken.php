<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PasswordSetupToken extends Model
{
    protected $fillable = [
        'email',
        'token',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public $timestamps = false;

    public static function createToken($email)
    {
        $token = Str::random(64);
        
        // Supprimer les anciens tokens pour cet email
        static::where('email', $email)->delete();
        
        // Créer un nouveau token
        static::create([
            'email' => $email,
            'token' => $token,
            'created_at' => now(),
            'expires_at' => now()->addDays(7),
        ]);
        
        return $token;
    }

    public static function validateToken($email, $token)
    {
        return static::where('email', $email)
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->first();
    }

    public function isExpired()
    {
        return $this->expires_at < now();
    }
} 
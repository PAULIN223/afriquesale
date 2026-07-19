<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role',
        'verification_code', 'code_expires_at',
        'statut', 'phone', 'adresse', 'ville', 'pays',
        'email_verified_at',
    ];

    protected $hidden = ['password', 'remember_token', 'verification_code'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'code_expires_at'   => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isBlocked(): bool
    {
        return $this->statut === 'blocked';
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}

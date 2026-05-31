<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public $timestamps = false;

    protected $fillable = [
        'username',
        'email',
        'password',
        'createdAt',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'createdAt' => 'datetime',
        ];
    }

    public function clothingItems()
    {
        return $this->hasMany(ClothingItem::class, 'userId');
    }

    public function outfits()
    {
        return $this->hasMany(Outfit::class, 'userId');
    }

    public function tags()
    {
        return $this->hasMany(Tag::class, 'userId');
    }
}
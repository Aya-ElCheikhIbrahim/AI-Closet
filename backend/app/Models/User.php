<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
   protected $fillable = [
    'id',
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
}

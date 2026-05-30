<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'userId',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function clothingItems()
    {
        return $this->belongsToMany(ClothingItem::class, 'clothing_tags', 'tagId', 'clothingItemId');
    }
}
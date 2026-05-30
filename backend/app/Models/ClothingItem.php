<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClothingItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id',
        'userId',
        'name',
        'category',
        'subcategory',
        'color',
        'secondaryColor',
        'season',
        'occasion',
        'brand',
        'notes',
        'imageOriginal',
        'imageNoBg',
        'favorite',
        'wearCount',
        'lastWornAt',
        'createdAt',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function outfits()
    {
        return $this->belongsToMany(Outfit::class, 'outfit_items', 'clothingItemId', 'outfitId');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'clothing_tags', 'clothingItemId', 'tagId');
    }
}
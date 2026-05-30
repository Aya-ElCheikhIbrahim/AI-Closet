<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Outfit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id',
        'userId',
        'name',
        'style',
        'occasion',
        'season',
        'outfitHash',
        'aiGenerated',
        'favorite',
        'wearCount',
        'lastWornAt',
        'createdAt',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function items()
    {
        return $this->belongsToMany(ClothingItem::class, 'outfit_items', 'outfitId', 'clothingItemId');
    }

    public function aiGenerations()
    {
        return $this->hasMany(AiGeneration::class, 'outfitId');
    }
}
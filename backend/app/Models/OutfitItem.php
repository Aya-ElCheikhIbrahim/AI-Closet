<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutfitItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id',
        'outfitId',
        'clothingItemId',
        'position',
    ];

    public function outfit()
    {
        return $this->belongsTo(Outfit::class, 'outfitId');
    }

    public function clothingItem()
    {
        return $this->belongsTo(ClothingItem::class, 'clothingItemId');
    }
}
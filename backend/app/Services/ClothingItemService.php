<?php

namespace App\Services;

use App\Models\ClothingItem;

class ClothingItemService
{
    public static function getAllClothingItems($user_id, $id = null)
    {
        if (!$id) {
            return ClothingItem::where('userId', $user_id)->get();
        }

        return ClothingItem::where('userId', $user_id)
            ->where('id', $id)
            ->first();
    }
}
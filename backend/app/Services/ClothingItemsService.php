<?php

namespace App\Services;

use App\Models\ClothingItem;

class ClothingItemService
{
    static function getAllClothingItems($user_id, $id = null)
    {
        if (!$id) {
            return ClothingItem::where('user_id', $user_id)->get();
        }

        return ClothingItem::where('user_id', $user_id)->find($id);
    }
}
<?php

namespace App\Services;

use App\Models\Outfit;
use App\Models\OutfitItem;

class OutfitItemService
{
    public static function getAllOutfitItems($userId, $id = null)
    {
        $query = OutfitItem::whereHas('outfit', function ($q) use ($userId) {
            $q->where('userId', $userId);
        })->with(['outfit', 'clothingItem']);

        if (!$id) {
            return $query->get();
        }

        return $query->where('id', $id)->first();
    }

    public static function createOrUpdateOutfitItem($data, $outfitItem)
    {
        $outfitItem->outfitId = $data['outfitId'] ?? $outfitItem->outfitId;
        $outfitItem->clothingItemId = $data['clothingItemId'] ?? $outfitItem->clothingItemId;
        $outfitItem->position = $data['position'] ?? $outfitItem->position ?? 0;

        $outfitItem->save();

        return $outfitItem;
    }

    public static function deleteOutfitItem($userId, $id)
    {
        $outfitItem = self::getAllOutfitItems($userId, $id);

        if (!$outfitItem) {
            throw new \Exception("Outfit item not found.");
        }

        $outfitItem->delete();

        return true;
    }
}
<?php

namespace App\Services;

use App\Models\Outfit;

class OutfitService
{
    public static function getAllOutfits($userId, $id = null)
    {
        if (!$id) {
            return Outfit::where('userId', $userId)->get();
        }

        return Outfit::where('userId', $userId)
            ->where('id', $id)
            ->first();
    }

    public static function createOrUpdateOutfit($data, $outfit)
    {
        $outfit->userId = $data['userId'] ?? $outfit->userId;
        $outfit->name = $data['name'] ?? $outfit->name;
        $outfit->style = $data['style'] ?? $outfit->style;
        $outfit->occasion = $data['occasion'] ?? $outfit->occasion;
        $outfit->season = $data['season'] ?? $outfit->season;
        $outfit->outfitHash = $data['outfitHash'] ?? $outfit->outfitHash;
        $outfit->aiGenerated = $data['aiGenerated'] ?? $outfit->aiGenerated;
        $outfit->favorite = $data['favorite'] ?? $outfit->favorite;
        $outfit->wearCount = $data['wearCount'] ?? $outfit->wearCount;
        $outfit->lastWornAt = $data['lastWornAt'] ?? $outfit->lastWornAt;

        $outfit->save();

        return $outfit;
    }

    public static function deleteOutfit($id)
    {
        $outfit = Outfit::where('userId', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $outfit->delete();

        return true;
    }
}
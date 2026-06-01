<?php

namespace App\Services;

use App\Models\ClothingTag;

class ClothingTagService
{
    public static function getClothingTags($user_id, $id = null)
    {
        if (!$id) {
            return ClothingTag::where('clothingItemId', $id)->get();
        }

        return ClothingTag::where('clothingItemId', $id)
            ->first();
    }

    public static function createOrUpdateClothingTag($request, $id = null)
    {
        $tag = $id ? ClothingTag::where('id', $id)->firstOrFail() : new ClothingTag();

        $tag->clothingItemId = $request->clothingItemId ?? $tag->clothingItemId;
        $tag->tagId          = $request->tagId          ?? $tag->tagId;
        $tag->save();

        return $tag;
    }

    public static function deleteClothingTag($id)
    {
        $tag = ClothingTag::where('id', $id)->firstOrFail();
        $tag->delete();
    }
}
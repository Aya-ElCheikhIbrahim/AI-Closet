<?php

namespace App\Services;

use App\Models\Outfit;

class OutfitService
{
    static function getAllOutfits($user_id, $id = null)
    {
        if (!$id) {
            return Outfit::where('user_id', $user_id)->get();
        }

        return Outfit::where('user_id', $user_id)->find($id);
    }

    static function createOutfit($request)
    {
        $outfit = new Outfit();
        $outfit->user_id  = auth()->id();
        $outfit->name     = $request->name;
        $outfit->style    = $request->style;
        $outfit->occasion = $request->occasion;
        $outfit->season   = $request->season;
        $outfit->favorite = $request->favorite ?? false;
        $outfit->save();

        return $outfit;
    }

    static function updateOutfit($request, $id)
    {
        $outfit = Outfit::where('user_id', auth()->id())->findOrFail($id);
        $outfit->name     = $request->name     ?? $outfit->name;
        $outfit->style    = $request->style    ?? $outfit->style;
        $outfit->occasion = $request->occasion ?? $outfit->occasion;
        $outfit->season   = $request->season   ?? $outfit->season;
        $outfit->favorite = $request->favorite ?? $outfit->favorite;
        $outfit->save();

        return $outfit;
    }

    static function deleteOutfit($id)
    {
        $outfit = Outfit::where('user_id', auth()->id())->findOrFail($id);
        $outfit->delete();
    }
}
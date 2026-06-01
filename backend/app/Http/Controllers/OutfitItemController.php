<?php

namespace App\Http\Controllers;

use App\Models\OutfitItem;
use App\Services\OutfitItemService;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\Request;

class OutfitItemController extends Controller
{
    use ResponseTrait;

    public function getAllOutfitItems($id = null)
    {
        try {
            $items = OutfitItemService::getAllOutfitItems(auth()->id(), $id);

            return $this->responseJSON($items, "Outfit items fetched successfully.");
        } catch (Exception $e) {
            return $this->responseJSON(null, $e->getMessage(), 500);
        }
    }

    public function createOrUpdateOutfitItem(Request $request, $id = null)
    {
        try {
            $outfitItem = new OutfitItem;

            if ($id) {
                $outfitItem = OutfitItemService::getAllOutfitItems(auth()->id(), $id);

                if (!$outfitItem) {
                    return $this->responseJSON(null, "Outfit item not found.", 404);
                }
            }

            $outfitItem = OutfitItemService::createOrUpdateOutfitItem($request->all(), $outfitItem);

            return $this->responseJSON($outfitItem, "Outfit item saved successfully.");
        } catch (Exception $e) {
            return $this->responseJSON(null, $e->getMessage(), 500);
        }
    }

    public function deleteOutfitItem($id)
    {
        try {
            OutfitItemService::deleteOutfitItem(auth()->id(), $id);

            return $this->responseJSON(null, "Outfit item deleted successfully.");
        } catch (Exception $e) {
            return $this->responseJSON(null, $e->getMessage(), 500);
        }
    }
}
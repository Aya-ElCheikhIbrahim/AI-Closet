<?php

namespace App\Http\Controllers;

use App\Models\ClothingItem;
use App\Services\ClothingItemService;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\Request;

class ClothingItemController extends Controller
{
    use ResponseTrait;

    public function getAllClothingItems($id = null)
    {
        try {
            $user_id = auth()->id();

            $items = ClothingItemService::getAllClothingItems($user_id, $id);

            return $this->responseJSON($items, "Clothing items fetched successfully.");
        } catch (Exception $e) {
            return $this->responseJSON(null, $e->getMessage(), 500);
        }
    }

    public function createOrUpdateClothingItem(Request $request, $id = null)
    {
        try {
            $item = new ClothingItem;

            if ($id) {
                $item = ClothingItemService::getAllClothingItems(auth()->id(), $id);

                if (!$item) {
                    return $this->responseJSON(null, "Clothing item not found.", 404);
                }
            }

            $data = $request->all();

            if (!$id) {
                $data['userId'] = auth()->id();
            }

            $item = ClothingItemService::createOrUpdateClothingItem($data, $item);

            if ($item) {
                return $this->responseJSON($item, "Clothing item saved successfully.");
            }

            return $this->responseJSON(null, "Failed to save clothing item.", 400);
        } catch (Exception $e) {
            return $this->responseJSON(null, $e->getMessage(), 500);
        }
    }

    public function deleteClothingItem($id)
{
    try {
        ClothingItemService::deleteClothingItem($id);

        return $this->responseJSON(null, "Clothing item deleted successfully.");
    } catch (Exception $e) {
        return $this->responseJSON(null, $e->getMessage(), 500);
    }
}
}
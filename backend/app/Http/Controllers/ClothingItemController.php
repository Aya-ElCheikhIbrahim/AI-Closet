<?php

namespace App\Http\Controllers;

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
}
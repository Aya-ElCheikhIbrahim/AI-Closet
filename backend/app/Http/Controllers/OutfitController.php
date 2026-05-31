<?php

namespace App\Http\Controllers;

use App\Services\OutfitService;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\Request;

class OutfitController extends Controller
{
    use ResponseTrait;

    public function getAllOutfits($id = null)
    {
        try {
            $user_id = auth()->id();
            $outfits = OutfitService::getAllOutfits($user_id, $id);

            return $this->responseJSON($outfits, "Outfits fetched successfully.");
        } catch (Exception $e) {
            return $this->responseJSON(null, $e->getMessage(), 500);
        }
    }

    public function createOutfit(Request $request)
    {
        try {
            $outfit = OutfitService::createOutfit($request);

            return $this->responseJSON($outfit, "Outfit created successfully.");
        } catch (Exception $e) {
            return $this->responseJSON(null, $e->getMessage(), 500);
        }
    }

    public function updateOutfit(Request $request, $id)
    {
        try {
            $outfit = OutfitService::updateOutfit($request, $id);

            return $this->responseJSON($outfit, "Outfit updated successfully.");
        } catch (Exception $e) {
            return $this->responseJSON(null, $e->getMessage(), 500);
        }
    }

    public function deleteOutfit($id)
    {
        try {
            OutfitService::deleteOutfit($id);

            return $this->responseJSON(null, "Outfit deleted successfully.");
        } catch (Exception $e) {
            return $this->responseJSON(null, $e->getMessage(), 500);
        }
    }
}
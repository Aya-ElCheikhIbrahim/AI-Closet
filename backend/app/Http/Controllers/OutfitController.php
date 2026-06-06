<?php

namespace App\Http\Controllers;

use App\Models\Outfit;
use App\Services\OutfitService;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\Request;
use App\Services\OutfitGenerationService;

class OutfitController extends Controller
{
    use ResponseTrait;

    public function getAllOutfits($id = null)
    {
        try {
            $userId = auth()->id();

            $outfits = OutfitService::getAllOutfits($userId, $id);

            return $this->responseJSON($outfits, "Outfits fetched successfully.");
        } catch (Exception $e) {
            return $this->responseJSON(null, $e->getMessage(), 500);
        }
    }

    public function createOrUpdateOutfit(Request $request, $id = null)
    {
        try {
            $outfit = new Outfit;

            if ($id) {
                $outfit = OutfitService::getAllOutfits(auth()->id(), $id);

                if (!$outfit) {
                    return $this->responseJSON(null, "Outfit not found.", 404);
                }
            }

            $data = $request->all();

            if (!$id) {
                $data['userId'] = auth()->id();
            }

            $outfit = OutfitService::createOrUpdateOutfit($data, $outfit);

            if ($outfit) {
                return $this->responseJSON($outfit, "Outfit saved successfully.");
            }

            return $this->responseJSON(null, "Failed to save outfit.", 400);
        } catch (Exception $e) {
            return $this->responseJSON(null, "Server error while saving outfit.", 500);
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
        public function generateOutfits(Request $request)
{
    try {
        $outfits = OutfitGenerationService::generateOutfits(
            auth()->id(),
            [
                'season' => $request->season,
                'occasion' => $request->occasion,
                'limit' => $request->limit ?? 10,
            ]
        );

        return $this->responseJSON(
            $outfits,
            "Outfits generated successfully."
        );
    } catch (Exception $e) {
        return $this->responseJSON(
            null,
            $e->getMessage(),
            500
        );
    }
}
}
<?php

namespace App\Http\Controllers;

use App\Services\ClothingTagService;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\Request;

class ClothingTagController extends Controller
{
    use ResponseTrait;

    public function getClothingTags($id = null)
    {
        try {
            $user_id = auth()->id();
            $tags = ClothingTagService::getClothingTags($user_id, $id);

            return $this->responseJSON($tags, "Clothing tags fetched successfully.");
        } catch (Exception $e) {
            return $this->responseJSON(null, $e->getMessage(), 500);
        }
    }

    public function createOrUpdateClothingTag(Request $request, $id = null)
    {
        try {
            $tag = ClothingTagService::createOrUpdateClothingTag($request, $id);

            return $this->responseJSON($tag, $id ? "Clothing tag updated successfully." : "Clothing tag created successfully.");
        } catch (Exception $e) {
            return $this->responseJSON(null, $e->getMessage(), 500);
        }
    }

    public function deleteClothingTag($id)
    {
        try {
            ClothingTagService::deleteClothingTag($id);

            return $this->responseJSON(null, "Clothing tag deleted successfully.");
        } catch (Exception $e) {
            return $this->responseJSON(null, $e->getMessage(), 500);
        }
    }
}
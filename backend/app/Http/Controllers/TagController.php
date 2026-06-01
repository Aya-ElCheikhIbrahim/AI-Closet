<?php

namespace App\Http\Controllers;

use App\Services\TagService;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\Request;

class TagController extends Controller
{
    use ResponseTrait;

    public function getTags($id = null)
    {
        try {
            $user_id = auth()->id();
            $tags = TagService::getTags($user_id, $id);

            return $this->responseJSON($tags, "Tags fetched successfully.");
        } catch (Exception $e) {
            return $this->responseJSON(null, $e->getMessage(), 500);
        }
    }

    public function createOrUpdateTag(Request $request, $id = null)
    {
        try {
            $tag = TagService::createOrUpdateTag($request, $id);

            return $this->responseJSON($tag, $id ? "Tag updated successfully." : "Tag created successfully.");
        } catch (Exception $e) {
            return $this->responseJSON(null, $e->getMessage(), 500);
        }
    }

    public function deleteTag($id)
    {
        try {
            TagService::deleteTag($id);

            return $this->responseJSON(null, "Tag deleted successfully.");
        } catch (Exception $e) {
            return $this->responseJSON(null, $e->getMessage(), 500);
        }
    }
}
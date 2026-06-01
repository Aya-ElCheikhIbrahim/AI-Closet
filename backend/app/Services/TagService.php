<?php

namespace App\Services;

use App\Models\Tag;

class TagService
{
    public static function getTags($user_id, $id = null)
    {
        if (!$id) {
            return Tag::where('userId', $user_id)->get();
        }

        return Tag::where('userId', $user_id)
            ->where('id', $id)
            ->first();
    }

    public static function createOrUpdateTag($request, $id = null)
    {
        $tag = $id ? Tag::where('userId', auth()->id())->where('id', $id)->firstOrFail() : new Tag();

        $tag->userId = auth()->id();
        $tag->name   = $request->name ?? $tag->name;
        $tag->save();

        return $tag;
    }

    public static function deleteTag($id)
    {
        $tag = Tag::where('userId', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $tag->delete();
    }
}
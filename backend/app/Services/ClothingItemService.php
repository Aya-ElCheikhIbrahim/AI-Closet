<?php

namespace App\Services;

use App\Models\ClothingItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClothingItemService
{
    public static function getAllClothingItems($user_id, $id = null)
    {
        if (!$id) {
            return ClothingItem::where('userId', $user_id)->get();
        }

        return ClothingItem::where('userId', $user_id)
            ->where('id', $id)
            ->first();
    }

    public static function createOrUpdateClothingItem($data, $item)
{
    set_time_limit(300);

    $item->userId = $data['userId'] ?? $item->userId;
    $item->name = $data['name'] ?? $item->name;
    $item->category = $data['category'] ?? $item->category;
    $item->subcategory = $data['subcategory'] ?? $item->subcategory;
    $item->color = $data['color'] ?? $item->color;
    $item->secondaryColor = $data['secondaryColor'] ?? $item->secondaryColor;
    $item->season = $data['season'] ?? $item->season;
    $item->occasion = $data['occasion'] ?? $item->occasion;
    $item->brand = $data['brand'] ?? $item->brand;
    $item->notes = $data['notes'] ?? $item->notes;
    $item->favorite = $data['favorite'] ?? $item->favorite ?? false;
    $item->wearCount = $data['wearCount'] ?? $item->wearCount ?? 0;
    $item->lastWornAt = $data['lastWornAt'] ?? $item->lastWornAt;

    if (isset($data['base64']) && isset($data['file_name'])) {
        $base64String = $data['base64'];

        if (Str::contains($base64String, ';base64,')) {
            [$meta, $base64String] = explode(';base64,', $base64String);
        }

        $decoded = base64_decode($base64String);

        $filename = uniqid() . '_' . preg_replace('/\s+/', '_', $data['file_name']);
        $folder = 'clothing_items/originals';
        $fullPath = $folder . '/' . $filename;

        Storage::disk('public')->put($fullPath, $decoded);

        $item->imageOriginal = $fullPath;
    }

    $item->save();

    if (isset($fullPath)) {
        $inputPath = storage_path('app/public/' . $fullPath);
        $outputPath = storage_path('app/public/clothing_items/processed');

        $python = base_path('venv/bin/python');
        $script = base_path('python_scripts/bg_remover.py');

        $command = escapeshellcmd($python) . ' ' .
            escapeshellarg($script) . ' --file ' .
            escapeshellarg($inputPath) . ' --output ' .
            escapeshellarg($outputPath);

        $output = shell_exec($command);

        $result = json_decode($output, true);

        if ($result && isset($result['success']) && $result['success']) {
            $item->imageNoBg = str_replace(
                storage_path('app/public/') . '/',
                '',
                $result['transparent_path']
            );

            $item->save();
        }
    }

    return $item;
}

public static function deleteClothingItem($id)
{
    $item = ClothingItem::where('userId', auth()->id())
        ->where('id', $id)
        ->firstOrFail();

    $item->delete();

    return true;
}
}
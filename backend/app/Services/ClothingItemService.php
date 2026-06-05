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

    private static function parsePythonJson($output)
    {
        $lines = explode("\n", trim($output));

        foreach (array_reverse($lines) as $line) {
            $line = trim($line);

            if (str_starts_with($line, '{')) {
                return json_decode($line, true);
            }
        }

        return null;
    }

    private static function getPythonPath()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? base_path('ai/venv/Scripts/python.exe')
            : base_path('ai/venv/bin/python');
    }

    public static function createOrUpdateClothingItem($data, $item)
    {
        set_time_limit(300);

        $item->userId = $data['userId'] ?? $item->userId;
        $item->name = $data['name'] ?? $item->name ?? 'Untitled Item';
        $item->category = $data['category'] ?? $item->category ?? 'uncategorized';
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

            $python = self::getPythonPath();

            $bgScript = base_path('ai/bg_remover.py');

            $bgCommand = escapeshellarg($python) . ' ' .
                escapeshellarg($bgScript) . ' --file ' .
                escapeshellarg($inputPath) . ' --output ' .
                escapeshellarg($outputPath);

            $bgOutput = shell_exec($bgCommand . ' 2>&1');

            \Log::info('BG OUTPUT: ' . $bgOutput);

            $bgResult = self::parsePythonJson($bgOutput);

            if (!$bgResult) {
                throw new \Exception('Background remover did not return valid JSON: ' . $bgOutput);
            }

            if (!isset($bgResult['success']) || !$bgResult['success']) {
                throw new \Exception('Background remover failed: ' . ($bgResult['error'] ?? 'Unknown error'));
            }

            $item->imageNoBg = str_replace(
                storage_path('app/public/') . '/',
                '',
                $bgResult['transparent_path']
            );

            $item->save();

            $classifierScript = base_path('ai/fashion_classifier.py');

            $classifyCommand = escapeshellarg($python) . ' ' .
                escapeshellarg($classifierScript) . ' --file ' .
                escapeshellarg($bgResult['transparent_path']);

            $classifyOutput = shell_exec($classifyCommand . ' 2>&1');

            \Log::info('CLASSIFY OUTPUT: ' . $classifyOutput);

            $classifyResult = self::parsePythonJson($classifyOutput);

            if (
                $classifyResult &&
                isset($classifyResult['success']) &&
                $classifyResult['success']
            ) {
                $item->category = $classifyResult['category'];
                $item->save();
            }

            $colorScript = base_path('ai/color_extractor.py');

            $colorCommand = escapeshellarg($python) . ' ' .
                escapeshellarg($colorScript) . ' --file ' .
                escapeshellarg($bgResult['transparent_path']);

            $colorOutput = shell_exec($colorCommand . ' 2>&1');

            \Log::info('COLOR OUTPUT: ' . $colorOutput);

            $colorResult = self::parsePythonJson($colorOutput);

            if (
                $colorResult &&
                isset($colorResult['success']) &&
                $colorResult['success']
            ) {
                $item->color = $colorResult['primaryColor'];
                $item->secondaryColor = $colorResult['secondaryColor'];
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
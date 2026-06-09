<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Exception;

class AiOutfitImageService
{
    public static function generateOutfitImage($items)
    {
        $apiKey = env('OPENAI_API_KEY');
        $model = env('OPENAI_IMAGE_MODEL', 'gpt-image-1');

        if (!$apiKey) {
            throw new Exception('OpenAI API key is missing.');
        }

        $prompt = self::buildPrompt($items);

        $multipart = [
            [
                'name' => 'model',
                'contents' => $model,
            ],
            [
                'name' => 'prompt',
                'contents' => $prompt,
            ],
            [
                'name' => 'size',
                'contents' => '1024x1024',
            ],
            [
                'name' => 'n',
                'contents' => '1',
            ],
        ];

        foreach ($items as $index => $item) {
            $imagePath = $item->imageNoBg ?: $item->imageOriginal;

            if (!$imagePath) {
                continue;
            }

            $absolutePath = str_starts_with($imagePath, '/')
                ? $imagePath
                : storage_path('app/public/' . $imagePath);

            if (!file_exists($absolutePath)) {
                continue;
            }

            $multipart[] = [
                'name' => 'image[]',
                'contents' => fopen($absolutePath, 'r'),
                'filename' => 'item_' . $item->id . '.png',
            ];
        }

        $response = Http::withToken($apiKey)
            ->asMultipart()
            ->post('https://api.openai.com/v1/images/edits', $multipart);

        if (!$response->successful()) {
            throw new Exception('OpenAI image generation failed: ' . $response->body());
        }

        $data = $response->json();

        $base64Image = $data['data'][0]['b64_json'] ?? null;

        if (!$base64Image) {
            throw new Exception('OpenAI did not return an image.');
        }

        $imageBinary = base64_decode($base64Image);

        $filename = 'outfits/generated/outfit_' . uniqid() . '.png';

        Storage::disk('public')->put($filename, $imageBinary);

        return [
            'imagePath' => $filename,
            'usedItemIds' => collect($items)->pluck('id')->toArray(),
            'prompt' => $prompt,
        ];
    }

    private static function buildPrompt($items)
    {
        $description = collect($items)->map(function ($item) {
            return "- Item ID {$item->id}: {$item->category}, primary color {$item->color}, secondary color {$item->secondaryColor}";
        })->implode("\n");

        return <<<PROMPT
Create one clean fashion flat-lay outfit image using only the provided clothing item images.

Arrange the items together into one complete outfit on a clean neutral studio background.

Rules:
- Use only the provided clothing items.
- Do not invent extra clothes.
- Do not add a human model.
- Do not add accessories unless they are provided.
- Preserve the original clothing colors and style as much as possible.
- The final image should look like a polished digital wardrobe outfit preview.
- Output one square image.

Provided items:
{$description}
PROMPT;
    }
}
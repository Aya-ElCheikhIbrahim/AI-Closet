<?php

namespace App\Services;

use App\Models\ClothingItem;

class OutfitGenerationService
{
    private static array $tops = [
        't-shirt', 'shirt', 'blouse', 'hoodie', 'sweater'
    ];

    private static array $bottoms = [
        'jeans', 'pants', 'skirt', 'shorts'
    ];

    private static array $dresses = [
        'dress'
    ];

    private static array $outerwear = [
        'jacket', 'coat'
    ];

    private static array $neutralColors = [
        'black', 'white', 'cream', 'beige', 'gray', 'camel'
    ];

    private static array $boldColors = [
        'red', 'burgundy', 'pink', 'orange', 'yellow', 'green', 'blue', 'purple'
    ];

    public static function generateOutfits($userId, $filters = [])
    {
        $items = ClothingItem::where('userId', $userId)->get();

        $groups = self::groupItemsByCategory($items);

        $outfits = [];

        foreach ($groups['tops'] as $top) {
            foreach ($groups['bottoms'] as $bottom) {
                $outfitItems = [$top, $bottom];

                $outfits[] = self::buildOutfit($outfitItems, $filters);

                foreach ($groups['outerwear'] as $outerwear) {
                    $outfits[] = self::buildOutfit([$top, $bottom, $outerwear], $filters);
                }
            }
        }

        foreach ($groups['dresses'] as $dress) {
            $outfits[] = self::buildOutfit([$dress], $filters);

            foreach ($groups['outerwear'] as $outerwear) {
                $outfits[] = self::buildOutfit([$dress, $outerwear], $filters);
            }
        }

        usort($outfits, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($outfits, 0, $filters['limit'] ?? 10);
    }

    private static function groupItemsByCategory($items)
    {
        return [
            'tops' => $items->filter(fn ($item) => in_array($item->category, self::$tops))->values(),
            'bottoms' => $items->filter(fn ($item) => in_array($item->category, self::$bottoms))->values(),
            'dresses' => $items->filter(fn ($item) => in_array($item->category, self::$dresses))->values(),
            'outerwear' => $items->filter(fn ($item) => in_array($item->category, self::$outerwear))->values(),
        ];
    }

    private static function buildOutfit($items, $filters)
    {
        $score = self::scoreOutfit($items, $filters);

        return [
            'score' => $score,
            'items' => collect($items)->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category,
                    'color' => $item->color,
                    'secondaryColor' => $item->secondaryColor,
                    'season' => $item->season,
                    'occasion' => $item->occasion,
                    'imageNoBg' => $item->imageNoBg,
                    'favorite' => $item->favorite,
                    'wearCount' => $item->wearCount,
                    'lastWornAt' => $item->lastWornAt,
                ];
            })->toArray(),
            'reason' => self::generateReason($items),
        ];
    }

    private static function scoreOutfit($items, $filters)
    {
        $score = 40;

        if (self::hasGoodColorHarmony($items)) {
            $score += 25;
        } else {
            $score -= 20;
        }

        if (self::matchesSeason($items, $filters['season'] ?? null)) {
            $score += 15;
        }

        if (self::matchesOccasion($items, $filters['occasion'] ?? null)) {
            $score += 15;
        }

        foreach ($items as $item) {
            if ($item->favorite) {
                $score += 5;
            }

            if (($item->wearCount ?? 0) === 0) {
                $score += 3;
            }
        }

        if (self::hasTooManyBoldColors($items)) {
            $score -= 10;
        }

        return max(0, min(100, $score));
    }

    private static function hasGoodColorHarmony($items)
    {
        $colors = self::extractColors($items);

        if (count($colors) <= 1) {
            return true;
        }

        $neutralCount = count(array_intersect($colors, self::$neutralColors));

        if ($neutralCount >= 1) {
            return true;
        }

        foreach ($colors as $colorA) {
            foreach ($colors as $colorB) {
                if ($colorA === $colorB) {
                    continue;
                }

                if (self::colorsMatch($colorA, $colorB)) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function colorsMatch($colorA, $colorB)
    {
        $matches = [
            'brown' => ['cream', 'beige', 'white', 'black', 'camel', 'navy'],
            'camel' => ['cream', 'beige', 'white', 'black', 'brown'],
            'burgundy' => ['cream', 'beige', 'black', 'gray', 'navy'],
            'red' => ['white', 'black', 'cream', 'gray'],
            'blue' => ['white', 'cream', 'beige', 'gray', 'brown'],
            'navy' => ['white', 'cream', 'beige', 'gray', 'brown', 'burgundy'],
            'green' => ['white', 'cream', 'beige', 'brown', 'black'],
            'olive' => ['cream', 'beige', 'brown', 'black', 'white'],
            'pink' => ['white', 'cream', 'gray', 'beige', 'black'],
            'purple' => ['white', 'cream', 'gray', 'black'],
            'yellow' => ['white', 'cream', 'gray', 'blue', 'black'],
            'orange' => ['white', 'cream', 'beige', 'brown'],
        ];

        return in_array($colorB, $matches[$colorA] ?? [])
            || in_array($colorA, $matches[$colorB] ?? []);
    }

    private static function hasTooManyBoldColors($items)
    {
        $colors = self::extractColors($items);

        $boldCount = count(array_intersect($colors, self::$boldColors));

        return $boldCount >= 3;
    }

    private static function extractColors($items)
    {
        $colors = [];

        foreach ($items as $item) {
            if ($item->color) {
                $colors[] = $item->color;
            }

            if ($item->secondaryColor) {
                $colors[] = $item->secondaryColor;
            }
        }

        return array_values(array_unique($colors));
    }

    private static function matchesSeason($items, $season)
    {
        if (!$season) {
            return true;
        }

        foreach ($items as $item) {
            if ($item->season && $item->season !== $season && $item->season !== 'all') {
                return false;
            }
        }

        return true;
    }

    private static function matchesOccasion($items, $occasion)
    {
        if (!$occasion) {
            return true;
        }

        foreach ($items as $item) {
            if ($item->occasion && $item->occasion !== $occasion && $item->occasion !== 'all') {
                return false;
            }
        }

        return true;
    }

    private static function generateReason($items)
    {
        $colors = self::extractColors($items);

        if (count($colors) >= 2) {
            return ucfirst($colors[0]) . ' and ' . $colors[1] . ' create a balanced outfit.';
        }

        if (count($colors) === 1) {
            return ucfirst($colors[0]) . ' creates a clean, cohesive look.';
        }

        return 'This outfit has a balanced structure and works well together.';
    }
}
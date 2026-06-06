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
                $outfits[] = self::buildOutfit([$top, $bottom], $filters);

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

        usort($outfits, fn ($a, $b) => $b['score'] <=> $a['score']);

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
        return [
            'score' => self::scoreOutfit($items, $filters),
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

        $colorScore = self::colorHarmonyScore($items);
        $score += $colorScore;

        if ($colorScore < 0) {
            $score -= 10;
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

        if (self::hasTooManyBoldPrimaryColors($items)) {
            $score -= 15;
        }

        return max(0, min(100, $score));
    }

    private static function colorHarmonyScore($items)
    {
        $score = 0;
        $items = collect($items)->values();

        for ($i = 0; $i < count($items); $i++) {
            for ($j = $i + 1; $j < count($items); $j++) {
                $score += self::pairColorScore($items[$i], $items[$j]);
            }
        }

        return $score;
    }

    private static function pairColorScore($itemA, $itemB)
    {
        $score = 0;

        $primaryA = $itemA->color;
        $primaryB = $itemB->color;

        $secondaryA = $itemA->secondaryColor;
        $secondaryB = $itemB->secondaryColor;

        if (!$primaryA || !$primaryB) {
            return 0;
        }

        if ($primaryA === $primaryB) {
            $score += 20;
        } elseif (self::colorsMatch($primaryA, $primaryB)) {
            $score += 18;
        } elseif (self::isNeutral($primaryA) || self::isNeutral($primaryB)) {
            $score += 12;
        } else {
            $score -= 15;
        }

        if ($secondaryA) {
            if ($secondaryA === $primaryB || self::colorsMatch($secondaryA, $primaryB)) {
                $score += 8;
            }
        }

        if ($secondaryB) {
            if ($secondaryB === $primaryA || self::colorsMatch($secondaryB, $primaryA)) {
                $score += 8;
            }
        }

        if ($secondaryA && $secondaryB) {
            if ($secondaryA === $secondaryB || self::colorsMatch($secondaryA, $secondaryB)) {
                $score += 4;
            }
        }

        return $score;
    }

    private static function isNeutral($color)
    {
        return in_array($color, self::$neutralColors);
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
            'mustard' => ['cream', 'white', 'brown', 'beige', 'navy'],
            'orange' => ['white', 'cream', 'beige', 'brown'],
            'gray' => ['black', 'white', 'cream', 'pink', 'burgundy', 'blue'],
            'cream' => ['black', 'brown', 'camel', 'burgundy', 'red', 'blue', 'navy', 'green', 'olive'],
            'beige' => ['black', 'brown', 'camel', 'burgundy', 'blue', 'navy', 'green', 'olive'],
            'white' => ['black', 'brown', 'camel', 'burgundy', 'red', 'blue', 'navy', 'green', 'pink', 'purple'],
            'black' => ['white', 'cream', 'beige', 'gray', 'brown', 'camel', 'burgundy', 'red', 'pink', 'blue', 'green', 'purple'],
        ];

        return in_array($colorB, $matches[$colorA] ?? [])
            || in_array($colorA, $matches[$colorB] ?? []);
    }

    private static function hasTooManyBoldPrimaryColors($items)
    {
        $primaryColors = [];

        foreach ($items as $item) {
            if ($item->color) {
                $primaryColors[] = $item->color;
            }
        }

        $boldCount = count(array_intersect($primaryColors, self::$boldColors));

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
        $items = collect($items)->values();

        if (count($items) >= 2) {
            $first = $items[0];
            $second = $items[1];

            return ucfirst($first->color) . ' ' . $first->category .
                ' pairs well with ' . $second->color . ' ' . $second->category .
                ' because the primary colors work together, while the secondary colors add balance.';
        }

        if (count($items) === 1) {
            return ucfirst($items[0]->color) . ' creates a clean, cohesive look.';
        }

        return 'This outfit has a balanced structure and works well together.';
    }
}
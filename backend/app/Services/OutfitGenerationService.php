<?php

namespace App\Services;

use App\Models\ClothingItem;
use Carbon\Carbon;

class OutfitGenerationService
{
    private static array $tops = ['t-shirt', 'shirt', 'blouse', 'hoodie', 'sweater'];
    private static array $bottoms = ['jeans', 'pants', 'skirt', 'shorts'];
    private static array $dresses = ['dress'];
    private static array $outerwear = ['jacket', 'coat'];

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

        $outfits = array_filter($outfits, fn ($outfit) => $outfit['score'] >= 45);

        usort($outfits, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice(array_values($outfits), 0, $filters['limit'] ?? 10);
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
        $score = 50;

        $score += self::colorHarmonyScore($items);
        $score += self::structureScore($items);
        $score += self::seasonScore($items, $filters['season'] ?? null);
        $score += self::occasionScore($items, $filters['occasion'] ?? null);
        $score += self::usageScore($items);

        if (self::hasTooManyBoldPrimaryColors($items)) {
            $score -= 18;
        }

        return max(0, min(100, round($score)));
    }

    private static function structureScore($items)
    {
        $categories = collect($items)->pluck('category')->toArray();

        $hasTop = count(array_intersect($categories, self::$tops)) > 0;
        $hasBottom = count(array_intersect($categories, self::$bottoms)) > 0;
        $hasDress = count(array_intersect($categories, self::$dresses)) > 0;
        $hasOuterwear = count(array_intersect($categories, self::$outerwear)) > 0;

        if ($hasTop && $hasBottom && $hasOuterwear) {
            return 8;
        }

        if ($hasTop && $hasBottom) {
            return 5;
        }

        if ($hasDress && $hasOuterwear) {
            return 6;
        }

        if ($hasDress) {
            return 3;
        }

        return -10;
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

        return min(25, max(-25, $score));
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
            $score += 10;
        } elseif (self::colorsMatch($primaryA, $primaryB)) {
            $score += 9;
        } elseif (self::isNeutral($primaryA) || self::isNeutral($primaryB)) {
            $score += 6;
        } else {
            $score -= 10;
        }

        if ($secondaryA && ($secondaryA === $primaryB || self::colorsMatch($secondaryA, $primaryB))) {
            $score += 3;
        }

        if ($secondaryB && ($secondaryB === $primaryA || self::colorsMatch($secondaryB, $primaryA))) {
            $score += 3;
        }

        if ($secondaryA && $secondaryB && ($secondaryA === $secondaryB || self::colorsMatch($secondaryA, $secondaryB))) {
            $score += 2;
        }

        return $score;
    }

    private static function seasonScore($items, $requestedSeason)
    {
        $score = 0;

        foreach ($items as $item) {
            if (!$item->season || $item->season === 'all') {
                continue;
            }

            if ($requestedSeason) {
                $score += $item->season === $requestedSeason ? 4 : -8;
            }
        }

        if (!$requestedSeason) {
            $seasons = collect($items)
                ->pluck('season')
                ->filter(fn ($season) => $season && $season !== 'all')
                ->unique()
                ->values();

            if ($seasons->count() > 1) {
                $score -= 6;
            }
        }

        return $score;
    }

    private static function occasionScore($items, $requestedOccasion)
    {
        $score = 0;

        foreach ($items as $item) {
            if (!$item->occasion || $item->occasion === 'all') {
                continue;
            }

            if ($requestedOccasion) {
                $score += $item->occasion === $requestedOccasion ? 4 : -10;
            }
        }

        if (!$requestedOccasion) {
            $occasions = collect($items)
                ->pluck('occasion')
                ->filter(fn ($occasion) => $occasion && $occasion !== 'all')
                ->unique()
                ->values();

            if ($occasions->count() > 1) {
                $score -= 8;
            }
        }

        return $score;
    }

    private static function usageScore($items)
    {
        $score = 0;

        foreach ($items as $item) {
            $wearCount = $item->wearCount ?? 0;

            if ($item->favorite) {
                $score += 3;
            }

            if ($wearCount === 0) {
                $score += 5;
            } elseif ($wearCount <= 3) {
                $score += 2;
            } elseif ($wearCount > 20) {
                $score -= 12;
            } elseif ($wearCount > 10) {
                $score -= 7;
            } elseif ($wearCount > 5) {
                $score -= 3;
            }

            if ($item->lastWornAt) {
                $lastWorn = Carbon::parse($item->lastWornAt);

                if ($lastWorn->greaterThanOrEqualTo(now()->subDays(3))) {
                    $score -= 8;
                } elseif ($lastWorn->greaterThanOrEqualTo(now()->subDays(7))) {
                    $score -= 4;
                }
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

        return count(array_intersect($primaryColors, self::$boldColors)) >= 3;
    }

    private static function generateReason($items)
    {
        $items = collect($items)->values();

        if ($items->count() >= 2) {
            $first = $items[0];
            $second = $items[1];

            return ucfirst($first->color) . ' ' . $first->category .
                ' pairs well with ' . $second->color . ' ' . $second->category .
                ' because the primary colors are compatible and the secondary tones add balance.';
        }

        if ($items->count() === 1) {
            return ucfirst($items[0]->color) . ' creates a clean, cohesive look.';
        }

        return 'This outfit has a balanced structure and works well together.';
    }
}
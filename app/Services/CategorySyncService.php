<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CategorySyncService
{
    private const CACHE_KEY = 'olx_categories';
    private const CACHE_TTL = 1440;

    public function fetchCategories(bool $forceRefresh = false): array
    {
        if (!$forceRefresh) {
            $cached = Cache::get(self::CACHE_KEY);
            if ($cached !== null) {
                return $cached;
            }
        }

        $response = Http::timeout(30)
            ->get('https://www.olx.com.lb/api/categories');

        if ($response->failed()) {
            throw new \Exception("Failed to fetch categories: HTTP {$response->status()}");
        }

        $categories = $response->json();
        Cache::put(self::CACHE_KEY, $categories, now()->addMinutes(self::CACHE_TTL));

        return $categories;
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function syncCategories(array $categories, ?int $parentId = null): int
    {
        $syncedCount = 0;

        foreach ($categories as $categoryData) {
            if (empty($categoryData['externalID'])) {
                continue;
            }

            $category = Category::updateOrCreate(
                ['external_id' => $categoryData['externalID']],
                [
                    'olx_id' => $categoryData['id'],
                    'name' => $categoryData['name'] ?? 'Unnamed',
                    'name_ar' => $categoryData['name_l1'] ?? null,
                    'slug' => $categoryData['slug'] ?? $this->generateSlug($categoryData['name'] ?? 'unnamed'),
                    'level' => $categoryData['level'] ?? 0,
                    'parent_id' => $parentId,
                    'purpose' => $categoryData['purpose'] ?? null,
                    'roles' => $categoryData['roles'] ?? null,
                ]
            );

            $syncedCount++;

            if (!empty($categoryData['children'])) {
                $syncedCount += $this->syncCategories($categoryData['children'], $category->id);
            }
        }

        return $syncedCount;
    }

    public function syncAll(bool $forceRefresh = false): array
    {
        $categories = $this->fetchCategories($forceRefresh);

        if (empty($categories)) {
            return [
                'success' => false,
                'message' => 'No categories received from API.',
                'synced_count' => 0,
            ];
        }

        $syncedCount = $this->syncCategories($categories);

        return [
            'success' => true,
            'message' => "Successfully synced {$syncedCount} categories.",
            'synced_count' => $syncedCount,
        ];
    }

    private function generateSlug(string $name): string
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    }
}


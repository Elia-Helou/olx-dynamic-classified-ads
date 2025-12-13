<?php

namespace App\Console\Commands;

use App\Services\CategoryFieldSyncService;
use App\Services\CategorySyncService;
use Illuminate\Console\Command;

class ClearOlxCacheCommand extends Command
{
    protected $signature = 'olx:clear-cache {--category= : Clear cache for specific category external_id}';

    protected $description = 'Clear OLX API cache (categories and/or category fields)';

    public function handle(
        CategorySyncService $categoryService,
        CategoryFieldSyncService $fieldService
    ): int {
        $categoryExternalId = $this->option('category');

        if ($categoryExternalId) {
            $this->info("Clearing cache for category: {$categoryExternalId}");
            $fieldService->clearCache($categoryExternalId);
            $this->info('Cache cleared successfully.');
        } else {
            $this->info('Clearing all OLX cache...');
            $categoryService->clearCache();
            $fieldService->clearCache();
            $this->info('All cache cleared successfully.');
        }

        return Command::SUCCESS;
    }
}

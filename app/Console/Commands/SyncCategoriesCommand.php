<?php

namespace App\Console\Commands;

use App\Services\CategorySyncService;
use Illuminate\Console\Command;

class SyncCategoriesCommand extends Command
{
    protected $signature = 'olx:sync-categories {--force : Force refresh by clearing cache}';

    protected $description = 'Sync categories from OLX API (idempotent)';

    public function handle(CategorySyncService $service): int
    {
        $forceRefresh = $this->option('force');
        
        if ($forceRefresh) {
            $this->info('Clearing cache...');
            $service->clearCache();
        }

        $this->info('Fetching categories from OLX API...');

        try {
            $result = $service->syncAll($forceRefresh);

            if ($result['success']) {
                $this->info($result['message']);
                $this->info("Synced {$result['synced_count']} categories.");
                return Command::SUCCESS;
            }

            $this->warn($result['message']);
            return Command::FAILURE;
        } catch (\Exception $e) {
            $this->error('Failed to sync categories: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

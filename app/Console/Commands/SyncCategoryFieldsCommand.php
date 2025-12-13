<?php

namespace App\Console\Commands;

use App\Services\CategoryFieldSyncService;
use Illuminate\Console\Command;

class SyncCategoryFieldsCommand extends Command
{
    protected $signature = 'olx:sync-category-fields {--force : Force refresh by clearing cache}';

    protected $description = 'Sync category fields from OLX API (idempotent)';

    public function handle(CategoryFieldSyncService $service): int
    {
        $forceRefresh = $this->option('force');
        
        if ($forceRefresh) {
            $this->info('Clearing cache...');
            $service->clearCache();
        }

        $this->info('Syncing category fields from OLX API...');

        try {
            $result = $service->syncAll($forceRefresh);

            if ($result['success']) {
                $this->info($result['message']);
                $this->info("Successfully synced: {$result['success_count']} categories");
                
                if ($result['error_count'] > 0) {
                    $this->warn("Failed: {$result['error_count']} categories");
                }

                return Command::SUCCESS;
            }

            $this->warn($result['message']);
            return Command::FAILURE;
        } catch (\Exception $e) {
            $this->error('Failed to sync category fields: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

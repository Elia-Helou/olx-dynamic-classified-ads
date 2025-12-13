<?php

namespace App\Console\Commands;

use App\Services\CategorySyncService;
use Illuminate\Console\Command;

class SyncCategoriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'olx:sync-categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync categories from OLX API (idempotent)';

    public function handle(CategorySyncService $service): int
    {
        $this->info('Fetching categories from OLX API...');

        try {
            $result = $service->syncAll();

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

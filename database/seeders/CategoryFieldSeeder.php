<?php

namespace Database\Seeders;

use App\Services\CategoryFieldSyncService;
use Illuminate\Database\Seeder;

class CategoryFieldSeeder extends Seeder
{
    public function run(CategoryFieldSyncService $service): void
    {
        $this->command->info('Syncing category fields from OLX API...');

        try {
            $result = $service->syncAll();

            if ($result['success']) {
                $this->command->info($result['message']);
                $this->command->info("Successfully synced: {$result['success_count']} categories");
                
                if ($result['error_count'] > 0) {
                    $this->command->warn("Failed: {$result['error_count']} categories");
                }
            } else {
                $this->command->warn($result['message']);
            }
        } catch (\Exception $e) {
            $this->command->error('Failed to sync category fields: ' . $e->getMessage());
        }
    }
}

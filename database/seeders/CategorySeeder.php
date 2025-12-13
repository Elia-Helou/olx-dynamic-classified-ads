<?php

namespace Database\Seeders;

use App\Services\CategorySyncService;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(CategorySyncService $service): void
    {
        $this->command->info('Syncing categories from OLX API...');

        try {
            $result = $service->syncAll();

            if ($result['success']) {
                $this->command->info($result['message']);
            } else {
                $this->command->warn($result['message']);
            }
        } catch (\Exception $e) {
            $this->command->error('Failed to sync categories: ' . $e->getMessage());
        }
    }
}

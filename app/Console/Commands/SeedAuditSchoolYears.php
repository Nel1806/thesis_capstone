<?php

namespace App\Console\Commands;

use App\Services\MultiYearAuditSeeder;
use Illuminate\Console\Command;

class SeedAuditSchoolYears extends Command
{
    protected $signature = 'audit:seed-years';

    protected $description = 'Create placeholder school years (same schools/grades as 2025-2026, all values zero).';

    public function handle(MultiYearAuditSeeder $seeder): int
    {
        $created = $seeder->syncPlaceholderYears();

        if ($created === 0) {
            $this->info('Placeholder years are up to date (or 2025-2026 base data is missing).');

            return self::SUCCESS;
        }

        $this->info("Created {$created} placeholder school year(s) with zero values.");

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Services\MultiYearAuditSeeder;
use App\Services\NormalizeSchoolAuditGrades;
use Illuminate\Console\Command;

class NormalizeAuditGrades extends Command
{
    protected $signature = 'audit:normalize-grades';

    protected $description = 'Remove grade 7, add kindergarten (grade 0) on top of each school audit.';

    public function handle(
        NormalizeSchoolAuditGrades $normalizer,
        MultiYearAuditSeeder $yearSeeder,
    ): int {
        $normalizer->applyToSeedJson();
        $this->info('Updated teacher_audit_seed.json.');

        $result = $normalizer->applyToDatabase();
        $this->info("Removed {$result['deleted']} grade 7+ row(s).");
        $this->info("Added {$result['inserted']} kindergarten row(s).");

        $yearSeeder->syncPlaceholderYears();
        $this->info('Refreshed placeholder school years (zeros).');

        return self::SUCCESS;
    }
}

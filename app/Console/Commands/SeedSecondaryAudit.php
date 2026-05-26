<?php

namespace App\Console\Commands;

use App\Services\SecondaryAuditSeeder;
use Illuminate\Console\Command;

class SeedSecondaryAudit extends Command
{
    protected $signature = 'audit:seed-secondary';

    protected $description = 'Create secondary school audit rows for all configured years.';

    public function handle(SecondaryAuditSeeder $seeder): int
    {
        $created = $seeder->seedMissingYears();

        if ($created === 0) {
            $this->info('Secondary school audit rows are up to date.');

            return self::SUCCESS;
        }

        $this->info("Seeded secondary school audit rows for {$created} school year(s).");

        return self::SUCCESS;
    }
}

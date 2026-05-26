<?php

namespace App\Console\Commands;

use App\Services\AuditYearCatalogSeeder;
use Illuminate\Console\Command;

class SyncAuditCatalog extends Command
{
    protected $signature = 'audit:sync-catalog';

    protected $description = 'Sync school years, schools, and school year audit records from config and legacy imports';

    public function handle(AuditYearCatalogSeeder $seeder): int
    {
        $result = $seeder->sync();

        $this->info('Catalog sync complete.');
        $this->line("  School years added: {$result['years']}");
        $this->line("  Schools added: {$result['schools']}");
        $this->line("  Imports updated: {$result['imports']}");
        $this->line("  School year records added: {$result['grade_rows']}");

        return self::SUCCESS;
    }
}

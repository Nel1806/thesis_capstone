<?php

namespace Tests\Concerns;

use App\Services\AuditYearCatalogSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait SeedsAuditCatalog
{
    protected function seedAuditCatalog(): void
    {
        app(AuditYearCatalogSeeder::class)->sync();
    }

    /**
     * Insert a grade audit row and sync the school year record used by the UI.
     */
    /**
     * @return array{record_id: int, grade_audit_id: int}
     */
    protected function insertGradeAuditRow(array $attributes): array
    {
        $defaults = [
            'school_level' => 'elementary',
            'learners' => 0,
            'sections' => 1,
            'class_size' => 0,
            'required_teachers' => 0,
            'available_teachers' => 0,
            'surplus' => 0,
            'shortage' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $gradeAuditId = (int) DB::table('school_grade_audits')->insertGetId(array_merge($defaults, $attributes));

        $row = DB::table('school_grade_audits')->where('id', $gradeAuditId)->first();
        app(AuditYearCatalogSeeder::class)->upsertFromGradeAudit($row);

        $recordId = $gradeAuditId;

        if (Schema::hasTable('school_year_records')) {
            $recordId = (int) (DB::table('school_year_records')
                ->where('school_grade_audit_id', $gradeAuditId)
                ->value('id') ?? $gradeAuditId);
        }

        return [
            'record_id' => $recordId,
            'grade_audit_id' => $gradeAuditId,
        ];
    }

    protected function insertAuditImport(string $year, string $fileName = 'test.xlsx', string $level = 'elementary'): int
    {
        $yearId = DB::table('school_years')->where('label', $year)->value('id');

        return (int) DB::table('audit_imports')->insertGetId([
            'file_name' => $fileName,
            'school_year' => $year,
            'school_year_id' => $yearId,
            'school_level' => $level,
            'sheet_count' => 1,
            'row_count' => 1,
            'imported_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

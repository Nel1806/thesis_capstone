<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class MultiYearAuditSeeder
{
    private const BASE_YEAR = '2025-2026';

    /**
     * Ensure every configured year after 2025-2026 exists with the same
     * schools/grade rows as the base year, but all numeric values set to zero.
     */
    public function syncPlaceholderYears(): int
    {
        app(AuditYearCatalogSeeder::class)->syncMetadata();

        $baseImport = DB::table('audit_imports')
            ->where('school_year', self::BASE_YEAR)
            ->where('school_level', 'elementary')
            ->orderBy('id')
            ->first();

        if (! $baseImport) {
            $baseImport = DB::table('audit_imports')
                ->where('school_year', self::BASE_YEAR)
                ->orderBy('id')
                ->first();
        }

        if (! $baseImport) {
            return 0;
        }

        $baseRows = DB::table('school_grade_audits')
            ->where('audit_import_id', $baseImport->id)
            ->where('school_level', 'elementary')
            ->get();

        if ($baseRows->isEmpty()) {
            return 0;
        }

        $years = config('audit_school_years', [self::BASE_YEAR]);
        $existingYears = DB::table('audit_imports')
            ->where('school_level', 'elementary')
            ->pluck('school_year')
            ->all();
        $catalog = app(AuditYearCatalogSeeder::class);
        $created = 0;

        foreach ($years as $year) {
            if ($year === self::BASE_YEAR || in_array($year, $existingYears, true)) {
                continue;
            }

            DB::transaction(function () use ($baseImport, $baseRows, $year, $catalog, &$created) {
                $yearId = DB::table('school_years')->where('label', $year)->value('id');

                $importId = DB::table('audit_imports')->insertGetId([
                    'file_name' => 'Elementary School Teacher Audit-SY-'.$year.'.xlsx',
                    'school_year' => $year,
                    'school_year_id' => $yearId,
                    'school_level' => 'elementary',
                    'sheet_count' => $baseImport->sheet_count,
                    'row_count' => $baseImport->row_count,
                    'imported_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $now = now();

                foreach ($baseRows as $row) {
                    $gradeAuditId = DB::table('school_grade_audits')->insertGetId([
                        'audit_import_id' => $importId,
                        'school_code' => $row->school_code,
                        'school_level' => 'elementary',
                        'grade_level' => $row->grade_level,
                        'learners' => 0,
                        'sections' => 1,
                        'class_size' => 0,
                        'required_teachers' => 0,
                        'available_teachers' => 0,
                        'surplus' => 0,
                        'shortage' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $catalog->upsertFromGradeAudit((object) array_merge((array) $row, [
                        'id' => $gradeAuditId,
                        'audit_import_id' => $importId,
                        'school_level' => 'elementary',
                        'learners' => 0,
                        'sections' => 1,
                        'class_size' => 0,
                        'required_teachers' => 0,
                        'available_teachers' => 0,
                        'surplus' => 0,
                        'shortage' => 0,
                    ]));
                }

                $created++;
            });
        }

        return $created;
    }

    public function seedMissingYears(): int
    {
        $this->deleteNonBaseYears();

        $placeholder = $this->syncPlaceholderYears();
        $catalog = app(AuditYearCatalogSeeder::class)->sync();

        return max($placeholder, $catalog['grade_rows']);
    }

    private function deleteNonBaseYears(): void
    {
        $importIds = DB::table('audit_imports')
            ->where('school_year', '!=', self::BASE_YEAR)
            ->where('school_level', 'elementary')
            ->pluck('id');

        if ($importIds->isEmpty()) {
            return;
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('school_year_records')) {
            $yearIds = DB::table('audit_imports')
                ->whereIn('id', $importIds)
                ->pluck('school_year_id')
                ->filter()
                ->unique();

            DB::table('school_year_records')->whereIn('school_year_id', $yearIds)->delete();
        }

        DB::table('school_grade_audits')->whereIn('audit_import_id', $importIds)->delete();
        DB::table('audit_imports')->whereIn('id', $importIds)->delete();
    }
}

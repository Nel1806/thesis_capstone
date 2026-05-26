<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SecondaryAuditSeeder
{
    public function seedMissingYears(): int
    {
        app(AuditYearCatalogSeeder::class)->syncMetadata();

        $years = config('audit_school_years', ['2025-2026']);
        $schools = config('audit_secondary_schools', []);
        $grades = array_keys(config('audit_secondary_grades', []));

        if (empty($schools) || empty($grades)) {
            return 0;
        }

        $catalog = app(AuditYearCatalogSeeder::class);
        $created = 0;

        foreach ($years as $year) {
            $yearId = DB::table('school_years')->where('label', $year)->value('id');

            $importId = DB::table('audit_imports')
                ->where('school_year', $year)
                ->where('school_level', 'secondary')
                ->value('id');

            if (! $importId) {
                $importId = DB::table('audit_imports')->insertGetId([
                    'file_name' => 'Secondary School Teacher Audit-SY-'.$year.'.xlsx',
                    'school_year' => $year,
                    'school_year_id' => $yearId,
                    'school_level' => 'secondary',
                    'sheet_count' => count($schools),
                    'row_count' => count($schools) * count($grades),
                    'imported_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($schools as $schoolCode => $name) {
                foreach ($grades as $gradeLevel) {
                    $exists = DB::table('school_grade_audits')
                        ->where('audit_import_id', $importId)
                        ->where('school_code', $schoolCode)
                        ->where('school_level', 'secondary')
                        ->where('grade_level', $gradeLevel)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $gradeAuditId = DB::table('school_grade_audits')->insertGetId([
                        'audit_import_id' => $importId,
                        'school_code' => $schoolCode,
                        'school_level' => 'secondary',
                        'grade_level' => (int) $gradeLevel,
                        'learners' => 0,
                        'sections' => 1,
                        'class_size' => 0,
                        'required_teachers' => 0,
                        'available_teachers' => 0,
                        'surplus' => 0,
                        'shortage' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if (Schema::hasTable('school_year_records')) {
                        $catalog->upsertFromGradeAudit(DB::table('school_grade_audits')->where('id', $gradeAuditId)->first());
                    }
                }
            }

            $created++;
        }

        return $created;
    }
}

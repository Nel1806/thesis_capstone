<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuditYearCatalogSeeder
{
    /**
     * @return array{years: int, schools: int, imports: int, grade_rows: int}
     */
    public function sync(bool $includeGradeRecords = true): array
    {
        if (! Schema::hasTable('school_years')) {
            return ['years' => 0, 'schools' => 0, 'imports' => 0, 'grade_rows' => 0];
        }

        $years = $this->syncSchoolYears();
        $schools = $this->syncSchools();
        $imports = $this->syncAuditImports();
        $gradeRows = $includeGradeRecords ? $this->syncSchoolYearRecords() : 0;

        return [
            'years' => $years,
            'schools' => $schools,
            'imports' => $imports,
            'grade_rows' => $gradeRows,
        ];
    }

    public function syncMetadata(): void
    {
        $this->sync(includeGradeRecords: false);
    }

    public function upsertFromGradeAudit(object $row): void
    {
        if (! Schema::hasTable('school_year_records')) {
            return;
        }

        $import = DB::table('audit_imports')->where('id', $row->audit_import_id)->first();

        if (! $import) {
            return;
        }

        if (! $import->school_year_id) {
            $import->school_year_id = DB::table('school_years')
                ->where('label', $import->school_year)
                ->value('id');
        }

        if (! $import->school_year_id) {
            return;
        }

        $level = $row->school_level ?? $import->school_level ?? 'elementary';
        $school = $this->resolveSchool($row->school_code, $level);
        $gradeLabel = config(
            ($level === 'secondary' ? 'audit_secondary_grades' : 'audit_grades').'.'.$row->grade_level,
            'Grade '.$row->grade_level
        );

        $payload = [
            'school_year_id' => $import->school_year_id,
            'school_id' => $school->id,
            'school_name' => $school->name,
            'basic_education_level' => $level,
            'grade' => $gradeLabel,
            'grade_level' => $row->grade_level,
            'learners' => $row->learners,
            'sections' => $row->sections,
            'class_size' => $row->class_size,
            'teacher_requirement' => $row->required_teachers,
            'current_teachers' => $row->available_teachers,
            'teacher_surplus' => max(0, (int) $row->surplus),
            'teacher_needs' => max(0, (int) $row->shortage),
            'school_grade_audit_id' => $row->id,
            'updated_at' => now(),
        ];

        $existing = DB::table('school_year_records')
            ->where('school_year_id', $import->school_year_id)
            ->where('school_id', $school->id)
            ->where('grade_level', $row->grade_level)
            ->first();

        if ($existing) {
            DB::table('school_year_records')->where('id', $existing->id)->update($payload);

            return;
        }

        DB::table('school_year_records')->insert(array_merge($payload, [
            'created_at' => now(),
        ]));
    }

    private function syncSchoolYears(): int
    {
        $years = config('audit_school_years', ['2025-2026']);
        $now = now();
        $count = 0;

        foreach ($years as $index => $label) {
            $exists = DB::table('school_years')->where('label', $label)->exists();

            if ($exists) {
                DB::table('school_years')->where('label', $label)->update([
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('school_years')->insert([
                    'label' => $label,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $count++;
            }
        }

        return $count;
    }

    private function syncSchools(): int
    {
        $now = now();
        $count = 0;

        foreach (['elementary' => config('audit_schools', []), 'secondary' => config('audit_secondary_schools', [])] as $level => $schools) {
            foreach ($schools as $code => $name) {
                $exists = DB::table('schools')
                    ->where('code', $code)
                    ->where('school_level', $level)
                    ->exists();

                if ($exists) {
                    DB::table('schools')
                        ->where('code', $code)
                        ->where('school_level', $level)
                        ->update(['name' => $name, 'updated_at' => $now]);
                } else {
                    DB::table('schools')->insert([
                        'code' => $code,
                        'name' => $name,
                        'school_level' => $level,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $count++;
                }
            }
        }

        return $count;
    }

    private function syncAuditImports(): int
    {
        $yearIds = DB::table('school_years')->pluck('id', 'label');
        $updated = 0;

        foreach (DB::table('audit_imports')->get(['id', 'school_year', 'file_name', 'school_year_id', 'school_level']) as $import) {
            $yearId = $yearIds[$import->school_year] ?? null;
            $level = str_contains(strtolower($import->file_name), 'secondary') ? 'secondary' : 'elementary';

            if ($import->school_year_id === $yearId && $import->school_level === $level) {
                continue;
            }

            DB::table('audit_imports')->where('id', $import->id)->update([
                'school_year_id' => $yearId,
                'school_level' => $level,
            ]);
            $updated++;
        }

        return $updated;
    }

    private function syncSchoolYearRecords(): int
    {
        if (! Schema::hasTable('school_grade_audits') || ! Schema::hasColumn('school_grade_audits', 'school_level')) {
            return 0;
        }

        $missing = DB::table('school_grade_audits')
            ->leftJoin('school_year_records', 'school_year_records.school_grade_audit_id', '=', 'school_grade_audits.id')
            ->whereNull('school_year_records.id')
            ->select('school_grade_audits.*')
            ->get();

        foreach ($missing as $row) {
            $this->upsertFromGradeAudit($row);
        }

        return $missing->count();
    }

    private function resolveSchool(string $code, string $level): object
    {
        $school = DB::table('schools')
            ->where('code', $code)
            ->where('school_level', $level)
            ->first();

        if ($school) {
            return $school;
        }

        $name = config(($level === 'secondary' ? 'audit_secondary_schools' : 'audit_schools').'.'.$code, $code);
        $id = DB::table('schools')->insertGetId([
            'code' => $code,
            'name' => $name,
            'school_level' => $level,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('schools')->where('id', $id)->first();
    }
}

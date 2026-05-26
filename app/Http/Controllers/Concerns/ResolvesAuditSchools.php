<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait ResolvesAuditSchools
{
    private static ?bool $schoolYearRecordsTableExists = null;

    /** @var array<int, string>|null */
    private static ?array $cachedSchoolYearLabels = null;

    protected function resolveAuditLevel(?string $level): string
    {
        return in_array($level, ['elementary', 'secondary'], true) ? $level : 'elementary';
    }

    protected function usesSchoolYearRecords(): bool
    {
        if (self::$schoolYearRecordsTableExists === null) {
            self::$schoolYearRecordsTableExists = Schema::hasTable('school_year_records');
        }

        return self::$schoolYearRecordsTableExists;
    }

    protected function auditRecordsTable(): string
    {
        return $this->usesSchoolYearRecords() ? 'school_year_records' : 'school_grade_audits';
    }

    protected function schoolCodeColumn(): string
    {
        return $this->usesSchoolYearRecords() ? 'schools.code' : 'school_grade_audits.school_code';
    }

    protected function applySchoolYearScope(Builder $query, string $schoolYear, string $level): Builder
    {
        if ($this->usesSchoolYearRecords()) {
            return $query
                ->join('school_years', 'school_year_records.school_year_id', '=', 'school_years.id')
                ->join('schools', 'school_year_records.school_id', '=', 'schools.id')
                ->where('school_years.label', $schoolYear)
                ->where('school_year_records.basic_education_level', $level);
        }

        $schoolCodes = $this->schoolOptionsFromConfig($level)->pluck('code')->all();

        return $query
            ->join('audit_imports', 'school_grade_audits.audit_import_id', '=', 'audit_imports.id')
            ->where('audit_imports.school_year', $schoolYear)
            ->when(
                Schema::hasColumn('school_grade_audits', 'school_level'),
                fn (Builder $scoped) => $scoped->where('school_grade_audits.school_level', $level)
            )
            ->whereIn('school_grade_audits.school_code', $schoolCodes);
    }

    protected function withSchoolName(object $school, string $level = 'elementary'): object
    {
        if ($this->usesSchoolYearRecords()) {
            $school->school_name = $school->school_name ?? $this->schoolName($school->school_code ?? null, $level);

            return $school;
        }

        $school->school_name = $this->schoolName($school->school_code, $level);

        return $school;
    }

    protected function schoolName(?string $code, string $level = 'elementary'): string
    {
        if (! $code) {
            return '';
        }

        if (Schema::hasTable('schools')) {
            $name = DB::table('schools')
                ->where('code', $code)
                ->where('school_level', $level)
                ->value('name');

            if ($name) {
                return $name;
            }
        }

        return config($this->schoolConfigKey($level).'.'.$code, $code);
    }

    protected function resolveSchoolYear(?string $year): string
    {
        $labels = $this->schoolYearOptions();

        if ($year && in_array($year, $labels, true)) {
            return $year;
        }

        return $labels[0] ?? '2025-2026';
    }

    protected function schoolYearOptions(): array
    {
        if (self::$cachedSchoolYearLabels !== null) {
            return self::$cachedSchoolYearLabels;
        }

        if (Schema::hasTable('school_years')) {
            $labels = DB::table('school_years')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->pluck('label')
                ->all();

            if ($labels !== []) {
                return self::$cachedSchoolYearLabels = $labels;
            }
        }

        return self::$cachedSchoolYearLabels = config('audit_school_years', ['2025-2026']);
    }

    protected function schoolOptionsFromConfig(string $level = 'elementary'): \Illuminate\Support\Collection
    {
        if (Schema::hasTable('schools')) {
            $fromDb = $this->schoolOptionsFromDatabase($level);

            if ($fromDb->isNotEmpty()) {
                return $fromDb;
            }
        }

        return collect(config($this->schoolConfigKey($level), []))
            ->map(fn (string $name, string $code) => [
                'code' => $code,
                'name' => $name,
            ])
            ->sortBy('name')
            ->values();
    }

    protected function schoolOptionsFromDatabase(string $level = 'elementary'): \Illuminate\Support\Collection
    {
        return DB::table('schools')
            ->where('school_level', $level)
            ->orderBy('name')
            ->get(['code', 'name'])
            ->map(fn ($school) => [
                'code' => $school->code,
                'name' => $school->name,
            ])
            ->values();
    }

    protected function schoolConfigKey(string $level): string
    {
        return $level === 'secondary' ? 'audit_secondary_schools' : 'audit_schools';
    }

    protected function gradeConfigKey(string $level): string
    {
        return $level === 'secondary' ? 'audit_secondary_grades' : 'audit_grades';
    }

    protected function gradeLabel(int $gradeLevel, string $level = 'elementary'): string
    {
        return config($this->gradeConfigKey($level).'.'.$gradeLevel, 'Grade '.$gradeLevel);
    }

    protected function gradeRange(string $level): array
    {
        return $level === 'secondary'
            ? ['min' => 7, 'max' => 12]
            : ['min' => 0, 'max' => 6];
    }

    protected function mapRecordForDisplay(object $row): object
    {
        if (! $this->usesSchoolYearRecords()) {
            return $row;
        }

        $row->school_code = $row->school_code ?? null;
        $row->available_teachers = (int) $row->current_teachers;
        $row->required_teachers = (int) $row->teacher_requirement;
        $row->surplus = (int) $row->teacher_surplus;
        $row->shortage = (int) $row->teacher_needs;

        return $row;
    }
}

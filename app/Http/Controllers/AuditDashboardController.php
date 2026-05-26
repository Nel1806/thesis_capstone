<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesAuditSchools;
use App\Services\AuditYearCatalogSeeder;
use App\Services\SecondaryAuditSeeder;
use App\Services\TeacherRequirementCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuditDashboardController extends Controller
{
    use ResolvesAuditSchools;

    public function index(Request $request): View
    {
        $level = $this->resolveAuditLevel($request->query('level'));
        $this->ensureSecondaryAuditDataIfNeeded($level);

        $selectedSchoolYear = $this->resolveSchoolYear($request->query('year'));
        $schoolYearOptions = $this->schoolYearOptions();
        $gradeConfig = $level === 'secondary' ? 'audit_secondary_grades' : 'audit_grades';
        $levelLabel = $level === 'secondary' ? 'Secondary' : 'Elementary';
        $table = $this->auditRecordsTable();

        $totals = $this->applySchoolYearScope(DB::table($table), $selectedSchoolYear, $level)
            ->selectRaw($this->usesSchoolYearRecords()
                ? 'COUNT(DISTINCT school_year_records.school_id) as schools'
                : 'COUNT(DISTINCT school_grade_audits.school_code) as schools')
            ->selectRaw("SUM({$table}.learners) as learners")
            ->selectRaw("SUM({$table}.sections) as sections")
            ->selectRaw($this->usesSchoolYearRecords()
                ? "SUM({$table}.teacher_requirement) as required_teachers"
                : "SUM({$table}.required_teachers) as required_teachers")
            ->selectRaw($this->usesSchoolYearRecords()
                ? "SUM({$table}.current_teachers) as available_teachers"
                : "SUM({$table}.available_teachers) as available_teachers")
            ->selectRaw($this->usesSchoolYearRecords()
                ? "SUM({$table}.teacher_needs) as shortage"
                : "SUM({$table}.shortage) as shortage")
            ->selectRaw($this->usesSchoolYearRecords()
                ? "SUM({$table}.teacher_surplus) as surplus"
                : "SUM({$table}.surplus) as surplus")
            ->first();

        $schools = $this->schoolsSummaryForLevel($selectedSchoolYear, $level);
        $gradeLevels = $this->gradeLevelsSummaryForLevel($selectedSchoolYear, $level);

        return view('dashboard', compact(
            'totals',
            'schools',
            'gradeLevels',
            'schoolYearOptions',
            'selectedSchoolYear',
            'level',
            'levelLabel',
            'gradeConfig',
        ));
    }

    public function schools(Request $request): View
    {
        $level = $this->resolveAuditLevel($request->query('level'));

        return $this->schoolsByLevel(
            $request,
            $level,
            'schools',
            $level === 'secondary' ? 'audit_secondary_grades' : 'audit_grades',
            'schools.update',
            'schools',
        );
    }

    public function updateSchool(Request $request, string $school): RedirectResponse
    {
        $level = $this->resolveAuditLevel($request->input('school_level', $request->query('level')));

        return $this->updateSchoolByLevel($request, $school, $level);
    }

    private function schoolsSummaryForLevel(string $schoolYear, string $level)
    {
        $table = $this->auditRecordsTable();

        if ($this->usesSchoolYearRecords()) {
            return $this->applySchoolYearScope(DB::table($table), $schoolYear, $level)
                ->select('schools.code as school_code', 'school_year_records.school_name')
                ->selectRaw("SUM({$table}.learners) as learners")
                ->selectRaw("SUM({$table}.sections) as sections")
                ->selectRaw("SUM({$table}.teacher_requirement) as required_teachers")
                ->selectRaw("SUM({$table}.current_teachers) as available_teachers")
                ->selectRaw("SUM({$table}.teacher_needs) as shortage")
                ->selectRaw("SUM({$table}.teacher_surplus) as surplus")
                ->groupBy('schools.code', 'school_year_records.school_name')
                ->orderByDesc('shortage')
                ->orderBy('schools.code')
                ->get()
                ->map(fn ($school) => $this->withSchoolName($school, $level));
        }

        return $this->applySchoolYearScope(DB::table($table), $schoolYear, $level)
            ->select('school_grade_audits.school_code')
            ->selectRaw("SUM({$table}.learners) as learners")
            ->selectRaw("SUM({$table}.sections) as sections")
            ->selectRaw("SUM({$table}.required_teachers) as required_teachers")
            ->selectRaw("SUM({$table}.available_teachers) as available_teachers")
            ->selectRaw("SUM({$table}.shortage) as shortage")
            ->selectRaw("SUM({$table}.surplus) as surplus")
            ->groupBy('school_grade_audits.school_code')
            ->orderByDesc('shortage')
            ->orderBy('school_grade_audits.school_code')
            ->get()
            ->map(fn ($school) => $this->withSchoolName($school, $level));
    }

    private function gradeLevelsSummaryForLevel(string $schoolYear, string $level)
    {
        $table = $this->auditRecordsTable();

        return $this->applySchoolYearScope(DB::table($table), $schoolYear, $level)
            ->select("{$table}.grade_level")
            ->selectRaw("SUM({$table}.learners) as learners")
            ->groupBy("{$table}.grade_level")
            ->orderBy("{$table}.grade_level")
            ->get();
    }

    private function schoolsByLevel(
        Request $request,
        string $level,
        string $view,
        string $gradeConfig,
        string $updateRoute,
        string $selfRoute,
    ): View {
        $this->ensureSecondaryAuditDataIfNeeded($level);

        $selectedSchoolYear = $this->resolveSchoolYear($request->query('year'));
        $schoolYearOptions = $this->schoolYearOptions();
        $schoolOptions = $this->schoolOptionsFromConfig($level);
        $selectedSchool = $request->query('school');
        $validSchoolCodes = $schoolOptions->pluck('code');

        if ($selectedSchool && ! $validSchoolCodes->contains($selectedSchool)) {
            $selectedSchool = null;
        }

        if (! $selectedSchool) {
            $selectedSchool = $schoolOptions->first()['code'] ?? null;
        }

        $calculator = app(TeacherRequirementCalculator::class);
        $table = $this->auditRecordsTable();

        $query = $this->applySchoolYearScope(DB::table($table), $selectedSchoolYear, $level)
            ->select("{$table}.*");

        if ($this->usesSchoolYearRecords()) {
            $query->addSelect('schools.code as school_code');
        }

        $rows = $query
            ->when($selectedSchool, function ($scoped) use ($selectedSchool, $table) {
                if ($this->usesSchoolYearRecords()) {
                    return $scoped->where('schools.code', $selectedSchool);
                }

                return $scoped->where("{$table}.school_code", $selectedSchool);
            })
            ->orderBy("{$table}.grade_level")
            ->get()
            ->map(fn ($row) => $calculator->applyToRow($this->mapRecordForDisplay($row)));

        $selectedSchoolName = $this->schoolName($selectedSchool, $level);
        $summary = $this->summaryFromRows($rows);

        return view($view, compact(
            'schoolOptions',
            'selectedSchool',
            'selectedSchoolName',
            'rows',
            'summary',
            'schoolYearOptions',
            'selectedSchoolYear',
            'gradeConfig',
            'updateRoute',
            'selfRoute',
            'level',
        ));
    }

    private function updateSchoolByLevel(Request $request, string $school, string $level): RedirectResponse
    {
        $allowedYears = $this->schoolYearOptions();

        $validated = $request->validate([
            'school_year' => ['required', 'string', 'in:'.implode(',', $allowedYears)],
            'school_level' => ['required', 'string', 'in:elementary,secondary'],
            'rows' => ['required', 'array'],
            'rows.*.learners' => ['required', 'integer', 'min:0', 'max:99999'],
            'rows.*.available_teachers' => ['required', 'integer', 'min:0', 'max:999'],
        ]);

        $schoolYear = $validated['school_year'];
        $level = $this->resolveAuditLevel($validated['school_level']);
        $calculator = app(TeacherRequirementCalculator::class);
        $catalog = app(AuditYearCatalogSeeder::class);
        $table = $this->auditRecordsTable();

        DB::transaction(function () use ($validated, $school, $schoolYear, $calculator, $level, $catalog, $table) {
            foreach ($validated['rows'] as $id => $data) {
                $learners = (int) $data['learners'];
                $availableTeachers = (int) $data['available_teachers'];

                $query = $this->applySchoolYearScope(DB::table($table), $schoolYear, $level)
                    ->where("{$table}.id", $id);

                if ($this->usesSchoolYearRecords()) {
                    $query->where('schools.code', $school);
                } else {
                    $query->where("{$table}.school_code", $school);
                }

                $auditRow = $query->first();

                if (! $auditRow) {
                    continue;
                }

                $calculated = $calculator->calculate((int) $auditRow->grade_level, $learners);
                $requiredTeachers = $calculated['required_teachers'];
                $difference = $requiredTeachers - $availableTeachers;

                if ($this->usesSchoolYearRecords()) {
                    DB::table('school_year_records')->where('id', $id)->update([
                        'learners' => $learners,
                        'sections' => $calculated['sections'],
                        'class_size' => $calculated['class_size'],
                        'teacher_requirement' => $requiredTeachers,
                        'current_teachers' => $availableTeachers,
                        'teacher_surplus' => max(-$difference, 0),
                        'teacher_needs' => max($difference, 0),
                        'updated_at' => now(),
                    ]);

                    if ($auditRow->school_grade_audit_id) {
                        DB::table('school_grade_audits')->where('id', $auditRow->school_grade_audit_id)->update([
                            'learners' => $learners,
                            'sections' => $calculated['sections'],
                            'class_size' => $calculated['class_size'],
                            'required_teachers' => $requiredTeachers,
                            'available_teachers' => $availableTeachers,
                            'surplus' => max(-$difference, 0),
                            'shortage' => max($difference, 0),
                            'updated_at' => now(),
                        ]);
                    }

                    continue;
                }

                DB::table('school_grade_audits')
                    ->where('id', $id)
                    ->where('school_code', $school)
                    ->where('school_level', $level)
                    ->update([
                        'learners' => $learners,
                        'sections' => $calculated['sections'],
                        'class_size' => $calculated['class_size'],
                        'required_teachers' => $requiredTeachers,
                        'available_teachers' => $availableTeachers,
                        'surplus' => max(-$difference, 0),
                        'shortage' => max($difference, 0),
                        'updated_at' => now(),
                    ]);

                $gradeRow = DB::table('school_grade_audits')->where('id', $id)->first();

                if ($gradeRow) {
                    $catalog->upsertFromGradeAudit($gradeRow);
                }
            }
        });

        return redirect()
            ->route('schools', [
                'school' => $school,
                'year' => $schoolYear,
                'level' => $level,
            ])
            ->with('status', 'Enrollment saved. Sections, class size, and teacher requirement were recalculated from Parameters.');
    }

    private function ensureSecondaryAuditDataIfNeeded(string $level): void
    {
        if ($level !== 'secondary') {
            return;
        }

        $hasSecondaryRows = DB::table('school_grade_audits')
            ->where('school_level', 'secondary')
            ->limit(1)
            ->exists();

        if (! $hasSecondaryRows) {
            app(SecondaryAuditSeeder::class)->seedMissingYears();
        }
    }

    private function summaryFromRows($rows): object
    {
        return (object) [
            'learners' => $rows->sum('learners'),
            'sections' => $rows->sum('sections'),
            'required_teachers' => $rows->sum(fn ($row) => $row->required_teachers ?? $row->teacher_requirement ?? 0),
            'available_teachers' => $rows->sum(fn ($row) => $row->available_teachers ?? $row->current_teachers ?? 0),
            'shortage' => $rows->sum(fn ($row) => $row->shortage ?? $row->teacher_needs ?? 0),
            'surplus' => $rows->sum(fn ($row) => $row->surplus ?? $row->teacher_surplus ?? 0),
        ];
    }
}

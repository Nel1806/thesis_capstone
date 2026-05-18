<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuditDashboardController extends Controller
{
    public function index(): View
    {
        $import = DB::table('audit_imports')->latest('imported_at')->first();
        $totals = DB::table('school_grade_audits')
            ->selectRaw('COUNT(DISTINCT school_code) as schools')
            ->selectRaw('SUM(learners) as learners')
            ->selectRaw('SUM(sections) as sections')
            ->selectRaw('SUM(required_teachers) as required_teachers')
            ->selectRaw('SUM(available_teachers) as available_teachers')
            ->selectRaw('SUM(shortage) as shortage')
            ->selectRaw('SUM(surplus) as surplus')
            ->first();

        $schools = DB::table('school_grade_audits')
            ->select('school_code')
            ->selectRaw('SUM(learners) as learners')
            ->selectRaw('SUM(sections) as sections')
            ->selectRaw('SUM(required_teachers) as required_teachers')
            ->selectRaw('SUM(available_teachers) as available_teachers')
            ->selectRaw('SUM(shortage) as shortage')
            ->selectRaw('SUM(surplus) as surplus')
            ->groupBy('school_code')
            ->orderByDesc('shortage')
            ->orderBy('school_code')
            ->get()
            ->map(fn ($school) => $this->withSchoolName($school));

        $gradeLevels = DB::table('school_grade_audits')
            ->select('grade_level')
            ->selectRaw('SUM(learners) as learners')
            ->selectRaw('SUM(sections) as sections')
            ->selectRaw('AVG(class_size) as class_size')
            ->selectRaw('SUM(shortage) as shortage')
            ->groupBy('grade_level')
            ->orderBy('grade_level')
            ->get();

        return view('dashboard', compact('import', 'totals', 'schools', 'gradeLevels'));
    }

    public function schools(Request $request): View
    {
        $selectedSchool = $request->query('school');
        $schoolOptions = DB::table('school_grade_audits')
            ->distinct()
            ->orderBy('school_code')
            ->pluck('school_code')
            ->map(fn ($code) => [
                'code' => $code,
                'name' => $this->schoolName($code),
            ]);

        if (! $selectedSchool && $schoolOptions->isNotEmpty()) {
            $selectedSchool = $schoolOptions->first()['code'];
        }

        $rows = DB::table('school_grade_audits')
            ->when($selectedSchool, fn ($query) => $query->where('school_code', $selectedSchool))
            ->orderBy('grade_level')
            ->get();
        $selectedSchoolName = $this->schoolName($selectedSchool);
        $summary = $this->schoolSummary($selectedSchool);

        return view('schools', compact('schoolOptions', 'selectedSchool', 'selectedSchoolName', 'rows', 'summary'));
    }

    public function updateSchool(Request $request, string $school): RedirectResponse
    {
        $validated = $request->validate([
            'rows' => ['required', 'array'],
            'rows.*.learners' => ['required', 'integer', 'min:0', 'max:99999'],
            'rows.*.sections' => ['required', 'integer', 'min:1', 'max:999'],
            'rows.*.class_size' => ['required', 'numeric', 'min:0', 'max:999'],
            'rows.*.required_teachers' => ['required', 'integer', 'min:0', 'max:999'],
            'rows.*.available_teachers' => ['required', 'integer', 'min:0', 'max:999'],
        ]);

        DB::transaction(function () use ($validated, $school) {
            foreach ($validated['rows'] as $id => $data) {
                $learners = (int) $data['learners'];
                $sections = max(1, (int) $data['sections']);
                $requiredTeachers = (int) $data['required_teachers'];
                $availableTeachers = (int) $data['available_teachers'];
                $auditRow = DB::table('school_grade_audits')
                    ->where('id', $id)
                    ->where('school_code', $school)
                    ->first();

                if (! $auditRow) {
                    continue;
                }

                $difference = $requiredTeachers - $availableTeachers;

                DB::table('school_grade_audits')
                    ->where('id', $id)
                    ->where('school_code', $school)
                    ->update([
                        'learners' => $learners,
                        'sections' => $sections,
                        'class_size' => round((float) $data['class_size'], 2),
                        'required_teachers' => $requiredTeachers,
                        'available_teachers' => $availableTeachers,
                        'surplus' => max(-$difference, 0),
                        'shortage' => max($difference, 0),
                        'updated_at' => now(),
                    ]);
            }
        });

        return redirect()
            ->route('schools', ['school' => $school])
            ->with('status', 'School audit updated. Surplus and need teachers were recalculated.');
    }

    private function withSchoolName(object $school): object
    {
        $school->school_name = $this->schoolName($school->school_code);

        return $school;
    }

    private function schoolName(?string $code): string
    {
        if (! $code) {
            return '';
        }

        return config('audit_schools.'.$code, $code);
    }

    private function schoolSummary(?string $school): object
    {
        if (! $school) {
            return (object) [
                'learners' => 0,
                'sections' => 0,
                'required_teachers' => 0,
                'available_teachers' => 0,
                'shortage' => 0,
                'surplus' => 0,
            ];
        }

        return DB::table('school_grade_audits')
            ->where('school_code', $school)
            ->selectRaw('SUM(learners) as learners')
            ->selectRaw('SUM(sections) as sections')
            ->selectRaw('SUM(required_teachers) as required_teachers')
            ->selectRaw('SUM(available_teachers) as available_teachers')
            ->selectRaw('SUM(shortage) as shortage')
            ->selectRaw('SUM(surplus) as surplus')
            ->first();
    }

}

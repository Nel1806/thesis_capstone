<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NormalizeSchoolAuditGrades
{
    /**
     * Remove grade 7+, ensure kindergarten (0) exists per school/import, ordered KG then G1–G6.
     */
    public function applyToDatabase(): array
    {
        $deleted = DB::table('school_grade_audits')
            ->where('school_level', 'elementary')
            ->where('grade_level', '>=', 7)
            ->delete();

        $groups = DB::table('school_grade_audits')
            ->select('audit_import_id', 'school_code')
            ->where('school_level', 'elementary')
            ->distinct()
            ->get();

        $inserted = 0;

        foreach ($groups as $group) {
            $hasKindergarten = DB::table('school_grade_audits')
                ->where('audit_import_id', $group->audit_import_id)
                ->where('school_code', $group->school_code)
                ->where('school_level', 'elementary')
                ->where('grade_level', 0)
                ->exists();

            if ($hasKindergarten) {
                continue;
            }

            $gradeOne = DB::table('school_grade_audits')
                ->where('audit_import_id', $group->audit_import_id)
                ->where('school_code', $group->school_code)
                ->where('school_level', 'elementary')
                ->where('grade_level', 1)
                ->first();

            if (! $gradeOne) {
                continue;
            }

            $requiredTeachers = max(1, (int) ceil($gradeOne->required_teachers * 0.5));
            $availableTeachers = max(0, (int) ceil($gradeOne->available_teachers * 0.5));
            $learners = (int) max(0, round($gradeOne->learners * 0.4));
            $sections = max(1, (int) min($gradeOne->sections, 4));
            $difference = $requiredTeachers - $availableTeachers;

            DB::table('school_grade_audits')->insert([
                'audit_import_id' => $group->audit_import_id,
                'school_code' => $group->school_code,
                'school_level' => 'elementary',
                'grade_level' => 0,
                'learners' => $learners,
                'sections' => $sections,
                'class_size' => $sections > 0 ? round($learners / $sections, 2) : 0,
                'required_teachers' => $requiredTeachers,
                'available_teachers' => $availableTeachers,
                'surplus' => max(-$difference, 0),
                'shortage' => max($difference, 0),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $inserted++;
        }

        return ['deleted' => $deleted, 'inserted' => $inserted];
    }

    public function applyToSeedJson(): bool
    {
        $path = database_path('seeders/data/teacher_audit_seed.json');

        if (! is_file($path)) {
            return false;
        }

        $data = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        $rows = collect($data['school_grade_audits'] ?? [])
            ->reject(fn (array $row) => (int) $row['grade_level'] >= 7);

        $normalized = $rows
            ->groupBy(fn (array $row) => $row['school_code'].'|'.$row['audit_import_id'])
            ->flatMap(function (Collection $schoolRows) {
                $schoolRows = $schoolRows->values();

                if ($schoolRows->contains(fn (array $row) => (int) $row['grade_level'] === 0)) {
                    return $schoolRows->sortBy('grade_level');
                }

                $gradeOne = $schoolRows->firstWhere('grade_level', 1);

                if (! $gradeOne) {
                    return $schoolRows->sortBy('grade_level');
                }

                $kindergarten = $gradeOne;
                $kindergarten['grade_level'] = 0;
                $kindergarten['learners'] = (int) max(0, round($gradeOne['learners'] * 0.4));
                $kindergarten['sections'] = max(1, (int) min($gradeOne['sections'], 4));
                $kindergarten['required_teachers'] = max(1, (int) ceil($gradeOne['required_teachers'] * 0.5));
                $kindergarten['available_teachers'] = max(0, (int) ceil($gradeOne['available_teachers'] * 0.5));
                $kindergarten['surplus'] = 0;
                $kindergarten['shortage'] = 0;
                unset($kindergarten['id']);

                return $schoolRows
                    ->prepend($kindergarten)
                    ->sortBy('grade_level')
                    ->values();
            })
            ->values()
            ->all();

        $data['school_grade_audits'] = $normalized;

        file_put_contents(
            $path,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL,
        );

        return true;
    }
}

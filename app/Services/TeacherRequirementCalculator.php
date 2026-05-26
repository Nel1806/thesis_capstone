<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TeacherRequirementCalculator
{
    private ?Collection $parametersByLevel = null;

    public function calculate(int $gradeLevel, int $learners): array
    {
        $parameter = $this->parameterForGrade($gradeLevel);

        if ($learners <= 0) {
            return [
                'sections' => 1,
                'class_size' => 0.0,
                'required_teachers' => 0,
            ];
        }

        $divisor = $this->sectionDivisor($parameter);
        $sections = max(1, (int) ceil($learners / $divisor));
        $classSize = round($learners / $sections, 2);
        $requiredTeachers = (int) ceil($sections * (float) $parameter->teacher_factor);

        return [
            'sections' => $sections,
            'class_size' => $classSize,
            'required_teachers' => $requiredTeachers,
        ];
    }

    public function applyToRow(object $row): object
    {
        $calculated = $this->calculate((int) $row->grade_level, (int) $row->learners);

        $row->sections = $calculated['sections'];
        $row->class_size = $calculated['class_size'];
        $row->required_teachers = $calculated['required_teachers'];
        $row->teacher_requirement = $calculated['required_teachers'];

        $currentTeachers = (int) ($row->available_teachers ?? $row->current_teachers ?? 0);
        $row->available_teachers = $currentTeachers;
        $row->current_teachers = $currentTeachers;

        $difference = $row->required_teachers - $currentTeachers;
        $row->surplus = max(-$difference, 0);
        $row->shortage = max($difference, 0);
        $row->teacher_surplus = $row->surplus;
        $row->teacher_needs = $row->shortage;

        return $row;
    }

    private function parameterForGrade(int $gradeLevel): object
    {
        $level = config('audit_grades.'.$gradeLevel) ?? config('audit_secondary_grades.'.$gradeLevel);

        if (! $level) {
            throw new RuntimeException("No grade label configured for grade level {$gradeLevel}.");
        }

        $parameter = $this->parametersByLevel()->get($level);

        if (! $parameter) {
            throw new RuntimeException("No planning parameters found for {$level}.");
        }

        return $parameter;
    }

    private function parametersByLevel(): Collection
    {
        if ($this->parametersByLevel !== null) {
            return $this->parametersByLevel;
        }

        $this->parametersByLevel = DB::table('audit_parameters')
            ->orderBy('sort_order')
            ->get()
            ->keyBy('level');

        return $this->parametersByLevel;
    }

    private function sectionDivisor(object $parameter): float
    {
        $maximum = $this->numericParam($parameter->maximum);
        $roundedHalf = $this->numericParam($parameter->rounded_half);

        $divisor = $maximum ?? $roundedHalf ?? 1;

        return max($divisor, 1);
    }

    private function numericParam(?string $value): ?float
    {
        if ($value === null || $value === '' || $value === '-') {
            return null;
        }

        return (float) $value;
    }
}

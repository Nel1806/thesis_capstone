<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;

trait SeedsAuditParameters
{
    protected function seedAuditParameters(): void
    {
        if (DB::table('audit_parameters')->exists()) {
            return;
        }

        $rows = [
            ['Kindergarten', '30', '15', 0.5],
            ['Grade 1', '35', '18', 1.0],
            ['Grade 2', '35', '18', 1.0],
            ['Grade 3', '35', '18', 1.25],
            ['Grade 4', '45', '23', 1.25],
            ['Grade 5', '45', '23', 1.25],
            ['Grade 6', '45', '23', 1.25],
            ['Grade 7', '45', '23', 1.25],
            ['Grade 8', '45', '23', 1.25],
            ['Grade 9', '45', '23', 1.25],
            ['Grade 10', '45', '23', 1.25],
            ['Grade 11', '40', '20', 1.5],
            ['Grade 12', '40', '20', 1.5],
        ];

        foreach ($rows as $index => [$level, $maximum, $roundedHalf, $factor]) {
            $gradeNumber = (int) filter_var($level, FILTER_SANITIZE_NUMBER_INT);
            $group = match (true) {
                $gradeNumber >= 11 => 'Senior High School',
                $gradeNumber >= 7 => 'Junior High School',
                default => 'Elementary',
            };

            DB::table('audit_parameters')->insert([
                'group_name' => $group,
                'level' => $level,
                'minimum' => '25',
                'maximum' => $maximum,
                'rounded_half' => $roundedHalf,
                'small_excess' => '10',
                'teacher_factor' => $factor,
                'class_organization' => '',
                'teacher_specialization' => '',
                'sort_order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

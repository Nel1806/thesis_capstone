<?php

return [
    // Values follow the workbook formulas in the Parameters sheet.
    // section_divisor comes from column E for rows 8-14; the last row follows the sheet formula C18/15.
    'teacher_rules' => [
        0 => ['section_divisor' => 30, 'teacher_factor' => 0.5],
        1 => ['section_divisor' => 30, 'teacher_factor' => 0.5],
        2 => ['section_divisor' => 35, 'teacher_factor' => 1.0],
        3 => ['section_divisor' => 35, 'teacher_factor' => 1.0],
        4 => ['section_divisor' => 35, 'teacher_factor' => 1.25],
        5 => ['section_divisor' => 45, 'teacher_factor' => 1.25],
        6 => ['section_divisor' => 45, 'teacher_factor' => 1.25],
        7 => ['section_divisor' => 45, 'teacher_factor' => 1.25],
        8 => ['section_divisor' => 15, 'teacher_factor' => 1.0],
    ],
];

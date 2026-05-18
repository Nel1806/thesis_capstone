<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuditParametersController extends Controller
{
    public function index(): View
    {
        $this->ensureDefaults();
        $rows = DB::table('audit_parameters')->orderBy('sort_order')->get();

        return view('parameters', compact('rows'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rows' => ['required', 'array'],
            'rows.*.minimum' => ['nullable', 'string', 'max:50'],
            'rows.*.maximum' => ['nullable', 'string', 'max:50'],
            'rows.*.rounded_half' => ['nullable', 'string', 'max:50'],
            'rows.*.small_excess' => ['nullable', 'string', 'max:50'],
            'rows.*.teacher_factor' => ['required', 'numeric', 'min:0', 'max:99'],
        ]);

        foreach ($validated['rows'] as $id => $row) {
            DB::table('audit_parameters')
                ->where('id', $id)
                ->update([
                    ...$row,
                    'updated_at' => now(),
                ]);
        }

        return redirect()->route('parameters')->with('status', 'Parameters updated.');
    }

    private function ensureDefaults(): void
    {
        if (DB::table('audit_parameters')->exists()) {
            return;
        }

        $rows = [
            ['Elementary', 'Kindergarten', '25', '30', '15', '10', 0.5, '25 learners per class, maximum of 30', '1 Teacher per 2 sessions'],
            ['Elementary', 'Grade 1', '30', '35', '18', '10', 1.0, '30 learners per class, maximum of 35', '1 Teacher per class'],
            ['Elementary', 'Grade 2', '30', '35', '18', '10', 1.0, '30 learners per class, maximum of 35', '1 Teacher per class'],
            ['Elementary', 'Grade 3', '30', '35', '18', '10', 1.25, '30 learners per class, maximum of 35', '1 Teacher per class'],
            ['Elementary', 'Grade 4', '40', '45', '23', '10', 1.25, '40 learners per class, maximum of 45', '5 Teachers for every 4 classes'],
            ['Elementary', 'Grade 5', '40', '45', '23', '10', 1.25, '40 learners per class, maximum of 45', '5 Teachers for every 4 classes'],
            ['Elementary', 'Grade 6', '40', '45', '23', '10', 1.25, '40 learners per class, maximum of 45', '5 Teachers for every 4 classes'],
            ['Elementary', 'Multigrade', '-', '25', '-', '', 1.0, 'Max of 25 learners of two consecutive levels', 'Max of 3 MG Teachers for every multi-grade school except Kindergarten'],
            ['Junior High School', 'Grade 7', '40', '45', '23', '10', 1.25, '40 learners per class, maximum of 45', '5 Teachers for every 4 classes'],
            ['Junior High School', 'Grade 8', '40', '45', '23', '10', 1.25, '40 learners per class, maximum of 45', '5 Teachers for every 4 classes'],
            ['Junior High School', 'Grade 9', '40', '45', '23', '10', 1.25, '40 learners per class, maximum of 45', '5 Teachers for every 4 classes'],
            ['Junior High School', 'Grade 10', '40', '45', '23', '10', 1.25, '40 learners per class, maximum of 45', '5 Teachers for every 4 classes'],
            ['Senior High School', 'Grade 11', '40', '40', '20', '10', 1.5, '40 learners per class', '9 Teachers for every 6 classes'],
            ['Senior High School', 'Grade 12', '40', '40', '20', '10', 1.5, '40 learners per class', '9 Teachers for every 6 classes'],
            ['Non-Graded', 'Elem/JHS', '', '15', '8', '', 1.0, '15 learners per class', '1 Teacher per class'],
            ['ALS', 'ALS', '', '75', '', '', 1.0, '', '(within the SDO)'],
        ];

        foreach ($rows as $index => $row) {
            DB::table('audit_parameters')->insert([
                'group_name' => $row[0],
                'level' => $row[1],
                'minimum' => $row[2],
                'maximum' => $row[3],
                'rounded_half' => $row[4],
                'small_excess' => $row[5],
                'teacher_factor' => $row[6],
                'class_organization' => $row[7],
                'teacher_specialization' => $row[8],
                'sort_order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

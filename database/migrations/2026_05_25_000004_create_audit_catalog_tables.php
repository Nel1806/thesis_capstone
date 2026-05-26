<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_years', function (Blueprint $table) {
            $table->id();
            $table->string('label')->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->string('school_level');
            $table->timestamps();

            $table->unique(['code', 'school_level']);
            $table->index('school_level');
        });

        Schema::table('audit_imports', function (Blueprint $table) {
            $table->foreignId('school_year_id')->nullable()->after('school_year')->constrained('school_years')->nullOnDelete();
            $table->string('school_level')->default('elementary')->after('school_year_id');
        });

        Schema::create('school_year_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')->constrained('school_years')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('school_name');
            $table->string('basic_education_level');
            $table->string('grade');
            $table->unsignedTinyInteger('grade_level');
            $table->unsignedInteger('learners')->default(0);
            $table->unsignedInteger('sections')->default(0);
            $table->decimal('class_size', 8, 2)->default(0);
            $table->unsignedInteger('teacher_requirement')->default(0);
            $table->unsignedInteger('current_teachers')->default(0);
            $table->unsignedInteger('teacher_surplus')->default(0);
            $table->unsignedInteger('teacher_needs')->default(0);
            $table->foreignId('school_grade_audit_id')->nullable()->constrained('school_grade_audits')->nullOnDelete();
            $table->timestamps();

            $table->unique(['school_year_id', 'school_id', 'grade_level'], 'syr_year_school_grade_unique');
            $table->index(['school_year_id', 'basic_education_level'], 'syr_year_level_idx');
        });

        $this->seedSchoolYears();
        $this->seedSchools();
        $this->backfillAuditImports();
        $this->backfillSchoolYearRecords();
    }

    public function down(): void
    {
        Schema::dropIfExists('school_year_records');

        Schema::table('audit_imports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_year_id');
            $table->dropColumn('school_level');
        });

        Schema::dropIfExists('schools');
        Schema::dropIfExists('school_years');
    }

    private function seedSchoolYears(): void
    {
        $years = config('audit_school_years', ['2025-2026']);
        $now = now();

        foreach ($years as $index => $label) {
            DB::table('school_years')->insertOrIgnore([
                'label' => $label,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function seedSchools(): void
    {
        $now = now();

        foreach (['elementary' => config('audit_schools', []), 'secondary' => config('audit_secondary_schools', [])] as $level => $schools) {
            foreach ($schools as $code => $name) {
                DB::table('schools')->insertOrIgnore([
                    'code' => $code,
                    'name' => $name,
                    'school_level' => $level,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function backfillAuditImports(): void
    {
        $yearIds = DB::table('school_years')->pluck('id', 'label');

        foreach (DB::table('audit_imports')->get() as $import) {
            $yearId = $yearIds[$import->school_year] ?? null;
            $level = str_contains(strtolower($import->file_name), 'secondary') ? 'secondary' : 'elementary';

            DB::table('audit_imports')->where('id', $import->id)->update([
                'school_year_id' => $yearId,
                'school_level' => $level,
            ]);
        }
    }

    private function backfillSchoolYearRecords(): void
    {
        if (! Schema::hasColumn('school_grade_audits', 'school_level')) {
            return;
        }

        foreach (DB::table('school_grade_audits')->orderBy('id')->get() as $row) {
            $this->upsertRecordFromGradeAudit($row);
        }
    }

    private function upsertRecordFromGradeAudit(object $row): void
    {
        $import = DB::table('audit_imports')->where('id', $row->audit_import_id)->first();

        if (! $import || ! $import->school_year_id) {
            return;
        }

        $level = $row->school_level ?? $import->school_level ?? 'elementary';
        $school = DB::table('schools')
            ->where('code', $row->school_code)
            ->where('school_level', $level)
            ->first();

        if (! $school) {
            $name = config(($level === 'secondary' ? 'audit_secondary_schools' : 'audit_schools').'.'.$row->school_code, $row->school_code);
            $schoolId = DB::table('schools')->insertGetId([
                'code' => $row->school_code,
                'name' => $name,
                'school_level' => $level,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $school = DB::table('schools')->where('id', $schoolId)->first();
        }

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
            'teacher_surplus' => max(0, $row->surplus),
            'teacher_needs' => max(0, $row->shortage),
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
};

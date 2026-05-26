<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\MultiYearAuditSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\SeedsAuditCatalog;
use Tests\Concerns\SeedsAuditParameters;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    use SeedsAuditCatalog;
    use SeedsAuditParameters;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedAuditParameters();
        $this->seedAuditCatalog();
    }

    public function test_the_application_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_the_login_page_returns_a_successful_response(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
    }

    public function test_school_audit_updates_editable_fields_and_recalculates_computed_columns(): void
    {
        $user = User::factory()->create();
        $importId = $this->insertAuditImport('2025-2026');
        $row = $this->insertGradeAuditRow([
            'audit_import_id' => $importId,
            'school_code' => 'BES',
            'grade_level' => 1,
            'learners' => 100,
            'sections' => 4,
            'class_size' => 25,
            'required_teachers' => 4,
            'available_teachers' => 3,
            'surplus' => 0,
            'shortage' => 1,
        ]);

        $this->actingAs($user)
            ->put(route('schools.update', 'BES'), [
                'school_year' => '2025-2026',
                'school_level' => 'elementary',
                'rows' => [
                    $row['record_id'] => [
                        'learners' => 120,
                        'available_teachers' => 3,
                    ],
                ],
            ])
            ->assertRedirect(route('schools', ['school' => 'BES', 'year' => '2025-2026', 'level' => 'elementary']))
            ->assertSessionHas('status', 'Enrollment saved. Sections, class size, and teacher requirement were recalculated from Parameters.');

        $this->assertDatabaseHas('school_grade_audits', [
            'id' => $row['grade_audit_id'],
            'learners' => 120,
            'sections' => 4,
            'class_size' => 30,
            'required_teachers' => 4,
            'available_teachers' => 3,
            'shortage' => 1,
            'surplus' => 0,
        ]);
    }

    public function test_surplus_is_recalculated_from_teacher_requirement_and_current_teachers(): void
    {
        $user = User::factory()->create();
        $importId = $this->insertAuditImport('2025-2026');
        $row = $this->insertGradeAuditRow([
            'audit_import_id' => $importId,
            'school_code' => 'BES',
            'grade_level' => 4,
            'learners' => 207,
            'sections' => 6,
            'class_size' => 34.5,
            'required_teachers' => 8,
            'available_teachers' => 8,
            'surplus' => 0,
            'shortage' => 0,
        ]);

        $this->actingAs($user)
            ->put(route('schools.update', 'BES'), [
                'school_year' => '2025-2026',
                'school_level' => 'elementary',
                'rows' => [
                    $row['record_id'] => [
                        'learners' => 216,
                        'available_teachers' => 11,
                    ],
                ],
            ])
            ->assertRedirect(route('schools', ['school' => 'BES', 'year' => '2025-2026', 'level' => 'elementary']));

        $this->assertDatabaseHas('school_grade_audits', [
            'id' => $row['grade_audit_id'],
            'sections' => 5,
            'class_size' => 43.2,
            'required_teachers' => 7,
            'available_teachers' => 11,
            'shortage' => 0,
            'surplus' => 4,
        ]);
    }

    public function test_changing_learners_recalculates_kindergarten_teacher_requirement(): void
    {
        $user = User::factory()->create();
        $importId = $this->insertAuditImport('2025-2026');
        $row = $this->insertGradeAuditRow([
            'audit_import_id' => $importId,
            'school_code' => 'BES',
            'grade_level' => 0,
            'learners' => 50,
            'sections' => 2,
            'class_size' => 25,
            'required_teachers' => 1,
            'available_teachers' => 2,
            'surplus' => 1,
            'shortage' => 0,
        ]);

        $this->actingAs($user)
            ->put(route('schools.update', 'BES'), [
                'school_year' => '2025-2026',
                'school_level' => 'elementary',
                'rows' => [
                    $row['record_id'] => [
                        'learners' => 73,
                        'available_teachers' => 2,
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('school_grade_audits', [
            'id' => $row['grade_audit_id'],
            'learners' => 73,
            'sections' => 3,
            'required_teachers' => 2,
            'class_size' => 24.33,
        ]);
    }

    public function test_school_audit_shows_rows_for_seeded_school_year(): void
    {
        $user = User::factory()->create();
        $importId = $this->insertAuditImport('2025-2026');
        $this->insertGradeAuditRow([
            'audit_import_id' => $importId,
            'school_code' => 'BES',
            'grade_level' => 1,
            'learners' => 100,
            'sections' => 4,
            'class_size' => 25,
            'required_teachers' => 4,
            'available_teachers' => 3,
            'surplus' => 0,
            'shortage' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('schools', ['school' => 'BES', 'year' => '2025-2026']))
            ->assertOk()
            ->assertSee('Grade 1')
            ->assertSee('SY 2025-2026');
    }

    public function test_school_audit_shows_same_layout_for_another_seeded_year(): void
    {
        $user = User::factory()->create();
        $importId = $this->insertAuditImport('2025-2026');
        $this->insertGradeAuditRow([
            'audit_import_id' => $importId,
            'school_code' => 'KMES',
            'grade_level' => 1,
            'learners' => 244,
            'sections' => 8,
            'class_size' => 30.5,
            'required_teachers' => 5,
            'available_teachers' => 5,
            'surplus' => 0,
            'shortage' => 0,
        ]);

        app(MultiYearAuditSeeder::class)->seedMissingYears();

        $this->actingAs($user)
            ->get(route('schools', ['school' => 'KMES', 'year' => '2026-2027']))
            ->assertOk()
            ->assertSee('Kapitan Moy Elementary School (KMES)')
            ->assertSee('Grade 1')
            ->assertSee('SY 2026-2027')
            ->assertSee('Save Changes')
            ->assertDontSee('No audit data for SY 2026-2027 yet.');

        $placeholderRow = DB::table('school_grade_audits')
            ->join('audit_imports', 'school_grade_audits.audit_import_id', '=', 'audit_imports.id')
            ->where('audit_imports.school_year', '2026-2027')
            ->where('school_grade_audits.school_code', 'KMES')
            ->where('school_grade_audits.grade_level', 1)
            ->first();

        $this->assertNotNull($placeholderRow);
        $this->assertSame(0, (int) $placeholderRow->learners);
        $this->assertSame(0, (int) $placeholderRow->shortage);
    }

    public function test_dashboard_shows_data_for_selected_school_year(): void
    {
        $user = User::factory()->create();
        $importId = $this->insertAuditImport('2025-2026');
        $this->insertGradeAuditRow([
            'audit_import_id' => $importId,
            'school_code' => 'BES',
            'grade_level' => 1,
            'learners' => 500,
            'sections' => 10,
            'class_size' => 50,
            'required_teachers' => 10,
            'available_teachers' => 8,
            'surplus' => 0,
            'shortage' => 2,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard', ['year' => '2025-2026']))
            ->assertOk()
            ->assertSee('SY 2025-2026')
            ->assertSee('500')
            ->assertSee('Barangka Elementary School');
    }

    public function test_dashboard_shows_zeros_for_placeholder_school_year(): void
    {
        $user = User::factory()->create();
        $importId = $this->insertAuditImport('2025-2026');
        $this->insertGradeAuditRow([
            'audit_import_id' => $importId,
            'school_code' => 'BES',
            'grade_level' => 1,
            'learners' => 500,
            'sections' => 10,
            'class_size' => 50,
            'required_teachers' => 10,
            'available_teachers' => 8,
            'surplus' => 0,
            'shortage' => 2,
        ]);

        app(MultiYearAuditSeeder::class)->syncPlaceholderYears();

        $this->actingAs($user)
            ->get(route('dashboard', ['year' => '2026-2027', 'level' => 'elementary']))
            ->assertOk()
            ->assertSee('SY 2026-2027')
            ->assertDontSee('>500<', false)
            ->assertSee('Barangka Elementary School');
    }

    public function test_secondary_dashboard_shows_data_for_selected_school_year(): void
    {
        $user = User::factory()->create();
        $importId = $this->insertAuditImport(
            '2025-2026',
            'Secondary School Teacher Audit-SY-2025-2026.xlsx',
            'secondary'
        );
        $this->insertGradeAuditRow([
            'audit_import_id' => $importId,
            'school_code' => 'BNHS',
            'school_level' => 'secondary',
            'grade_level' => 7,
            'learners' => 160,
            'sections' => 4,
            'class_size' => 40,
            'required_teachers' => 5,
            'available_teachers' => 4,
            'surplus' => 0,
            'shortage' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard', ['year' => '2025-2026', 'level' => 'secondary']))
            ->assertOk()
            ->assertSee('Secondary Teacher Audit')
            ->assertSee('Barangka National High School');
    }

    public function test_secondary_school_audit_recalculates_from_enrollment(): void
    {
        $user = User::factory()->create();
        $importId = $this->insertAuditImport(
            '2025-2026',
            'Secondary School Teacher Audit-SY-2025-2026.xlsx',
            'secondary'
        );
        $row = $this->insertGradeAuditRow([
            'audit_import_id' => $importId,
            'school_code' => 'BNHS',
            'school_level' => 'secondary',
            'grade_level' => 7,
            'learners' => 100,
            'sections' => 3,
            'class_size' => 33.33,
            'required_teachers' => 4,
            'available_teachers' => 4,
            'surplus' => 0,
            'shortage' => 0,
        ]);

        $this->actingAs($user)
            ->put(route('schools.update', 'BNHS'), [
                'school_year' => '2025-2026',
                'school_level' => 'secondary',
                'rows' => [
                    $row['record_id'] => [
                        'learners' => 160,
                        'available_teachers' => 4,
                    ],
                ],
            ])
            ->assertRedirect(route('schools', ['school' => 'BNHS', 'year' => '2025-2026', 'level' => 'secondary']));

        $this->assertDatabaseHas('school_grade_audits', [
            'id' => $row['grade_audit_id'],
            'school_level' => 'secondary',
            'sections' => 4,
            'class_size' => 40,
            'required_teachers' => 5,
            'shortage' => 1,
        ]);
    }
}

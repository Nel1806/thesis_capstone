<?php

namespace Tests\Unit;

use App\Services\TeacherRequirementCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsAuditParameters;
use Tests\TestCase;

class TeacherRequirementCalculatorTest extends TestCase
{
    use RefreshDatabase;
    use SeedsAuditParameters;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedAuditParameters();
    }

    public function test_kindergarten_calculation(): void
    {
        $result = app(TeacherRequirementCalculator::class)->calculate(0, 73);

        $this->assertSame(3, $result['sections']);
        $this->assertSame(2, $result['required_teachers']);
        $this->assertSame(24.33, $result['class_size']);
    }

    public function test_grade_four_calculation(): void
    {
        $result = app(TeacherRequirementCalculator::class)->calculate(4, 320);

        $this->assertSame(8, $result['sections']);
        $this->assertSame(10, $result['required_teachers']);
        $this->assertSame(40.0, $result['class_size']);
    }

    public function test_zero_learners_returns_zero_requirement(): void
    {
        $result = app(TeacherRequirementCalculator::class)->calculate(1, 0);

        $this->assertSame(1, $result['sections']);
        $this->assertSame(0, $result['required_teachers']);
        $this->assertSame(0.0, $result['class_size']);
    }

    public function test_grade_seven_calculation(): void
    {
        $result = app(TeacherRequirementCalculator::class)->calculate(7, 160);

        $this->assertSame(4, $result['sections']);
        $this->assertSame(5, $result['required_teachers']);
        $this->assertSame(40.0, $result['class_size']);
    }
}

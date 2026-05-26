<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_grade_audits', function (Blueprint $table) {
            $table->string('school_level')->default('elementary')->after('school_code');
            $table->index('school_level');
            $table->index(['audit_import_id', 'school_code', 'grade_level', 'school_level'], 'sga_import_school_grade_level_idx');
        });

        DB::table('school_grade_audits')->update(['school_level' => 'elementary']);
    }

    public function down(): void
    {
        Schema::table('school_grade_audits', function (Blueprint $table) {
            $table->dropIndex('sga_import_school_grade_level_idx');
            $table->dropIndex(['school_level']);
            $table->dropColumn('school_level');
        });
    }
};

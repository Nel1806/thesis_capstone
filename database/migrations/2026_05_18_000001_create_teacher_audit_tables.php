<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_imports', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('school_year')->default('2025-2026');
            $table->unsignedInteger('sheet_count')->default(0);
            $table->unsignedInteger('row_count')->default(0);
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_import_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('position');
            $table->timestamps();
        });

        Schema::create('audit_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_sheet_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->json('cells');
            $table->timestamps();
        });

        Schema::create('school_grade_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_import_id')->constrained()->cascadeOnDelete();
            $table->string('school_code');
            $table->unsignedTinyInteger('grade_level');
            $table->unsignedInteger('learners')->default(0);
            $table->unsignedInteger('sections')->default(0);
            $table->decimal('class_size', 8, 2)->default(0);
            $table->unsignedInteger('required_teachers')->default(0);
            $table->unsignedInteger('available_teachers')->default(0);
            $table->integer('surplus')->default(0);
            $table->integer('shortage')->default(0);
            $table->timestamps();

            $table->index(['school_code', 'grade_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_grade_audits');
        Schema::dropIfExists('audit_rows');
        Schema::dropIfExists('audit_sheets');
        Schema::dropIfExists('audit_imports');
    }
};

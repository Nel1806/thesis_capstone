<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('group_name')->nullable();
            $table->string('level');
            $table->string('minimum')->nullable();
            $table->string('maximum')->nullable();
            $table->string('rounded_half')->nullable();
            $table->string('small_excess')->nullable();
            $table->decimal('teacher_factor', 8, 2)->default(1);
            $table->string('class_organization')->nullable();
            $table->string('teacher_specialization')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_parameters');
    }
};

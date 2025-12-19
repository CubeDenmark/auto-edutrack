<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete(); // Foreign key reference
            $table->string('assessment_name'); // Example: Quiz 1, Midterm Exam, Final Term Project
            $table->string('term'); // Defines the type of assessment
            $table->decimal('weight', 5, 2); // Example: 25.00 (for 25%)
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('assessments');
    }
};


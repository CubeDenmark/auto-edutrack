<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('assessment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->string('name'); // e.g., "Quiz 1", "Quiz 2", "Midterm Exam"
            $table->decimal('score', 5, 2)->nullable(); // The actual score a student gets
            $table->decimal('total_score', 5, 2); // Max possible score
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('assessment_items');
    }
};

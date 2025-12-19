<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinalGradesTable extends Migration
{
    public function up()
    {
        Schema::create('final_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('student_class_id')->constrained('students_classes')->onDelete('cascade');
            $table->decimal('prelim', 5, 2)->nullable();
            $table->decimal('midterm', 5, 2)->nullable();
            $table->decimal('final_term', 5, 2)->nullable();
            $table->decimal('final_grade', 5, 2)->nullable();
            $table->string('final_remarks')->nullable();
            $table->timestamps();
        
            // 👇 SHORTER INDEX NAME
            $table->unique(['student_id', 'student_class_id'], 'final_grade_unique_index');
        });
        
    }

    public function down()
    {
        Schema::dropIfExists('final_grades');
    }
}


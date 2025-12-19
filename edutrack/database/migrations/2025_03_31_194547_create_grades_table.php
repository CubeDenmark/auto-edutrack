<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            // Explicitly defining unsignedBigInteger for foreign keys
            $table->unsignedBigInteger('student_class_id');
            $table->unsignedBigInteger('assessment_id')->nullable();
            $table->unsignedBigInteger('assessment_item_id')->nullable();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->enum('term', ['midterm', 'final_term'])->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();

            // Add foreign key constraints
            $table->foreign('student_class_id')->references('id')->on('students_classes')->onDelete('cascade');
            $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('cascade');
            $table->foreign('assessment_item_id')->references('id')->on('assessment_items')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('grades');
    }
};

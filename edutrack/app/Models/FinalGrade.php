<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalGrade extends Model
{
    use HasFactory;

    protected $table = 'final_grades';

    protected $fillable = [
       'student_id',
        'student_class_id',
        'prelim',
        'midterm',
        'final_term',
        'final_grade',
        'final_remarks'
    ];

    // Optional relationships
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function assessmentItem()
    {
        return $this->belongsTo(AssessmentItem::class);
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class, 'student_class_id');
    }

    public function professor()
    {
        return $this->belongsTo(User::class, 'professor_id'); // or correct foreign key
    }


    

}

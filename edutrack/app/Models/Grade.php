<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = [
        'student_id', 'assessment_item_id', 'student_class_id', 'score', 'remarks', 'total_score'
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function assessmentItem()
    {
        return $this->belongsTo(AssessmentItem::class, 'assessment_item_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class, 'student_class_id'); // Assuming 'student_class_id' is the correct column
    }


    // Previously added
    public function assessment() {
        return $this->belongsTo(Assessment::class, 'assessment_id');
    }

    // Add this relationship
    public function grades() {
        return $this->hasMany(Grade::class, 'assessment_item_id');
    }

}

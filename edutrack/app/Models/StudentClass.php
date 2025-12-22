<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentClass extends Model
{
    use HasFactory;

    protected $table = 'students_classes'; // Ensure the correct table name

    protected $fillable = [
        'student_id',
        'class_id',
    ];

    // Define the relationship to the User model (student)
   // In StudentClass model
public function student()
{
    return $this->belongsTo(User::class, 'student_id'); // A StudentClass belongs to a User using the student_id foreign key
}


    // Define the relationship to the Class model
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    // Define the relationship to the Grade model
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    // Define the relationship to the Attendance model
    public function attendance()
    {
        return $this->hasOne(Attendance::class, 'student_id', 'student_id')
            ->whereColumn('class_id', 'class_id');
    }

    // Custom method to get class_id for a specific student
    public static function getClassIdForStudent($studentId)
    {
        $studentClass = self::where('student_id', $studentId)->first();
        return $studentClass ? $studentClass->class_id : null;
    }

    public function finalGrades()
    {
        return $this->hasMany(FinalGrade::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parents()
{
    return $this->hasMany(ParentStudent::class, 'student_id');
}


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes'; // Ensure your table name is 'classes'

    protected $fillable = [
        'name',
        'icon',
        'term',
        'description',
        'schedule',
        'room',
        'max_students',
        'professor_id',
    ];

    protected $casts = [
        'schedule' => 'array',
    ];

    // Relationships
    public function professor()
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    // public function students()
    // {
    //     return $this->belongsToMany(User::class, 'students_classes', 'class_id', 'student_id');

    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'class_id');
    }
    public function behaviorReports()
    {
        return $this->hasMany(BehaviorReport::class, 'class_id');
    }

    public function students()
    {
        return $this->hasMany(StudentClass::class, 'class_id');
    }

    public function studentUsers()
    {
        return $this->belongsToMany(User::class, 'students_classes', 'class_id', 'student_id');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BehaviorReport extends Model
{
    use HasFactory;

    protected $table = 'behavior_reports';

    protected $fillable = [
        'student_id',
        'class_id',
        'professor_id', // Added professor_id
        'title',         // Added title
        'type',
        'comment',
        'date',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(User::class);
    }

    public function classSection()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    // Relationship to the professor (assuming professors are users)
    public function professor()
    {
        return $this->belongsTo(User::class, 'professor_id');
    }
}

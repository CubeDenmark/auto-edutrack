<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model {
    use HasFactory;

    protected $fillable = ['class_id', 'term', 'weight', 'assessment_name'];

    public function classModel() {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function items() {
        return $this->hasMany(AssessmentItem::class, 'assessment_id');
    }

    public function assessmentItems()
    {
        return $this->hasMany(AssessmentItem::class, 'assessment_id');
    }

}


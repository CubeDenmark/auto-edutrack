<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentItem extends Model {
    use HasFactory;

    protected $fillable = ['assessment_id', 'name', 'score', 'total_score'];

    public function assessment() {
        return $this->belongsTo(Assessment::class, 'assessment_id');
    }
}

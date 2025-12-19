<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentStudent extends Model
{
    use HasFactory;

    protected $table = 'parents_students';

    protected $fillable = [
        'parent_id',
        'student_id',
    ];


    /**
     * Get the parent associated with this relationship.
     */

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    // app/Models/ParentStudent.php
    public function parentUser()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

}


// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class ParentStudent extends Model
// {
//     use HasFactory;

//     // The table associated with the model.
//     protected $table = 'parents_students';

//     // The attributes that are mass assignable.
//     protected $fillable = [
//         'parent_id',
//         'student_id',
//     ];
// }

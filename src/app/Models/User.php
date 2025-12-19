<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable implements MustVerifyEmail
// class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

     // The attributes that are mass assignable.
     protected $fillable = [
        'school_id',
        'name',
        'email',
        'user_type',
        'password',
        'avatar'
    ];

    // Relationships
    // public function classes()
    // {
    //     return $this->hasMany(ClassModel::class, 'professor_id');
    // }

    // public function studentClasses()
    // {
    //     return $this->belongsToMany(ClassModel::class, 'students_classes', 'student_id', 'class_id');
    // }
    // public function studentClasses()
    // {
    //     return $this->hasMany(StudentClass::class, 'student_id');
    // }


    // public function children()
    // {
    //     return $this->belongsToMany(User::class, 'parents_students', 'parent_id', 'student_id');
    // }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

// Get the classes if the user is a professor
public function classes()
{
    return $this->hasMany(ClassModel::class, 'professor_id');
}

// If this user is a student, get the student-class enrollments
public function studentClasses()
{
    return $this->hasMany(StudentClass::class, 'student_id');
}

// If this user is a student, get their section/class (assuming one-to-one)
public function studentClass()
{
    return $this->hasOne(StudentClass::class, 'student_id');
}

// If this user is a parent, get their linked students
// public function students()
// {
//     return $this->belongsToMany(User::class, 'parents_students', 'parent_id', 'student_id')
//                 ->withTimestamps()
//                 ->withPivot('icon'); // optional, if you plan to store additional info
// }

// If this user is a student, get their parent(s)


// Optional: If you want to use the pivot model directly
// app/Models/User.php


public function parentLinks()
{
    return $this->hasMany(ParentStudent::class, 'student_id');
}

// app/Models/User.php



// public function children() // For parents to access their children
// {
//     return $this->belongsToMany(User::class, 'parents_students', 'parent_id', 'student_id');
// }

// public function parents() // For students to access their parents
// {
//     return $this->belongsToMany(User::class, 'parents_students', 'student_id', 'parent_id');
// }

// Optional: Direct pivot model access
public function childrenPivot()
{
    return $this->hasMany(ParentStudent::class, 'parent_id');
}

public function parentsPivot()
{
    return $this->hasMany(ParentStudent::class, 'student_id');
}

public function parents()
{
    return $this->belongsToMany(User::class, 'parents_students', 'student_id', 'parent_id');
}

public function students()
{
    return $this->belongsToMany(User::class, 'parents_students', 'parent_id', 'student_id');
}

public function children()
{
    return $this->belongsToMany(User::class, 'parents_students', 'parent_id', 'student_id');
}

}



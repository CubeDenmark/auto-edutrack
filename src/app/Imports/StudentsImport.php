<?php

namespace App\Imports;

use App\Models\User;
use App\Models\StudentClass;
use App\Models\ParentStudent;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;

class StudentsImport implements ToModel
{
    protected $classId;

    public function __construct($classId)
    {
        $this->classId = $classId;
    }

    public function model(array $row)
    {
        // Skip header row (assuming first col header is 'parent_email')
        if ($row[0] === 'parent_email') {
            return null;
        }

       // 1. Create or update parent user
        $parent = User::firstOrNew(['email' => $row[0]]);
        $parent->name = $row[1];
        $parent->user_type = 'parent';

        if (!$parent->exists) {
            $parent->password = Hash::make('default123');
        }

        $parent->save();


        // 2. Create or update student user
        $student = User::firstOrNew(['email' => $row[2]]);
        $student->name = $row[3];
        $student->user_type = 'student';

        if (!$student->exists) {
            $student->password = Hash::make('default123');
        }

        $student->save();


        // 3. Enroll student to the selected class (avoid duplicates)
        StudentClass::updateOrCreate([
            'class_id' => $this->classId,
            'student_id' => $student->id,
        ]);


        // 4. Connect parent and student
        ParentStudent::updateOrCreate(
            ['parent_id' => $parent->id, 'student_id' => $student->id],
            ['icon' => $row[5] ?? null]
        );

        // Return null because we're handling multiple models here
        return null;
    }
}

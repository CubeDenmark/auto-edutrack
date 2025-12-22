<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentClass;
use App\Models\ClassModel;


class NavStudentController extends Controller
{

    public function index()
    {
        $studentId = Auth::id(); // Get the logged-in student's ID

    // Step 1: Get the classes the student is enrolled in
    $studentClasses = StudentClass::where('student_id', $studentId)->pluck('class_id');

    // Step 2: Fetch the actual classes
    $classes = ClassModel::whereIn('id', $studentClasses)->get();


    return response()->json([
        'classes' => $classes,
    ]);
    }

}

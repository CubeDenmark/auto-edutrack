<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ParentStudent; // Model for the parents_students table
use App\Models\User; // Model for the User table

class NavParentController extends Controller
{
    // public function index()
    // {
    //     $parentId = Auth::id(); // Get the logged-in parent ID

    //     // Step 1: Fetch the student IDs connected to this parent
    //     $parentStudents = ParentStudent::where('parent_id', $parentId)->get();

    //     // Step 2: Extract the student IDs
    //     $studentIds = $parentStudents->pluck('student_id');

    //     // Step 3: Fetch the student user details from the users table
    //     $students = User::whereIn('id', $studentIds)->get();

    //     // Step 4: Format the response to include the student's name, icon, etc.
    //     $studentsData = $students->map(function ($student) use ($parentStudents) {
    //         $parentStudent = $parentStudents->firstWhere('student_id', $student->id);

    //         return [
    //             'id' => $student->id,
    //             'name' => $student->name, // You can adjust this based on your structure
    //             'icon' => $parentStudent->icon ?? '👧👦', // Use the icon from the pivot table, or a default if not set
    //         ];
    //     });

    //     return response()->json([
    //         'children' => $studentsData,
    //     ]);
    // }

    public function index()
    {
        $parentId = Auth::id(); // Get the logged-in parent ID

        // Step 1: Fetch the student IDs connected to this parent
        $parentStudents = ParentStudent::where('parent_id', $parentId)->get();

        // Step 2: Extract the student IDs
        $studentIds = $parentStudents->pluck('student_id');

        // Step 3: Fetch the student user details from the users table
        $students = User::whereIn('id', $studentIds)->get();

        // Step 4: Format the response to include the student's name and avatar
        $studentsData = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'avatar' => $student->avatar ?? null, // Assuming `avatar` is a column in your users table
            ];
        });

        return response()->json([
            'children' => $studentsData,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\ParentStudent;
use App\Models\BehaviorReport;
use App\Models\Attendance;
use App\Models\FinalGrade;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentDashboardController extends Controller
{
    /**
     * Display the parent dashboard
     */
    // public function index(Request $request)
    // {
    //     // Get the authenticated parent user
    //     $parent = Auth::user();

    //     // Ensure the user is a parent
    //     if ($parent->user_type !== 'parent') {
    //         abort(403, 'Only parents can access this page');
    //     }

    //     // Simplify the controller to fetch only the required data
    //     $children = ParentStudent::where('parent_id', $request->user()->id)
    //         ->with(['student.studentClass.class', 'student.studentClass.finalGrades', 'student.studentClass.attendance'])
    //         ->get()
    //         ->map(function ($relation) {
    //             $student = $relation->student;
    //             $class = $student->studentClass->class ?? null;
    //             $attendance = $student->studentClass->attendance ?? null;
    //             $finalGrades = $student->studentClass->finalGrades ?? [];

    //             $attendanceSummary = [
    //                 'present' => $attendance->present ?? 0,
    //                 'absent' => $attendance->absent ?? 0,
    //                 'late' => $attendance->late ?? 0,
    //                 'total' => 40, // Assuming 40 total days
    //             ];

    //             $presentDays = round(($attendanceSummary['present'] / 100) * $attendanceSummary['total']);
    //             $absentDays = round(($attendanceSummary['absent'] / 100) * $attendanceSummary['total']);
    //             $lateDays = round(($attendanceSummary['late'] / 100) * $attendanceSummary['total']);

    //             return [
    //                 'id' => $student->id,
    //                 'name' => $student->name,
    //                 'grade' => $class->term ?? 'N/A',
    //                 'avatar' => 'https://i.pravatar.cc/150?img=' . ($student->id % 10 + 1),
    //                 'alerts' => BehaviorReport::where('student_id', $student->id)->count(),
    //                 'stats' => [
    //                     'gpa' => $finalGrades->avg('final_grade') ?? 'N/A',
    //                     'attendance' => $attendanceSummary['present'],
    //                     'behavior' => 'Good', // Placeholder
    //                 ],
    //                 'recentActivity' => 'No recent activity', // Placeholder
    //                 'attendanceSummary' => [
    //                     'present' => $presentDays,
    //                     'absent' => $absentDays,
    //                     'late' => $lateDays,
    //                     'total' => $attendanceSummary['total'],
    //                 ],
    //                 'academicPerformance' => [], // Placeholder for academic performance
    //             ];
    //         });

    //     return Inertia::render('Parent/ParentDashboard', [
    //         'childrenSections' => $children,
    //     ]);
    // }

//     public function index(Request $request)
// {
//     // Get the authenticated parent user
//     $parent = Auth::user();

//     // Ensure the user is a parent
//     if ($parent->user_type !== 'parent') {
//         abort(403, 'Only parents can access this page');
//     }

//     // Simplify the controller to fetch only the required data
//     $children = ParentStudent::where('parent_id', $request->user()->id)
//         ->with(['student.studentClass.class', 'student.studentClass.finalGrades', 'student.studentClass.attendance'])
//         ->get()
//         ->map(function ($relation) {
//             $student = $relation->student;
//             $class = $student->studentClass->class ?? null;
//             $attendance = $student->studentClass->attendance ?? null;
            
//             // Get all final grades for the student from the FinalGrade model
//             $finalGrades = FinalGrade::where('student_id', $student->id)->get();

//             // Calculate GPA by summing all final grades and dividing by the count
//             $totalGrades = $finalGrades->sum('final_grade');
//             $gpa = $finalGrades->count() > 0 ? $totalGrades / $finalGrades->count() : 'N/A';

//             // Attendance calculation
//             $attendanceSummary = [
//                 'present' => $attendance->present ?? 0,
//                 'absent' => $attendance->absent ?? 0,
//                 'late' => $attendance->late ?? 0,
//                 'total' => 40, // Assuming 40 total days
//             ];

//             $presentDays = round(($attendanceSummary['present'] / 100) * $attendanceSummary['total']);
//             $absentDays = round(($attendanceSummary['absent'] / 100) * $attendanceSummary['total']);
//             $lateDays = round(($attendanceSummary['late'] / 100) * $attendanceSummary['total']);

//             return [
//                 'id' => $student->id,
//                 'name' => $student->name,
//                 'grade' => $class->term ?? 'N/A',
//                 'avatar' => 'https://i.pravatar.cc/150?img=' . ($student->id % 10 + 1),
//                 'alerts' => BehaviorReport::where('student_id', $student->id)->count(),
//                 'stats' => [
//                     'gpa' => $gpa,
//                     'attendance' => $attendanceSummary['present'],
//                     'behavior' => 'Good', // Placeholder
//                 ],
//                 'recentActivity' => 'No recent activity', // Placeholder
//                 'attendanceSummary' => [
//                     'present' => $presentDays,
//                     'absent' => $absentDays,
//                     'late' => $lateDays,
//                     'total' => $attendanceSummary['total'],
//                 ],
//                 'academicPerformance' => [], // Placeholder for academic performance
//             ];
//         });

//     return Inertia::render('Parent/ParentDashboard', [
//         'childrenSections' => $children,
//     ]);
// }

public function index(Request $request)
{
    // Get the authenticated parent user
    $parent = Auth::user();

    // Ensure the user is a parent
    if ($parent->user_type !== 'parent') {
        abort(403, 'Only parents can access this page');
    }

    // Fetch all children associated with the parent
    $children = ParentStudent::where('parent_id', $request->user()->id)
        ->with(['student.studentClass.class', 'student.studentClass.finalGrades'])
        ->get()
        ->map(function ($relation) {
            $student = $relation->student;
            $class = $student->studentClass->class ?? null;

            // Get all final grades for the student
            $finalGrades = FinalGrade::where('student_id', $student->id)->get();
            $totalGrades = $finalGrades->sum('final_grade');
            $gpa = $finalGrades->count() > 0 ? $totalGrades / $finalGrades->count() : 'N/A';

            // Get all class IDs the student is enrolled in
            $classIds = StudentClass::where('student_id', $student->id)->pluck('class_id');

            // Get all attendance records across all classes for this student
            $attendanceRecords = Attendance::where('student_id', $student->id)
                ->whereIn('class_id', $classIds)
                ->get();

            // Count attendance status types
            $totalDays = $attendanceRecords->count();
            $presentDays = $attendanceRecords->where('status', 'present')->count();
            $absentDays = $attendanceRecords->where('status', 'absent')->count();
            $lateDays = $attendanceRecords->where('status', 'late')->count();

            $attendanceSummary = [
                'present' => $presentDays,
                'absent' => $absentDays,
                'late' => $lateDays,
                'total' => $totalDays,
            ];

            return [
                'id' => $student->id,
                'name' => $student->name,
                'grade' => $class->term ?? 'N/A',
                'avatar' => $student->avatar,
                'alerts' => BehaviorReport::where('student_id', $student->id)->count(),
                'stats' => [
                    'gpa' => $gpa,
                    'attendance' => $presentDays,
                    'behavior' => 'Good', // Placeholder
                ],
                'recentActivity' => 'No recent activity', // Placeholder
                'attendanceSummary' => $attendanceSummary,
                'academicPerformance' => [], // Placeholder
            ];
        });

    return Inertia::render('Parent/ParentDashboard', [
        'childrenSections' => $children,
    ]);
}



}

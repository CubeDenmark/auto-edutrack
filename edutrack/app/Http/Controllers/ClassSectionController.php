<?php
namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Attendance;
use App\Models\StudentClass; // Pivot model
use App\Models\User; // Use the User model
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ClassSectionController extends Controller
{
    // public function index()
    // {
    //     // Get the authenticated professor
    //     $professorId = Auth::id();

    //     // Fetch the professor's sections
    //     $sections = ClassModel::where('professor_id', $professorId)
    //         ->select('id', 'name', 'icon', 'term')
    //         ->get();

    //     // Fetch the students for each section
    //     $sections->each(function ($section) {
    //         $students = StudentClass::where('class_id', $section->id)
    //         ->with(['student' => function ($query) {
    //             $query->where('user_type', 'student');
    //         }, 'attendance'])
    //         ->get()
    //         ->map(function ($studentClass) {
    //             $user = $studentClass->student;

    //             if ($user) {
    //                 $attendance = $studentClass->attendance;

    //                 return [
    //                     'id' => $user->id,
    //                     'name' => $user->name,
    //                     'email' => $user->email,
    //                     'status' => $attendance ? $attendance->status : 'absent',
    //                     'notes' => $attendance ? $attendance->notes : '',
    //                 ];
    //             }

    //             return null;
    //         })
    //         ->filter()
    //         ->values();

    //         $section->setAttribute('students', $students);

    //         // Calculate attendance summary for each section
    //         $attendanceSummary = [
    //             'present' => $section->students->where('status', 'present')->count(),
    //             'absent' => $section->students->where('status', 'absent')->count(),
    //             'late' => $section->students->where('status', 'late')->count(),
    //             'excused' => $section->students->where('status', 'excused')->count(),
    //         ];

    //         $section->attendanceSummary = $attendanceSummary;
    //         $section->studentCount = $section->students->count();
    //     });

    //     return Inertia::render('Professor/Attendance', [
    //         'sections' => $sections,
    //     ]);
    // }

    public function index()
{
    // Get the authenticated professor
    $professorId = Auth::id();

    // Fetch the professor's sections
    $sections = ClassModel::where('professor_id', $professorId)
        ->select('id', 'name', 'icon', 'term')
        ->get();

    // Fetch the students and their attendance for each section
    $sections->each(function ($section) {
        $students = StudentClass::where('class_id', $section->id)
            ->with(['student' => function ($query) {
                $query->where('user_type', 'student');
            }])
            ->get()
            ->map(function ($studentClass) use ($section) {
                $user = $studentClass->student;

                if ($user) {
                    // Fetch all attendance records for this student in this class
                    $attendanceHistory = Attendance::where('student_id', $user->id)
                        ->where('class_id', $section->id)
                        ->orderBy('date', 'desc')
                        ->get();

                    // Get the latest attendance record (if any)
                    $latest = $attendanceHistory->first();

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'avatar' => $user->avatar,
                        'school_id' => $user->school_id,
                        'email' => $user->email,
                        'status' => $latest ? $latest->status : 'absent',
                        'notes' => $latest ? $latest->notes : '',
                        'attendance_history' => $attendanceHistory->map(function ($record) {
                            return [
                                'status' => $record->status,
                                'date' => $record->date,
                                'notes' => $record->notes,
                            ];
                        }),
                    ];
                }

                return null;
            })
            ->filter()
            ->values();

        // Attach students to the section
        $section->setAttribute('students', $students);

        // Calculate attendance summary
        $attendanceSummary = [
            'present' => $section->students->where('status', 'present')->count(),
            'absent' => $section->students->where('status', 'absent')->count(),
            'late' => $section->students->where('status', 'late')->count(),
            'excused' => $section->students->where('status', 'excused')->count(),
        ];

        $section->attendanceSummary = $attendanceSummary;
        $section->studentCount = $section->students->count();
    });

    return Inertia::render('Professor/Attendance', [
        'sections' => $sections,
    ]);
}



    public function saveAttendance(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id',
            'status' => 'required|in:present,absent,late,excused',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        // Match only by unique attendance keys: student_id, class_id, and date
        $attendance = Attendance::updateOrCreate(
            [
                'student_id' => $validated['student_id'],
                'class_id' => $validated['class_id'],
                'date' => $validated['date'],
            ],
            [
                'status' => $validated['status'],
                'notes' => $validated['notes'],
            ]
        );

        return response()->json([
            'message' => 'Attendance saved successfully.',
            'attendance' => $attendance,
        ]);
    }


}

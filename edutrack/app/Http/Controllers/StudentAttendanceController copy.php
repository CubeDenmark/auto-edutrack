<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassModel;
use App\Models\StudentClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StudentAttendanceController extends Controller
{
    /**
     * Display attendance records for a student
     */
    public function index(Request $request)
    {
        $student = Auth::user();
        $classId = $request->query('classId');

        // Get all classes the student is enrolled in
        $classes = $student->studentClasses()
        ->with(['class' => function ($query) {
            $query->with('professor');
        }])
        ->get()
        ->map(function ($studentClass) {
            $class = $studentClass->class;
            return [
                'id' => $class->id,
                'name' => $class->name,
                'icon' => $class->icon ?? '📚',
                'teacher' => $class->professor->name,
                'room' => $class->room,
                'time' => $class->schedule ? json_encode($class->schedule) : null,
'days' => $class->schedule ? array_map(function ($schedule) {
    // Ensure $schedule is a string and safely handle cases where it's not
    return is_string($schedule) ? explode(' ', $schedule)[0] : null;
}, is_array($class->schedule) ? $class->schedule : (is_string($class->schedule) ? json_decode($class->schedule, true) : [])) : [],

                'attendanceSummary' => $this->getAttendanceSummary($studentClass->student->id, $class->id)
            ];
        });

        // Get attendance records for the student
        $attendanceQuery = Attendance::where('student_id', $student->id);

        if ($classId) {
            $attendanceQuery->where('class_id', $classId);
        }

        $attendanceRecords = $attendanceQuery->with(['class', 'class.professor'])
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy('class_id')
            ->map(function($records) {
                return $records->map(function($record) {
                    $dayOfWeek = date('l', strtotime($record->date));
                    return [
                        'id' => $record->id,
                        'date' => $record->date,
                        'day' => $dayOfWeek,
                        'status' => $record->status,
                        'time' => $record->created_at ? date('h:i A', strtotime($record->created_at)) : null,
                        'notes' => $record->notes ?? '',
                        'class' => $record->class->name,
                        'teacher' => $record->class->professor->name
                    ];
                });
            });

        // Calculate overall attendance summary
        $overallSummary = $this->getOverallAttendanceSummary($student->id);

        return Inertia::render('Student/StudentAttendance', [
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'grade' => 'Grade ' . rand(9, 12), // This should come from your actual data
                'avatar' => 'https://i.pravatar.cc/150?img=3', // This should be dynamic
                'studentId' => 'S' . $student->id,
                'attendanceSummary' => $overallSummary
            ],
            'classes' => $classes,
            'attendanceRecordsByClass' => $attendanceRecords,
            'attendancePolicies' => $this->getAttendancePolicies()
        ]);
    }

    /**
     * Display attendance management for a professor
     */
    // public function professorAttendance(Request $request)
    // {
    //     $professor = Auth::user();
    //     $classId = $request->query('classId');

    //     // Get all classes taught by the professor
    //     $classes = $professor->classes()
    //         ->with(['students.student'])
    //         ->get()
    //         ->map(function($class) {
    //             return [
    //                 'id' => $class->id,
    //                 'name' => $class->name,
    //                 'icon' => $class->icon ?? '📚',
    //                 'term' => $class->term,
    //                 'room' => $class->room,
    //                 'schedule' => $class->schedule,
    //                 'studentCount' => $class->students->count(),
    //                 'students' => $class->students->map(function($studentClass) {
    //                     return [
    //                         'id' => $studentClass->student->id,
    //                         'name' => $studentClass->student->name,
    //                         'email' => $studentClass->student->email,
    //                         'attendanceSummary' => $this->getAttendanceSummary($studentClass->student_id, $studentClass->class_id)
    //                     ];
    //                 }),
    //                 'attendanceSummary' => $this->getClassAttendanceSummary($class->id)
    //             ];
    //         });

    //     // If a specific class is selected, get detailed attendance data
    //     $selectedClass = null;
    //     $attendanceData = null;

    //     if ($classId) {
    //         $selectedClass = $classes->firstWhere('id', $classId);

    //         if ($selectedClass) {
    //             $attendanceData = $this->getClassAttendanceData($classId);
    //         }
    //     }

    //     return Inertia::render('ProfessorAttendance', [
    //         'classes' => $classes,
    //         'selectedClass' => $selectedClass,
    //         'attendanceData' => $attendanceData
    //     ]);
    // }

    // /**
    //  * Display attendance for a parent's children
    //  */
    // public function parentAttendance(Request $request)
    // {
    //     $parent = Auth::user();
    //     $childId = $request->query('childId');

    //     // Get all children of the parent
    //     $children = $parent->children()
    //         ->get()
    //         ->map(function($child) {
    //             return [
    //                 'id' => $child->id,
    //                 'name' => $child->name,
    //                 'email' => $child->email,
    //                 'avatar' => null, // This should be dynamic
    //                 'icon' => '👧👦',
    //                 'attendanceSummary' => $this->getOverallAttendanceSummary($child->id)
    //             ];
    //         });

    //     // If a specific child is selected, get their classes and attendance
    //     $selectedChild = null;
    //     $childClasses = null;
    //     $attendanceRecords = null;

    //     if ($childId) {
    //         $selectedChild = $children->firstWhere('id', $childId);

    //         if ($selectedChild) {
    //             $child = User::find($childId);

    //             $childClasses = $child->studentClasses()
    //                 ->with(['class' => function($query) {
    //                     $query->with('professor');
    //                 }])
    //                 ->get()
    //                 ->map(function($studentClass) use ($childId) {
    //                     $class = $studentClass->class;
    //                     return [
    //                         'id' => $class->id,
    //                         'name' => $class->name,
    //                         'icon' => $class->icon ?? '📚',
    //                         'teacher' => $class->professor->name,
    //                         'room' => $class->room,
    //                         'time' => $class->schedule ? json_encode($class->schedule) : null,
    //                         'days' => $class->schedule ? array_map(function($schedule) {
    //                             return explode(' ', $schedule)[0]; // Extract day from schedule
    //                         }, $class->schedule) : [],
    //                         'attendanceSummary' => $this->getAttendanceSummary($childId, $class->id)
    //                     ];
    //                 });

    //             $attendanceRecords = Attendance::where('student_id', $childId)
    //                 ->with(['class', 'class.professor'])
    //                 ->orderBy('date', 'desc')
    //                 ->get()
    //                 ->groupBy('class_id')
    //                 ->map(function($records) {
    //                     return $records->map(function($record) {
    //                         $dayOfWeek = date('l', strtotime($record->date));
    //                         return [
    //                             'id' => $record->id,
    //                             'date' => $record->date,
    //                             'day' => $dayOfWeek,
    //                             'status' => $record->status,
    //                             'time' => $record->created_at ? date('h:i A', strtotime($record->created_at)) : null,
    //                             'notes' => $record->notes ?? '',
    //                             'class' => $record->class->name,
    //                             'teacher' => $record->class->professor->name
    //                         ];
    //                     });
    //                 });
    //         }
    //     }

    //     return Inertia::render('ParentAttendance', [
    //         'children' => $children,
    //         'selectedChild' => $selectedChild,
    //         'childClasses' => $childClasses,
    //         'attendanceRecordsByClass' => $attendanceRecords,
    //         'attendancePolicies' => $this->getAttendancePolicies()
    //     ]);
    // }

    /**
     * Store a new attendance record
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id',
            'status' => 'required|in:present,absent,late,excused',
            'date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        // Check if an attendance record already exists for this student, class, and date
        $existingRecord = Attendance::where('student_id', $validated['student_id'])
            ->where('class_id', $validated['class_id'])
            ->where('date', $validated['date'])
            ->first();

        if ($existingRecord) {
            // Update existing record
            $existingRecord->update($validated);
            $message = 'Attendance record updated successfully';
        } else {
            // Create new record
            Attendance::create($validated);
            $message = 'Attendance record created successfully';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update multiple attendance records at once (for a class)
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'records' => 'required|array',
            'records.*.student_id' => 'required|exists:users,id',
            'records.*.status' => 'required|in:present,absent,late,excused',
            'records.*.notes' => 'nullable|string'
        ]);

        $classId = $validated['class_id'];
        $date = $validated['date'];

        foreach ($validated['records'] as $record) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $record['student_id'],
                    'class_id' => $classId,
                    'date' => $date
                ],
                [
                    'status' => $record['status'],
                    'notes' => $record['notes'] ?? null
                ]
            );
        }

        return redirect()->back()->with('success', 'Attendance records updated successfully');
    }

    /**
     * Calculate attendance summary for a student in a specific class
     */
    private function getAttendanceSummary($studentId, $classId)
    {
        $records = Attendance::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->get();

        $total = $records->count();

        if ($total === 0) {
            return [
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'excused' => 0
            ];
        }

        $present = $records->where('status', 'present')->count();
        $absent = $records->where('status', 'absent')->count();
        $late = $records->where('status', 'late')->count();
        $excused = $records->where('status', 'excused')->count();

        return [
            'present' => round(($present / $total) * 100),
            'absent' => round(($absent / $total) * 100),
            'late' => round(($late / $total) * 100),
            'excused' => round(($excused / $total) * 100)
        ];
    }

    /**
     * Calculate overall attendance summary for a student across all classes
     */
    private function getOverallAttendanceSummary($studentId)
    {
        $records = Attendance::where('student_id', $studentId)->get();

        $total = $records->count();

        if ($total === 0) {
            return [
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'excused' => 0
            ];
        }

        $present = $records->where('status', 'present')->count();
        $absent = $records->where('status', 'absent')->count();
        $late = $records->where('status', 'late')->count();
        $excused = $records->where('status', 'excused')->count();

        return [
            'present' => round(($present / $total) * 100),
            'absent' => round(($absent / $total) * 100),
            'late' => round(($late / $total) * 100),
            'excused' => round(($excused / $total) * 100)
        ];
    }

    /**
     * Calculate attendance summary for an entire class
     */
    private function getClassAttendanceSummary($classId)
    {
        $records = Attendance::where('class_id', $classId)->get();

        $total = $records->count();

        if ($total === 0) {
            return [
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'excused' => 0
            ];
        }

        $present = $records->where('status', 'present')->count();
        $absent = $records->where('status', 'absent')->count();
        $late = $records->where('status', 'late')->count();
        $excused = $records->where('status', 'excused')->count();

        return [
            'present' => round(($present / $total) * 100),
            'absent' => round(($absent / $total) * 100),
            'late' => round(($late / $total) * 100),
            'excused' => round(($excused / $total) * 100)
        ];
    }

    /**
     * Get detailed attendance data for a class
     */
    private function getClassAttendanceData($classId)
    {
        $class = ClassModel::with(['students.student'])->findOrFail($classId);

        $students = $class->students->map(function($studentClass) use ($classId) {
            $student = $studentClass->student;

            $attendanceRecords = Attendance::where('student_id', $student->id)
                ->where('class_id', $classId)
                ->orderBy('date', 'desc')
                ->get()
                ->map(function($record) {
                    $dayOfWeek = date('l', strtotime($record->date));
                    return [
                        'id' => $record->id,
                        'date' => $record->date,
                        'day' => $dayOfWeek,
                        'status' => $record->status,
                        'notes' => $record->notes ?? ''
                    ];
                });

            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'attendanceSummary' => $this->getAttendanceSummary($student->id, $classId),
                'attendanceRecords' => $attendanceRecords
            ];
        });

        return [
            'students' => $students,
            'dates' => $this->getClassDates($classId)
        ];
    }

    /**
     * Get all dates for which attendance was recorded for a class
     */
    private function getClassDates($classId)
    {
        return Attendance::where('class_id', $classId)
            ->orderBy('date', 'desc')
            ->pluck('date')
            ->unique()
            ->values();
    }

    /**
     * Get attendance policies
     */
    private function getAttendancePolicies()
    {
        return [
            [
                'title' => 'Reporting Absences',
                'description' => 'Students must notify the teacher before class if they will be absent. For unexpected absences, notification should be made as soon as possible.'
            ],
            [
                'title' => 'Excused Absences',
                'description' => 'Absences may be excused for illness, medical appointments, religious observances, family emergencies, or pre-approved educational opportunities. Documentation may be required.'
            ],
            [
                'title' => 'Tardiness',
                'description' => 'Students are expected to be in their classrooms at the scheduled start time. Students arriving after this time will be marked tardy.'
            ],
            [
                'title' => 'Consequences',
                'description' => 'Excessive unexcused absences or tardiness may result in academic penalties, parent conferences, detention, or referral to academic advisors.'
            ],
            [
                'title' => 'Make-up Work',
                'description' => 'Students are responsible for requesting and completing all missed assignments. For excused absences, students have the same number of days as they were absent to make up work.'
            ]
        ];
    }
}

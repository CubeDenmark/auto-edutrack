<?php
namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Attendance;
use App\Models\Assessment;
use App\Models\AssessmentItem;
use App\Models\FinalGrade;
use App\Models\Grade;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ProfDashboardController extends Controller
{
    // public function index()
    // {
    //     $professorId = Auth::id();

    //     $sections = ClassModel::where('professor_id', $professorId)
    //         ->select('id', 'name', 'icon', 'term')
    //         ->with('students.student')
    //         ->get()
    //         ->map(function ($section) {
    //             $students = $section->students;
    //             $studentCount = $students->count();

    //             // Performance breakdown
    //             $performance = AssessmentItem::whereHas('assessment', function ($query) use ($section) {
    //                     $query->where('class_id', $section->id);
    //                 })
    //                 ->get()
    //                 ->groupBy('name')
    //                 ->mapWithKeys(function ($items, $name) {
    //                     return [$name => round($items->avg('score'))];
    //                 });

    //             return [
    //                 'id' => (string) $section->id,
    //                 'name' => $section->name,
    //                 'icon' => $section->icon ?? '📚',
    //                 'term' => $section->term ?? 'Current Term',
    //                 'studentCount' => $studentCount,
    //             ];
    //         });

    //     return Inertia::render('Professor/ProfDashboard', [
    //         'sections' => $sections,
    //     ]);
    // }

public function index()
{
    $professorId = Auth::id();

    // Fetch all classes for the logged-in professor
    $sections = ClassModel::where('professor_id', $professorId)
    ->select('id', 'name', 'icon', 'term', 'schedule')  // Include the 'schedule' field in the select statement
    ->with('students.student')
    ->get()
    ->map(function ($section) {
        $students = $section->students;
        $studentCount = $students->count();

        // Process the schedule field like you showed in the example
        $schedule = $section->schedule ? json_encode($section->schedule) : null;
        $days = $section->schedule ? array_map(function ($scheduleItem) {
            // Ensure $scheduleItem is a string and safely handle cases where it's not
            return is_string($scheduleItem) ? explode(' ', $scheduleItem)[0] : null;
        }, is_array($section->schedule) ? $section->schedule : (is_string($section->schedule) ? json_decode($section->schedule, true) : [])) : [];

        return [
            'id' => (string) $section->id,
            'name' => $section->name,
            'icon' => $section->icon ?? '📚',
            'term' => $section->term ?? 'Current Term',
            'studentCount' => $studentCount,
            'schedule' => $schedule,
            'days' => $days,
        ];
    });


    // Fetch grades and filter by classes that belong to the logged-in professor
    $grades = FinalGrade::with('student', 'studentClass.class')
        ->whereHas('studentClass.class', function ($query) use ($professorId) {
            $query->where('professor_id', $professorId);
        })
        ->get();

    // Group grades by class and then by term
    $gradesByClass = $grades->groupBy(function ($grade) {
        return $grade->studentClass->class->id; // Group by class ID
    });

    // Top performers by class and term (Prelim, Midterm, Final)
    $topPerformersByClassAndTerm = $gradesByClass->map(function ($gradesInClass) {
        // Group by term (Prelim, Midterm, Final Term)
        $gradesByTerm = $gradesInClass->groupBy(function ($grade) {
            return $grade->studentClass->class->term ?? 'Unknown Term';
        });

        // Now map over each term and sort by grade
        return $gradesByTerm->map(function ($gradesInTerm) {
            return collect([
                'Prelim' => $gradesInTerm->sortByDesc('prelim')->take(10)->map(function ($grade) {
                    return [
                        'student_name' => $grade->student->name ?? 'Unknown',
                        'email' => $grade->student->email ?? 'No email',
                        'avatar' => $grade->student->avatar ?? null,
                        'grade' => $grade->prelim,
                    ];
                }),

                'Midterm' => $gradesInTerm->sortByDesc('midterm')->take(10)->map(function ($grade) {
                    return [
                        'student_name' => $grade->student->name ?? 'Unknown',
                        'email' => $grade->student->email ?? 'No email',
                        'avatar' => $grade->student->avatar ?? null,
                        'grade' => $grade->midterm,
                    ];
                }),

                'Final Term' => $gradesInTerm->sortByDesc('final_term')->take(10)->map(function ($grade) {
                    return [
                        'student_name' => $grade->student->name ?? 'Unknown',
                        'email' => $grade->student->email ?? 'No email',
                        'avatar' => $grade->student->avatar ?? null,
                        'grade' => $grade->final_term,
                    ];
                }),

                'Final Grade' => $gradesInTerm->sortByDesc('final_garde')->take(10)->map(function ($grade) {
                    return [
                        'student_name' => $grade->student->name ?? 'Unknown',
                        'email' => $grade->student->email ?? 'No email',
                        'avatar' => $grade->student->avatar ?? null,
                        'grade' => $grade->final_term,
                    ];
                }),
            ]);
        });
    });

    // Attendance stats per class
    $attendanceStats = \App\Models\Attendance::whereIn('class_id', $sections->pluck('id'))
        ->get()
        ->groupBy('class_id')
        ->map(function ($records) {
            $present = $records->where('status', 'present')->count();
            $absent = $records->where('status', 'absent')->count();
            return [
                'present' => $present,
                'absent' => $absent,
                'attendanceRate' => ($present + $absent) > 0
                    ? round(($present / ($present + $absent)) * 100, 2)
                    : 0,
            ];
        });

    // Recent behavior reports
    $recentBehaviorReports = \App\Models\BehaviorReport::whereIn('class_id', $sections->pluck('id'))
        ->with('student:id,name')
        ->latest()
        ->take(5)
        ->get()
        ->map(function ($report) {
            return [
                'student' => $report->student->name ?? 'Unknown',
                'description' => $report->description,
                'type' => $report->type ?? 'General',
                'date' => $report->created_at->toDateString(),
            ];
        });

    return Inertia::render('Professor/ProfDashboard', [
        'sections' => $sections,
        'topPerformersByClassAndTerm' => $topPerformersByClassAndTerm,
        'totalStudents' => $sections->sum('studentCount'),
        'attendanceStats' => $attendanceStats,
        'recentBehaviorReports' => $recentBehaviorReports,
    ]);
}


}

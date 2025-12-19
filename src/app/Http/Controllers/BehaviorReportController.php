<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\ClassModel;
use App\Models\BehaviorReport;
use App\Models\StudentClass;
use Inertia\Inertia;
use Illuminate\Http\Request;

class BehaviorReportController extends Controller
{
    public function index()
    {
        $professorId = Auth::id();

        $sections = ClassModel::where('professor_id', $professorId)
            ->with([
                'behaviorReports.student', // student is a User
                'students.student', // students() is StudentClass; student() is User
            ])
            ->get()
            ->map(function ($section) {
                $students = $section->students; // StudentClass records
                $studentCount = $students->count();

                // Behavior Summary
                $positive = $section->behaviorReports->where('type', 'positive')->count();
                $warnings = $section->behaviorReports->where('type', 'warning')->count();
                $incidents = $section->behaviorReports->where('type', 'incident')->count();

                // Behavior Reports
                // Behavior Reports
$behaviorReports = $section->behaviorReports->map(function ($report) {
    return [
        'student' => $report->student->name ?? 'Unknown',
        'icon' => $this->getIcon($report->type),
        'iconBg' => $this->getIconBg($report->type),
        'iconColor' => $this->getIconColor($report->type),
        'type' => $report->type,
        'typeClass' => $this->getTypeClass($report->type),
        'description' => $report->comment,
        'date' => \Carbon\Carbon::parse($report->created_at)->format('M j, Y'),
        'date_raw' => $report->created_at, // Use created_at for sorting
    ];
});


                // Sort behavior reports in descending order by created_at
                $behaviorReports = collect($behaviorReports)->sortByDesc('date_raw')->values()->toArray();

                return [
                    'id' => (string) $section->id,
                    'name' => $section->name,
                    'icon' => $section->icon ?? '📚',
                    'term' => $section->term ?? 'Current Term',
                    'studentCount' => $studentCount,
                    'behaviorSummary' => [
                        'positive' => $positive,
                        'warnings' => $warnings,
                        'incidents' => $incidents,
                    ],
                    'students' => $students->map(function ($studentClass) {
                        $student = $studentClass->student;
                        return [
                            'id' => $student->id,
                            'name' => $student->name ?? 'Unknown',
                            'email' => $student->email ?? 'No Email',
                        ];
                    }),
                    'behaviorReports' => $behaviorReports,
                ];
            });

        return Inertia::render('Professor/BehaviorReports', [
            'sections' => $sections,
        ]);
    }


    private function getIcon($type)
    {
        return match ($type) {
            'positive' => '🌟',
            'warning' => '⚠️',
            'incident' => '❌',
            default => '📌',
        };
    }

    private function getIconBg($type)
    {
        return match ($type) {
            'positive' => 'bg-green-100 dark:bg-green-900',
            'warning' => 'bg-yellow-100 dark:bg-yellow-900',
            'incident' => 'bg-red-100 dark:bg-red-900',
            default => 'bg-gray-100 dark:bg-gray-900',
        };
    }

    private function getIconColor($type)
    {
        return match ($type) {
            'positive' => 'text-green-600 dark:text-green-400',
            'warning' => 'text-yellow-600 dark:text-yellow-400',
            'incident' => 'text-red-600 dark:text-red-400',
            default => 'text-gray-600 dark:text-gray-400',
        };
    }

    private function getTypeClass($type)
    {
        return match ($type) {
            'positive' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'incident' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
        };
    }

    public function store(Request $request)
    {

        $professorId = Auth::id();
        // Temporarily bypass validation to test if it works
        // $validated = $request->validate([
        //     'student_id' => 'required|exists:students,id',
        //     'class_id' => 'required|exists:classes,id',
        //     'description' => 'nullable|string',
        //     'date' => 'required|date',
        //     'type' => 'required|string|in:positive,warning,incident',
        //     'professor_id' => 'required|exists:professors,id', // Assuming you have a professors table
        //     'title' => 'required|string|max:255',
        // ]);


        // Now manually check if all necessary fields exist
        if (!$request->student_id || !$request->class_id || !$request->date || !$request->type) {
            return response()->json(['error' => 'Missing required fields'], 400);
        }

        // Store the behavior report data
        $behaviorReport = new BehaviorReport([
            'student_id' => $request->student_id,
            'class_id' => $request->class_id,
            'professor_id' => $professorId,
            'title' => $request->title,
            'comment' => $request->description,
            'date' => $request->date,
            'type' => $request->type,
        ]);


        // Save to the database
        $behaviorReport->save();

        // Return success response
        return response()->json(['message' => 'Class added successfully!', 'report' => $behaviorReport], 201);
    }


//     public function store(Request $request)
// {
//     try {
//         $validated = $request->validate([
//             'student_id' => 'required|exists:users,id',
//             'class_id' => 'required|exists:classes,id',
//             'comment' => 'nullable|string',
//             'date' => 'required|date',
//             'type' => 'required|string|in:positive,warning,incident',
//         ]);

//         $behaviorReport = new BehaviorReport([
//             'student_id' => $validated['student_id'],
//             'class_id' => $validated['class_id'],
//             'comment' => $validated['description'],
//             'date' => $validated['date'],
//             'type' => $validated['type'],
//         ]);

//         $behaviorReport->save();

//         // Return a JSON response instead of redirect()
//         return response()->json(['message' => 'Behavior Report added successfully!'], 200);

//     } catch (\Exception $e) {
//         \Log::error('Failed to save behavior report', ['error' => $e->getMessage()]);
//         return response()->json(['error' => 'Failed to save behavior report', 'details' => $e->getMessage()], 500);
//     }
// }


}

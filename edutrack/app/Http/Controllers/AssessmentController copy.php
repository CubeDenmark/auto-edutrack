<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Assessment;
use App\Models\AssessmentItem;
use App\Models\Student;  // Assuming you have a Student model
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AssessmentController extends Controller
{
    public function index()
    {
        $professorId = Auth::id();

        $sections = ClassModel::where('professor_id', $professorId)
            ->select('id', 'name', 'icon', 'term')
            ->with([
                'students.student',  // Relationship for fetching students
                'assessments.items', // Fetching assessments and items
            ])
            ->get()
            ->map(function ($section) {
                $students = $section->students;
                $studentCount = $students->count();

                // Grade Distribution (Calculating A, B, C, D, F based on student scores)
                $gradeDistribution = [
                    'a' => $students->where('score', '>=', 90)->count(),
                    'b' => $students->where('score', '>=', 80)->where('score', '<', 90)->count(),
                    'c' => $students->where('score', '>=', 70)->where('score', '<', 80)->count(),
                    'd' => $students->where('score', '>=', 60)->where('score', '<', 70)->count(),
                    'f' => $students->where('score', '<', 60)->count(),
                ];

                // Pending Assignments count
                $pendingAssignments = Assessment::where('class_id', $section->id)->count();

                // Calculate average score
                $averageScore = $students->avg('score') ?? 0;

                // Performance breakdown (e.g., quiz1, midterm, project)
                $performance = [];
                foreach ($section->assessments as $assessment) {
                    foreach ($assessment->items as $item) {
                        $performance[$assessment->name][$item->name] = $item->score;
                    }
                }

                // Assignments Data
                $assignments = $section->assessments->map(function ($assessment) {
                    return [
                        'id' => $assessment->id,
                        'name' => $assessment->name,
                        'category' => $assessment->type,
                        'weight' => $assessment->weight,
                        'totalPoints' => $assessment->items->sum('total_score'),
                        'dueDate' => $assessment->due_date, // Assuming you have a due_date field in assessments
                        'items' => $assessment->items->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'name' => $item->name,
                                'total_score' => $item->total_score,
                            ];
                        }),
                    ];
                });

                // Student Details with Scores and Comments
                $studentsData = $students->map(function ($student) {
                    return [
                        'id' => $student->student_id,
                        'name' => $student->student->name ?? 'Unknown',
                        'email' => $student->student->email ?? 'No Email',
                        'status' => $student->status ?? 'Unknown',
                        'score' => $student->score ?? 0,
                        'gradeComment' => $student->grade_comment ?? 'No comment',
                    ];
                });

                return [
                    'id' => (string) $section->id,
                    'name' => $section->name,
                    'icon' => $section->icon ?? '📚',
                    'term' => $section->term ?? 'Current Term',
                    'studentCount' => $studentCount,
                    'pendingAssignments' => $pendingAssignments ?? 0,
                    'averageScore' => round($averageScore) ?? 0,
                    'gradeDistribution' => $gradeDistribution,
                    'performance' => $performance,
                    'assignments' => $assignments,
                    'students' => $studentsData,
                ];
            });

        return Inertia::render('Professor/Assessment', [
            'sections' => $sections,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'assessment_name' => 'required|string|max:255',
            'term' => 'required|string|max:50',
            'weight' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Assessment::create([
            'assessment_name' => $request->assessment_name,
            'term' => $request->term,
            'weight' => $request->weight,
        ]);

        return redirect()->back()->with('success', 'Assessment created successfully.');
    }

    // Update existing assessment
    public function update(Request $request, $id)
    {
        $assessment = Assessment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'assessment_name' => 'required|string|max:255',
            'term' => 'required|string|max:50',
            'weight' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $assessment->update([
            'assessment_name' => $request->assessment_name,
            'term' => $request->term,
            'weight' => $request->weight,
        ]);

        return redirect()->back()->with('success', 'Assessment updated successfully.');
    }
}

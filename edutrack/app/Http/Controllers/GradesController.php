<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Assessment;
use App\Models\AssessmentItem;
use App\Models\FinalGrade;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class GradesController extends Controller
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

    //             // Pending assignments count
    //             $pendingAssignments = Assessment::where('class_id', $section->id)->count();

    //             // Count assessments to be graded
    //             $toGradeCount = AssessmentItem::whereHas('assessment', function ($query) use ($section) {
    //                     $query->where('class_id', $section->id);
    //                 })
    //                 ->whereNull('score')
    //                 ->count();

    //             // Calculate average score
    //             $averageScore = AssessmentItem::whereHas('assessment', function ($query) use ($section) {
    //                     $query->where('class_id', $section->id);
    //                 })
    //                 ->avg('score') ?? 0;

    //             // Performance breakdown (e.g., quiz1, midterm, project)
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
    //                 'pendingAssignments' => $pendingAssignments ?? 0,
    //                 'toGradeCount' => $toGradeCount ?? 0,
    //                 'averageScore' => round($averageScore) ?? 0,
    //                 'performance' => $performance ?? ['quiz1' => 0, 'midterm' => 0, 'project' => 0, 'quiz2' => 0],
    //                 'students' => $students->map(function ($student) {
    //                     return [
    //                         'id' => $student->student_id,
    //                         'name' => $student->student->name ?? 'Unknown',
    //                         'email' => $student->student->email ?? 'No Email',
    //                         'status' => $student->status ?? 'Unknown',
    //                     ];
    //                 }),
    //             ];
    //         });

    //     return Inertia::render('Professor/Grades', [
    //         'sections' => $sections,
    //     ]);
    // }


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

    //             // Count pending assignments (assessments created)
    //             $pendingAssignments = Assessment::where('class_id', $section->id)->count();

    //             // Count assessment items that haven't been graded
    //             $toGradeCount = Grade::whereHas('assessmentItem.assessment', function ($query) use ($section) {
    //                 $query->where('class_id', $section->id);
    //             })
    //                 ->whereNull('score')
    //                 ->count();

    //             // Calculate overall average score
    //             $averageScore = Grade::whereHas('assessmentItem.assessment', function ($query) use ($section) {
    //                 $query->where('class_id', $section->id);
    //             })
    //                 ->avg('score') ?? 0;

    //             // Performance breakdown (by item name like quiz1, midterm, etc.)
    //             $performance = Grade::whereHas('assessmentItem.assessment', function ($query) use ($section) {
    //                 $query->where('class_id', $section->id);
    //             })
    //                 ->with('assessmentItem')
    //                 ->get()
    //                 ->groupBy(function ($grade) {
    //                     return $grade->assessmentItem->name ?? 'Unknown';
    //                 })
    //                 ->mapWithKeys(function ($grades, $name) {
    //                     return [$name => round($grades->avg('score'))];
    //                 });

    //             return [
    //                 'id' => (string) $section->id,
    //                 'name' => $section->name,
    //                 'icon' => $section->icon ?? '📚',
    //                 'term' => $section->term ?? 'Current Term',
    //                 'studentCount' => $studentCount,
    //                 'pendingAssignments' => $pendingAssignments ?? 0,
    //                 'toGradeCount' => $toGradeCount ?? 0,
    //                 'averageScore' => round($averageScore) ?? 0,
    //                 'performance' => $performance ?? [],
    //                 'students' => $students->map(function ($student) {
    //                     return [
    //                         'id' => $student->student_id,
    //                         'name' => $student->student->name ?? 'Unknown',
    //                         'email' => $student->student->email ?? 'No Email',
    //                         'status' => $student->status ?? 'Unknown',
    //                     ];
    //                 }),
    //             ];
    //         });

    //     return Inertia::render('Professor/Grades', [
    //         'sections' => $sections,
    //     ]);
    // }
    public function index()
    {
        $professorId = Auth::id();

        $sections = ClassModel::where('professor_id', $professorId)
            ->select('id', 'name', 'icon', 'term')
            ->with('students.student')
            ->get()
            ->map(function ($section) {
                $students = $section->students;
                $studentCount = $students->count();

                $pendingAssignments = Assessment::where('class_id', $section->id)->count();

                $toGradeCount = Grade::whereHas('assessmentItem.assessment', function ($query) use ($section) {
                    $query->where('class_id', $section->id);
                })->whereNull('score')->count();

                $averageScore = Grade::whereHas('assessmentItem.assessment', function ($query) use ($section) {
                    $query->where('class_id', $section->id);
                })->avg('score') ?? 0;

                $assessments = Assessment::where('class_id', $section->id)
                    ->with(['assessmentItems'])
                    ->get()
                    ->map(function ($assessment) {
                        $totalPoints = $assessment->assessmentItems->sum('total_score');

                        return [
                            'id' => $assessment->id,
                            'assessment_name' => $assessment->assessment_name,
                            'term' => $assessment->term,
                            'weight' => $assessment->weight,
                            'items' => $assessment->assessmentItems,
                            'totalPoints' => $totalPoints,
                        ];
                    });

                $performance = Grade::whereHas('assessmentItem.assessment', function ($query) use ($section) {
                    $query->where('class_id', $section->id);
                })
                    ->with('assessmentItem')
                    ->get()
                    ->groupBy(function ($grade) {
                        return $grade->assessmentItem->name ?? 'Unknown';
                    })
                    ->mapWithKeys(function ($grades, $name) {
                        return [$name => round($grades->avg('score'))];
                    });

                return [
                    'id' => (string) $section->id,
                    'name' => $section->name,
                    'icon' => $section->icon ?? '📚',
                    'term' => $section->term ?? 'Current Term',
                    'studentCount' => $studentCount,
                    'toGradeCount' => $toGradeCount ?? 0,
                    'averageScore' => round($averageScore) ?? 0,
                    'performance' => $performance ?? [],
                    'assessments' => $assessments,

                    'students' => $students->map(function ($student) use ($section, $assessments) {
                        $studentGrades = Grade::whereHas('assessmentItem.assessment', function ($query) use ($section) {
                            $query->where('class_id', $section->id);
                        })
                            ->where('student_id', $student->student_id)
                            ->with(['assessmentItem.assessment'])
                            ->get()
                            ->groupBy(function ($grade) {
                                return $grade->assessmentItem->assessment->id;
                            })
                            ->map(function ($grades) use ($assessments) {
                                $assessmentId = $grades->first()->assessmentItem->assessment->id;
                                $assessmentName = $grades->first()->assessmentItem->assessment->name;

                                $totalScore = $grades->sum('score');

                                $assessmentDetails = collect($assessments)->firstWhere('id', $assessmentId);
                                $maxScore = $assessmentDetails['totalPoints'] ?? 0;

                                return [
                                    'assessment_id' => $assessmentId,
                                    'assessment_name' => $assessmentName,
                                    'total_score' => $totalScore,
                                    'max_score' => $maxScore,
                                    'grades' => $grades->mapWithKeys(function ($grade) {
                                        return [$grade->assessmentItem->id => $grade->score];
                                    }),
                                ];
                            });

                        return [
                            'id' => $student->student_id,
                            'name' => $student->student->name ?? 'Unknown',
                            'avatar' => $student->student->avatar,
                            'school_id' => $student->student->school_id,
                            'email' => $student->student->email ?? 'No Email',
                            'status' => $student->status ?? 'Unknown',
                            'student_class_id' => $student->id, // ✅ THIS IS THE FIX!
                            'assessment_grades' => $studentGrades,
                        ];
                    }),
                ];
            });

        return Inertia::render('Professor/Grades', [
            'sections' => $sections,
        ]);
    }



    // public function storeOrUpdateFinalGrade(Request $request)
    // {
    //     $validated = $request->validate([
    //         'records' => 'required|array',
    //         'records.*.student_id' => 'required|exists:users,id',
    //         'records.*.student_class_id' => 'required|exists:students_classes,id',
    //         'records.*.prelim' => 'nullable|numeric',
    //         'records.*.midterm' => 'nullable|numeric',
    //         'records.*.final_term' => 'nullable|numeric',
    //         'records.*.final_grade' => 'nullable|numeric',
    //         'records.*.final_remarks' => 'nullable|string',
    //     ]);

    //     foreach ($validated['records'] as $record) {
    //         \Log::info('Processing record:', $record);

    //         $student = User::find($record['student_id']);
    //         if (!$student) {
    //             return response()->json(['error' => 'Student not found.'], 404);
    //         }

    //         $finalGrade = FinalGrade::where('student_id', $record['student_id'])
    //             ->where('student_class_id', $record['student_class_id'])
    //             ->first();

    //         if ($finalGrade) {
    //             // Update
    //             $finalGrade->update([
    //                 'prelim' => $record['prelim'],
    //                 'midterm' => $record['midterm'],
    //                 'final_term' => $record['final_term'],
    //                 'final_grade' => $record['final_grade'],
    //                 'final_remarks' => $record['final_remarks'] ?? '',
    //             ]);
    //         } else {
    //             // Create only if not found
    //             FinalGrade::create([
    //                 'student_id' => $record['student_id'],
    //                 'student_class_id' => $record['student_class_id'],
    //                 'prelim' => $record['prelim'],
    //                 'midterm' => $record['midterm'],
    //                 'final_term' => $record['final_term'],
    //                 'final_grade' => $record['final_grade'],
    //                 'final_remarks' => $record['final_remarks'] ?? '',
    //             ]);
    //         }
    //     }

    //     return redirect()->back()->with('success', 'Scores added or updated successfully.');
    // }
    public function storeOrUpdateFinalGrade(Request $request)
    {
        // Validate the incoming data
        $validated = $request->validate([
            'records' => 'required|array',
            'records.*.student_id' => 'required|exists:users,id', // Check if student exists
            'records.*.student_class_id' => 'required|exists:students_classes,id',
            'records.*.prelim' => 'nullable|numeric',
            'records.*.midterm' => 'nullable|numeric',
            'records.*.final_term' => 'nullable|numeric',
            'records.*.final_grade' => 'nullable|numeric',
            'records.*.final_remarks' => 'nullable|string',
        ]);

        // Process each record
        foreach ($validated['records'] as $record) {
            \Log::info('Processing record:', $record);

            // Find the student by ID
            $student = User::find($record['student_id']);
            if (!$student) {
                return response()->json(['error' => 'Student not found.'], 404);
            }

            // Check if a FinalGrade record exists for the student and class
            $finalGrade = FinalGrade::where('student_id', $record['student_id'])
                ->where('student_class_id', $record['student_class_id'])
                ->first();

            if ($finalGrade) {
                // If record exists, update it
                $finalGrade->update([
                    'prelim' => $record['prelim'],
                    'midterm' => $record['midterm'],
                    'final_term' => $record['final_term'],
                    'final_grade' => $record['final_grade'],
                    'final_remarks' => $record['final_remarks'] ?? '',
                ]);
            } else {
                // If record does not exist, create a new one
                FinalGrade::create([
                    'student_id' => $record['student_id'],
                    'student_class_id' => $record['student_class_id'],
                    'prelim' => $record['prelim'],
                    'midterm' => $record['midterm'],
                    'final_term' => $record['final_term'],
                    'final_grade' => $record['final_grade'],
                    'final_remarks' => $record['final_remarks'] ?? '',
                ]);
            }
        }

        return redirect()->back()->with('success', 'Scores added or updated successfully.');
    }
}

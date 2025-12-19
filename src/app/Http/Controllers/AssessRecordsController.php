<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Assessment;
use App\Models\AssessmentItem;
use App\Models\Grade;
use App\Models\Student;  // Assuming you have a Student model
use App\Models\StudentClass;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AssessRecordsController extends Controller
{
    // public function index()
    // {
    //     $professorId = Auth::id();

    //     $sections = ClassModel::where('professor_id', $professorId)
    //         ->select('id', 'name', 'icon', 'term')
    //         ->with([
    //             'students.student',  // Relationship for fetching students
    //             'assessments.items', // Fetching assessments and items
    //         ])
    //         ->get()
    //         ->map(function ($section) {
    //             $students = $section->students;
    //             $studentCount = $students->count();

    //             // Grade Distribution (Calculating A, B, C, D, F based on student scores)
    //             $gradeDistribution = [
    //                 'a' => $students->where('score', '>=', 90)->count(),
    //                 'b' => $students->where('score', '>=', 80)->where('score', '<', 90)->count(),
    //                 'c' => $students->where('score', '>=', 70)->where('score', '<', 80)->count(),
    //                 'd' => $students->where('score', '>=', 60)->where('score', '<', 70)->count(),
    //                 'f' => $students->where('score', '<', 60)->count(),
    //             ];

    //             // Pending Assignments count
    //             $assessment = Assessment::where('class_id', $section->id)->count();

    //             // Calculate average score
    //             $averageScore = $students->avg('score') ?? 0;

    //             // Performance breakdown (e.g., quiz1, midterm, project)
    //             $performance = [];
    //             foreach ($section->assessments as $assessment) {
    //                 foreach ($assessment->items as $item) {
    //                     $performance[$assessment->assessment_name][$item->name] = $item->score;
    //                 }
    //             }

    //             // Assignments Data
    //             $assessmentsItems = $section->assessments->map(function ($assessment) {
    //                 return [
    //                     'id' => $assessment->id,
    //                     'name' => $assessment->assessment_name,
    //                     'category' => $assessment->term,
    //                     'weight' => $assessment->weight,
    //                     'totalPoints' => $assessment->items->sum('total_score'),
    //                     'items' => $assessment->items->map(function ($item) {
    //                         return [
    //                             'id' => $item->id,
    //                             'name' => $item->name,
    //                             'total_score' => $item->total_score,
    //                         ];
    //                     }),
    //                 ];
    //             });

    //             // Student Details with Scores and Comments
    //             $studentsData = $students->map(function ($student) {
    //                 return [
    //                     'id' => $student->student_id,
    //                     'name' => $student->student->name ?? 'Unknown',
    //                     'email' => $student->student->email ?? 'No Email',
    //                     'status' => $student->status ?? 'Unknown',
    //                     'score' => $student->score ?? 0,
    //                     'gradeComment' => $student->grade_comment ?? 'No comment',
    //                 ];
    //             });

    //             return [
    //                 'id' => (string) $section->id,
    //                 'name' => $section->name,
    //                 'icon' => $section->icon ?? '📚',
    //                 'term' => $section->term ?? 'Current Term',
    //                 'studentCount' => $studentCount,
    //                 'averageScore' => round($averageScore) ?? 0,
    //                 'gradeDistribution' => $gradeDistribution,
    //                 'performance' => $performance,
    //                 'students' => $studentsData,
    //             ];
    //         });

    //     return Inertia::render('Professor/AssessmentRecords', [
    //         'sections' => $sections,
    //     ]);
    // }


    // public function index()
    // {
    //     $professorId = Auth::id();

    //     $sections = ClassModel::where('professor_id', $professorId)
    //         ->select('id', 'name', 'icon', 'term')
    //         ->with([
    //             'students.student',        // Fetch student user info
    //             'assessments.items',       // Fetch assessments and their items
    //         ])
    //         ->get()
    //         ->map(function ($section) {
    //             $students = $section->students;
    //             $studentCount = $students->count();

    //             // Grade Distribution
    //             $gradeDistribution = [
    //                 'a' => $students->where('score', '>=', 90)->count(),
    //                 'b' => $students->where('score', '>=', 80)->where('score', '<', 90)->count(),
    //                 'c' => $students->where('score', '>=', 70)->where('score', '<', 80)->count(),
    //                 'd' => $students->where('score', '>=', 60)->where('score', '<', 70)->count(),
    //                 'f' => $students->where('score', '<', 60)->count(),
    //             ];

    //             // Average Score
    //             $averageScore = $students->avg('score') ?? 0;

    //             // Performance per assessment
    //             $performance = [];
    //             foreach ($section->assessments as $assessment) {
    //                 $assessmentName = $assessment->assessment_name;
    //                 foreach ($assessment->items as $item) {
    //                     $performance[$assessmentName][$item->name] = $item->score ?? null;
    //                 }
    //             }

    //             // Assessment items list
    //             $assessmentsItems = $section->assessments->map(function ($assessment) {
    //                 return [
    //                     'id' => $assessment->id,
    //                     'name' => $assessment->assessment_name,
    //                     'category' => $assessment->term,
    //                     'weight' => $assessment->weight,
    //                     'totalPoints' => $assessment->items->sum('total_score'),
    //                     'items' => $assessment->items->map(function ($item) {
    //                         return [
    //                             'id' => $item->id,
    //                             'name' => $item->name,
    //                             'total_score' => $item->total_score,
    //                             'score' => $item->score ?? null, // Item score from item model
    //                         ];
    //                     }),
    //                 ];
    //             });

    //             // Student data and scores
    //             $studentsData = $students->map(function ($student) use ($section) {
    //                 $studentClass = StudentClass::where('student_id', $student->student_id)->first();
    //                 $studentClassId = $studentClass?->class_id;

    //                 return [
    //                     'id' => $student->student_id,
    //                     'name' => $student->student->name ?? 'Unknown',
    //                     'email' => $student->student->email ?? 'No Email',
    //                     'status' => $student->status ?? 'Unknown',
    //                     'score' => $student->score ?? 0,
    //                     'student_class_id' => $studentClassId,
    //                     'gradeComment' => $student->grade_comment ?? 'No comment',

    //                     // Detailed assessment scores
    //                     'assessmentScores' => $section->assessments->map(function ($assessment) use ($student) {
    //                         return [
    //                             'assessment_id' => $assessment->id,
    //                             'assessment_name' => $assessment->assessment_name,
    //                             'items' => $assessment->items->map(function ($item) use ($student) {
    //                                 $studentGrade = $student->grades()->where('assessment_item_id', $item->id)->first();
    //                                 return [
    //                                     'item_id' => $item->id,
    //                                     'item_name' => $item->name,
    //                                     'item_score' => $studentGrade->score ?? null,
    //                                     'total_score' => $item->total_score,
    //                                 ];
    //                             }),
    //                         ];
    //                     }),
    //                 ];
    //             });

    //             return [
    //                 'id' => (string) $section->id,
    //                 'name' => $section->name,
    //                 'icon' => $section->icon ?? '📚',
    //                 'term' => $section->term ?? 'Current Term',
    //                 'studentCount' => $studentCount,
    //                 'averageScore' => round($averageScore, 2),
    //                 'gradeDistribution' => $gradeDistribution,
    //                 'performance' => $performance,
    //                 'students' => $studentsData,
    //                 'assessments' => $assessmentsItems,
    //             ];
    //         });

    //     return Inertia::render('Professor/AssessmentRecords', [
    //         'sections' => $sections,
    //     ]);
    // }

    //     public function index()
    // {
    //     $professorId = Auth::id();

    //     $sections = ClassModel::where('professor_id', $professorId)
    //         ->select('id', 'name', 'icon', 'term')
    //         ->with([
    //             'students.student',        // Fetch student user info
    //             'assessments.items',       // Fetch assessments and their items
    //             'students.grades',         // Eagerly load all grades for all students
    //         ])
    //         ->get()
    //         ->map(function ($section) {
    //             $students = $section->students;
    //             $studentCount = $students->count();

    //             // Grade Distribution
    //             $gradeDistribution = [
    //                 'a' => $students->where('score', '>=', 90)->count(),
    //                 'b' => $students->where('score', '>=', 80)->where('score', '<', 90)->count(),
    //                 'c' => $students->where('score', '>=', 70)->where('score', '<', 80)->count(),
    //                 'd' => $students->where('score', '>=', 60)->where('score', '<', 70)->count(),
    //                 'f' => $students->where('score', '<', 60)->count(),
    //             ];

    //             // Average Score
    //             $averageScore = $students->avg('score') ?? 0;

    //             // Performance per assessment
    //             $performance = [];
    //             foreach ($section->assessments as $assessment) {
    //                 $assessmentName = $assessment->assessment_name;
    //                 foreach ($assessment->items as $item) {
    //                     $performance[$assessmentName][$item->name] = $item->score ?? null;
    //                 }
    //             }

    //             // Assessment items list
    //             $assessmentsItems = $section->assessments->map(function ($assessment) {
    //                 return [
    //                     'id' => $assessment->id,
    //                     'name' => $assessment->assessment_name,
    //                     'category' => $assessment->term,
    //                     'weight' => $assessment->weight,
    //                     'totalPoints' => $assessment->items->sum('total_score'),
    //                     'items' => $assessment->items->map(function ($item) {
    //                         return [
    //                             'id' => $item->id,
    //                             'name' => $item->name,
    //                             'total_score' => $item->total_score,
    //                             'score' => $item->score ?? null, // Item score from item model
    //                         ];
    //                     }),
    //                 ];
    //             });

    //             // Student data and scores
    //             $studentsData = $students->map(function ($student) use ($section) {
    //                 // Get the student class record
    //                 $studentClass = StudentClass::where('student_id', $student->student_id)
    //                     ->where('class_id', $section->id)
    //                     ->first();

    //                 $studentClassId = $studentClass ? $studentClass->id : null;

    //                 // Organize all grades by assessment item ID for easy lookup
    //                 $gradesMap = [];
    //                 $remarks = [];

    //                 if ($student->grades) {
    //                     foreach ($student->grades as $grade) {
    //                         $gradesMap[$grade->assessment_item_id] = $grade->score;

    //                         // Store the most recent remark for this student
    //                         if ($grade->remarks) {
    //                             $remarks[$grade->assessment_id] = $grade->remarks;
    //                         }
    //                     }
    //                 }

    //                 // Get the most recent remark or use a default
    //                 $latestRemark = count($remarks) > 0 ? end($remarks) : null;

    //                 return [
    //                     'id' => $student->student_id,
    //                     'name' => $student->student->name ?? 'Unknown',
    //                     'email' => $student->student->email ?? 'No Email',
    //                     'status' => $student->status ?? 'active',
    //                     'score' => $student->score ?? 0,
    //                     'student_class_id' => $studentClassId,
    //                     'remarks' => $latestRemark, // Use the latest remark
    //                     'gradeComment' => $student->grade_comment ?? $latestRemark ?? 'No comment',

    //                     // Detailed assessment scores
    //                     'assessmentScores' => $section->assessments->map(function ($assessment) use ($student, $gradesMap) {
    //                         $assessmentItems = [];

    //                         foreach ($assessment->items as $item) {
    //                             $score = $gradesMap[$item->id] ?? null;

    //                             $assessmentItems[] = [
    //                                 'item_id' => $item->id,
    //                                 'item_name' => $item->name,
    //                                 'item_score' => $score,
    //                                 'total_score' => $item->total_score,
    //                             ];
    //                         }

    //                         return [
    //                             'assessment_id' => $assessment->id,
    //                             'assessment_name' => $assessment->assessment_name,
    //                             'items' => $assessmentItems,
    //                         ];
    //                     }),
    //                 ];
    //             });

    //             return [
    //                 'id' => (string) $section->id,
    //                 'name' => $section->name,
    //                 'icon' => $section->icon ?? '📚',
    //                 'term' => $section->term ?? 'Current Term',
    //                 'studentCount' => $studentCount,
    //                 'averageScore' => round($averageScore, 2),
    //                 'gradeDistribution' => $gradeDistribution,
    //                 'performance' => $performance,
    //                 'students' => $studentsData,
    //                 'assessments' => $assessmentsItems,
    //             ];
    //         });

    //     return Inertia::render('Professor/AssessmentRecords', [
    //         'sections' => $sections,
    //     ]);
    // }

    // public function storeOrUpdateScores(Request $request)
    // {
    //     // Validate the incoming request
    //     $validated = $request->validate([
    //         'records' => 'required|array',
    //         'records.*.assessment_id' => 'required|exists:assessments,id',
    //         'records.*.assessment_item_id' => 'required|exists:assessment_items,id',
    //         'records.*.student_id' => 'required|exists:users,id',
    //         'records.*.class_id' => 'required|exists:classes,id',
    //         'records.*.student_class_id' => 'required|exists:students_classes,id',
    //         'records.*.score' => 'required|numeric',
    //         'records.*.remarks' => 'nullable|string',
    //     ]);


    //     // Process each record to either create or update the scores
    //     foreach ($validated['records'] as $record) {
    //         \Log::info('Processing record:', $record);  // Add this line for debugging

    //         $student = User::find($record['student_id']);

    //         if (!$student) {
    //             return response()->json(['error' => 'Student not found.'], 404);
    //         }

    //         Grade::updateOrCreate(
    //             [
    //                 'student_id' => $record['student_id'],
    //                 'assessment_item_id' => $record['assessment_item_id'],
    //                 'student_class_id' => $record['student_class_id'],
    //                 'assessment_id' => $record['assessment_id'], // Ensure assessment_id is provided
    //             ],
    //             [
    //                 'score' => $record['score'],
    //                 'remarks' => $record['remarks'] ?? '',
    //             ]
    //         );
    //     }


    //     return redirect()->back()->with('success', 'Score added successfully.');
    // }
    public function index()
    {
        $professorId = Auth::id();

        $sections = ClassModel::where('professor_id', $professorId)
            ->select('id', 'name', 'icon', 'term')
            ->with([
                'students.student',        // Fetch student user info
                'assessments.items',       // Fetch assessments and their items
                'students.grades',         // Eagerly load all grades for all students
            ])
            ->get()
            ->map(function ($section) {
                $students = $section->students;
                $studentCount = $students->count();

                // Grade Distribution
                $gradeDistribution = [
                    'a' => $students->where('score', '>=', 90)->count(),
                    'b' => $students->where('score', '>=', 80)->where('score', '<', 90)->count(),
                    'c' => $students->where('score', '>=', 70)->where('score', '<', 80)->count(),
                    'd' => $students->where('score', '>=', 60)->where('score', '<', 70)->count(),
                    'f' => $students->where('score', '<', 60)->count(),
                ];

                // Average Score
                $averageScore = $students->avg('score') ?? 0;

                // Performance per assessment
                $performance = [];
                foreach ($section->assessments as $assessment) {
                    $assessmentName = $assessment->assessment_name;
                    foreach ($assessment->items as $item) {
                        $performance[$assessmentName][$item->name] = $item->score ?? null;
                    }
                }

                // Assessment items list
                $assessmentsItems = $section->assessments->map(function ($assessment) {
                    return [
                        'id' => $assessment->id,
                        'name' => $assessment->assessment_name,
                        'category' => $assessment->term,
                        'weight' => $assessment->weight,
                        'totalPoints' => $assessment->items->sum('total_score'),
                        'items' => $assessment->items->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'name' => $item->name,
                                'total_score' => $item->total_score,
                                'score' => $item->score ?? null, // From item model
                            ];
                        }),
                    ];
                });

                // Student data and scores
                $studentsData = $students->map(function ($student) use ($section) {
                    // Get the student class record
                    $studentClass = StudentClass::where('student_id', $student->student_id)
                        ->where('class_id', $section->id)
                        ->first();

                    $studentClassId = $studentClass ? $studentClass->id : null;

                    // Fetch all grades for this student in this class
                    $grades = Grade::where('student_id', $student->student_id)
                        ->where('student_class_id', $studentClassId)
                        ->get();

                    // Organize grades by assessment item ID
                    $gradesMap = [];
                    $remarks = [];

                    foreach ($grades as $grade) {
                        $gradesMap[$grade->assessment_item_id] = [
                            'score' => $grade->score,
                            'remarks' => $grade->remarks,
                            'total_score' => $grade->total_score
                        ];

                        if ($grade->remarks) {
                            $remarks[$grade->assessment_item_id] = $grade->remarks;
                        }
                    }

                    $latestRemark = count($remarks) > 0 ? end($remarks) : null;

                    return [
                        'id' => $student->student_id,
                        'name' => $student->student->name ?? 'Unknown',
                        'avatar' => $student->student->avatar,
                        'school_id' => $student->student->school_id,
                        'email' => $student->student->email ?? 'No Email',
                        'status' => $student->status ?? 'active',
                        'score' => $student->score ?? 0,
                        'student_class_id' => $studentClassId,
                        'remarks' => $latestRemark,
                        'gradeComment' => $student->grade_comment ?? $latestRemark ?? 'No comment',

                        // Assessment scores
                        'assessmentScores' => $section->assessments->map(function ($assessment) use ($gradesMap) {
                            $assessmentItems = $assessment->items->map(function ($item) use ($gradesMap) {
                                return [
                                    'item_id' => $item->id,
                                    'item_name' => $item->name,
                                    'item_score' => $gradesMap[$item->id]['score'] ?? null,
                                    'total_score' => $item->total_score,
                                    'remarks' => $gradesMap[$item->id]['remarks'] ?? null, // ✅ Correctly include remarks
                                ];
                            });

                            return [
                                'assessment_id' => $assessment->id,
                                'assessment_name' => $assessment->assessment_name,
                                'items' => $assessmentItems,
                            ];
                        }),

                    ];
                });

                return [
                    'id' => (string) $section->id,
                    'name' => $section->name,
                    'icon' => $section->icon ?? '📚',
                    'term' => $section->term ?? 'Current Term',
                    'studentCount' => $studentCount,
                    'averageScore' => round($averageScore, 2),
                    'gradeDistribution' => $gradeDistribution,
                    'performance' => $performance,
                    'students' => $studentsData,
                    'assessments' => $assessmentsItems,
                ];
            });

        return Inertia::render('Professor/AssessmentRecords', [
            'sections' => $sections,
        ]);
    }

    public function storeOrUpdateScores(Request $request)
    {
        $validated = $request->validate([
            'records' => 'required|array',
            'records.*.assessment_id' => 'required|exists:assessments,id',
            'records.*.assessment_item_id' => 'required|exists:assessment_items,id',
            'records.*.student_id' => 'required|exists:users,id',

            'records.*.student_class_id' => 'required|exists:students_classes,id',
            'records.*.score' => 'required|numeric',
            'records.*.remarks' => 'nullable|string',
        ]);

        foreach ($validated['records'] as $record) {
            \Log::info('Processing record:', $record);

            $student = User::find($record['student_id']);
            if (!$student) {
                return response()->json(['error' => 'Student not found.'], 404);
            }

            $grade = Grade::where('student_id', $record['student_id'])
                ->where('assessment_item_id', $record['assessment_item_id'])
                ->where('student_class_id', $record['student_class_id'])
                ->first();

            if ($grade) {
                // Update
                $grade->update([
                    'score' => $record['score'],
                    'remarks' => $record['remarks'] ?? '',
                ]);
            } else {
                // Create
                Grade::create([
                    'student_id' => $record['student_id'],
                    'assessment_item_id' => $record['assessment_item_id'],
                    'student_class_id' => $record['student_class_id'],
                    'score' => $record['score'],
                    'remarks' => $record['remarks'] ?? '',
                ]);
            }
        }

        return redirect()->back()->with('success', 'Score added or updated successfully.');
    }
}

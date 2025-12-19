<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\BehaviorReport;
use App\Models\StudentClass;
use App\Models\FinalGrade;
use App\Models\Assessment;
use App\Models\AssessmentItem;
use App\Models\Grade;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StudentGradesController extends Controller
{
    public function index()
    {
        $student = Auth::user();
        $studentId = $student->id;

        // Get all sections where the student is enrolled
        $studentClasses = StudentClass::with(['class', 'class.professor'])
            ->where('student_id', $studentId)
            ->get();

        $sections = $studentClasses->map(function ($studentClass) use ($student) {
            $section = $studentClass->class;

            // Get the professor name for the section
            $sectionTeacherName = 'Unknown Teacher';
            if ($section->professor) {
                $sectionTeacherName = $section->professor->name;
            }

            return [
                'id' => (string) $section->id,
                'name' => $section->name,
                'icon' => $section->icon ?? '📚',
                'term' => $section->term ?? 'Unknown Term',
                'teacher' => $sectionTeacherName,
                'room' => $section->room ?? null,
                'studentClassId' => $studentClass->id, // Important for fetching grades
            ];
        });

        return Inertia::render('Student/StudentGrades', [
            'classSections' => $sections,
        ]);
    }

    /**
     * Get all sections for the current student
     */
    public function getAllSections()
    {
        $student = Auth::user();
        $studentId = $student->id;

        // Get all sections where the student is enrolled
        $studentClasses = StudentClass::with(['class', 'class.professor'])
            ->where('student_id', $studentId)
            ->get();

        $sections = $studentClasses->map(function ($studentClass) use ($student) {
            $section = $studentClass->class;

            // Get the professor name for the section
            $sectionTeacherName = 'Unknown Teacher';
            if ($section->professor) {
                $sectionTeacherName = $section->professor->name;
            }

            return [
                'id' => (string) $section->id,
                'name' => $section->name,
                'icon' => $section->icon ?? '📚',
                'term' => $section->term ?? 'Unknown Term',
                'teacher' => $sectionTeacherName,
                'room' => $section->room ?? null,
                'studentClassId' => $studentClass->id, // Important for fetching grades
            ];
        });

        return response()->json([
            'classSections' => $sections
        ]);
    }

    /**
     * Fetch assessments for a specific section
     */
    public function getSectionAssessments($sectionId)
    {
        try {
            $student = Auth::user();
            $studentId = $student->id;

            Log::info("Fetching assessments for section {$sectionId} and student {$studentId}");

            // Find the student class record
            $studentClass = StudentClass::where('student_id', $studentId)
                ->where('class_id', $sectionId)
                ->first();

            if (!$studentClass) {
                Log::warning("Student {$studentId} not enrolled in class {$sectionId}");
                return response()->json(['error' => 'Student not enrolled in this class'], 404);
            }

            Log::info("Found student class: " . $studentClass->id);

            // Get assessments for this class
            $assessments = Assessment::where('class_id', $sectionId)->get();

            Log::info("Found " . $assessments->count() . " assessments for class " . $sectionId);

            if ($assessments->isEmpty()) {
                Log::info("No assessments found for class {$sectionId}");
                // Return empty data if no assessments found
                return response()->json([
                    'assessments' => [],
                    'finalGrade' => null
                ]);
            }

            // Log the terms found in assessments
            $terms = $assessments->pluck('term')->unique()->toArray();
            Log::info("Terms found in assessments: " . implode(', ', $terms));

            $mappedAssessments = $assessments->map(function($assessment) use ($studentId, $studentClass) {
                Log::info("Processing assessment: {$assessment->id} - {$assessment->assessment_name}");

                // Get items for this assessment
                $items = $assessment->items;
                Log::info("Found " . $items->count() . " items for assessment " . $assessment->id);

                // Map assessment items with student grades
                $mappedItems = $items->map(function($item) use ($studentId, $studentClass) {
                    // Directly query for the grade since the relationship might not be working
                    $grade = Grade::where('assessment_item_id', $item->id)
                        ->where('student_id', $studentId)
                        ->where('student_class_id', $studentClass->id)
                        ->first();

                    if ($grade) {
                        Log::info("Found grade for item {$item->id}: score = {$grade->score}");
                    } else {
                        Log::info("No grade found for item {$item->id}");
                    }

                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'total_score' => $item->total_score,
                        'score' => $grade ? $grade->score : 0,
                        'icon' => '📝', // You can customize this based on assessment type
                        'iconBg' => 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400',
                    ];
                });

                // Calculate totals
                $totalScore = $mappedItems->sum('score');
                $totalPossible = $mappedItems->sum('total_score');

                return [
                    'id' => $assessment->id,
                    'assessment_name' => $assessment->assessment_name,
                    'term' => $assessment->term,
                    'weight' => $assessment->weight,
                    'items' => $mappedItems,
                    'totalScore' => $totalScore,
                    'totalPossible' => $totalPossible
                ];
            });

            // Get final grades for this student in this class
            $finalGrade = FinalGrade::where('student_id', $studentId)
                ->where('student_class_id', $studentClass->id)
                ->first();

            if ($finalGrade) {
                Log::info("Found final grade for student {$studentId} in class {$sectionId}");
            } else {
                Log::info("No final grade found for student {$studentId} in class {$sectionId}");
            }

            $finalGradeData = $finalGrade ? [
                'prelim' => $finalGrade->prelim,
                'midterm' => $finalGrade->midterm,
                'final_term' => $finalGrade->final_term,
                'final_grade' => $finalGrade->final_grade,
                'final_remarks' => $finalGrade->final_remarks
            ] : null;

            return response()->json([
                'assessments' => $mappedAssessments,
                'finalGrade' => $finalGradeData
            ]);
        } catch (\Exception $e) {
            Log::error("Error fetching assessments: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'An error occurred while fetching assessments'], 500);
        }
    }

    /**
     * Debug method to check data
     */
    public function debug($sectionId)
    {
        try {
            $student = Auth::user();
            $studentId = $student->id;

            // Find the student class record
            $studentClass = StudentClass::where('student_id', $studentId)
                ->where('class_id', $sectionId)
                ->first();

            if (!$studentClass) {
                return response()->json(['error' => 'Student not enrolled in this class'], 404);
            }

            // Check assessments
            $assessments = Assessment::where('class_id', $sectionId)->get();

            $debug = [
                'student_id' => $studentId,
                'section_id' => $sectionId,
                'student_class_id' => $studentClass->id,
                'assessment_count' => $assessments->count(),
                'assessments' => []
            ];

            foreach ($assessments as $assessment) {
                $items = $assessment->items;

                $itemsDebug = [];
                foreach ($items as $item) {
                    $grades = Grade::where('assessment_item_id', $item->id)
                        ->where('student_id', $studentId)
                        ->where('student_class_id', $studentClass->id)
                        ->get();

                    $itemsDebug[] = [
                        'item_id' => $item->id,
                        'name' => $item->name,
                        'total_score' => $item->total_score,
                        'grade_count' => $grades->count(),
                        'grades' => $grades->toArray()
                    ];
                }

                $debug['assessments'][] = [
                    'assessment_id' => $assessment->id,
                    'name' => $assessment->assessment_name,
                    'term' => $assessment->term,
                    'weight' => $assessment->weight,
                    'item_count' => $items->count(),
                    'items' => $itemsDebug
                ];
            }

            return response()->json($debug);
        } catch (\Exception $e) {
            Log::error("Error in debug method: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

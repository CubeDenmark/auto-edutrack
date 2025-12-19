<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\BehaviorReport;
use App\Models\StudentClass;
use App\Models\FinalGrade;
use App\Models\Assessment;
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

    //Add this new method to fetch assessments for a specific section
    public function getSectionAssessments($sectionId)
    {
        $student = Auth::user();
        $studentId = $student->id;

        // Find the student class record
        $studentClass = StudentClass::where('student_id', $studentId)
            ->where('class_id', $sectionId)
            ->first();

        if (!$studentClass) {
            return response()->json(['error' => 'Student not enrolled in this class'], 404);
        }

        // Get assessments for this class
        $assessments = Assessment::where('class_id', $sectionId)
            ->with(['items' => function($query) use ($studentId, $studentClass) {
                $query->with(['grades' => function($q) use ($studentId, $studentClass) {
                    $q->where('student_id', $studentId)
                      ->where('student_class_id', $studentClass->id);
                }]);
            }])
            ->get()
            ->map(function($assessment) use ($studentId, $studentClass) {
                // Map assessment items with student grades
                $items = $assessment->items->map(function($item) use ($studentId, $studentClass) {
                    $grade = $item->grades->first();

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
                $totalScore = $items->sum('score');
                $totalPossible = $items->sum('total_score');

                return [
                    'id' => $assessment->id,
                    'assessment_name' => $assessment->assessment_name,
                    'term' => $assessment->term,
                    'weight' => $assessment->weight,
                    'items' => $items,
                    'totalScore' => $totalScore,
                    'totalPossible' => $totalPossible
                ];
            });

        // Get final grades for this student in this class
        $finalGrade = FinalGrade::where('student_id', $studentId)
            ->where('student_class_id', $studentClass->id)
            ->first();

        $finalGradeData = $finalGrade ? [
            'prelim' => $finalGrade->prelim,
            'midterm' => $finalGrade->midterm,
            'final_term' => $finalGrade->final_term,
            'final_grade' => $finalGrade->final_grade,
            'final_remarks' => $finalGrade->final_remarks
        ] : null;

        return response()->json([
            'assessments' => $assessments,
            'finalGrade' => $finalGradeData
        ]);
    }



}

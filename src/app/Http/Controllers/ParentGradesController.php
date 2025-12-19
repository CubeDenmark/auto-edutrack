<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ClassModel;
use App\Models\StudentClass;
use App\Models\Assessment;
use App\Models\AssessmentItem;
use App\Models\Grade;
use App\Models\FinalGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class ParentGradesController extends Controller
{
    /**
     * Display the grades view for a parent's children
     */
    public function index(Request $request)
    {
        // Get the authenticated parent user
        $parent = Auth::user();

        // Ensure the user is a parent
        if ($parent->user_type !== 'parent') {
            abort(403, 'Only parents can access this page');
        }

        // Get all children linked to this parent
        $children = $parent->children()->with('studentClass.class')->get();

        // Transform children data to match the frontend structure
        $transformedChildren = $this->transformChildrenData($children);

        // Return the Inertia view with the data
        return Inertia::render('Parent/ParentGrades', [
            'initialChildren' => $transformedChildren,
        ]);
    }

    /**
     * Get grades data for a specific child
     */
    public function getChildGrades(Request $request, $childId)
    {
        // Get the authenticated parent user
        $parent = Auth::user();

        // Ensure the user is a parent
        if ($parent->user_type !== 'parent') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if the requested child belongs to this parent
        $child = $parent->children()->where('users.id', $childId)->first();

        if (!$child) {
            return response()->json(['error' => 'Child not found or not linked to parent'], 404);
        }

        // Get the child data with grades
        $childData = $this->transformChildData($child);

        return response()->json([
            'child' => $childData,
        ]);
    }

    /**
     * Transform children data to match the frontend structure
     */
    private function transformChildrenData($children)
    {
        return $children->map(function ($child) {
            return $this->transformChildData($child);
        });
    }
    
    /**
     * Transform a single child's data with grades information
     */
    private function transformChildData($child)
    {
        // Try getting classes from student_classes first
        $studentClasses = $child->studentClasses()->with('class.professor')->get();
    
        // If no student_classes found, fallback: try to get classes via grades/final grades
        if ($studentClasses->isEmpty()) {
            $finalGrades = FinalGrade::where('student_id', $child->id)->get();
    
            $studentClasses = $finalGrades->map(function ($fg) {
                return \App\Models\StudentClass::with('class.professor')->find($fg->student_class_id);
            })->filter(); // Remove nulls in case of missing class
        }
    
        $grades = [];
    
        foreach ($studentClasses as $enrollment) {
            $class = $enrollment->class;
        
            if (!$class) continue;
        
            $finalGrade = FinalGrade::where('student_id', $child->id)
                ->where('student_class_id', $enrollment->id)
                ->first();
        
            $assessmentScoresRaw = Grade::where('student_id', $child->id)
                ->where('student_class_id', $enrollment->id)
                ->with(['assessmentItem.assessment'])
                ->get();
        
            $assessmentScores = [];
        
            $groupedByTerm = $assessmentScoresRaw->groupBy(function ($grade) {
                return $grade->assessmentItem->assessment->term ?? 'Unknown';
            });
        
            foreach ($groupedByTerm as $term => $gradesByTerm) {
                $groupedByType = $gradesByTerm->groupBy(function ($grade) {
                    return $grade->assessmentItem->assessment->assessment_name ?? 'Unknown Type';
                });
        
                $assessmentScores[$term] = $groupedByType->map(function ($items, $type) {
                    return $items->map(function ($grade) {
                        return [
                            'title' => $grade->assessmentItem->name ?? 'Untitled',
                            'score' => $grade->score,
                            'total_score' => $grade->assessmentItem->total_score,
                            'remarks' => $grade->remarks,
                        ];
                    })->values();
                });
            }
        
            $grades[] = [
                'subject' => $class->name,
                'code' => $class->id,
                'icon' =>  $class->icon,
                'avatar' =>  $class->professor->avatar,
                'iconBg' => 'bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400',
                'teacher' => $class->professor?->name ?? 'N/A',
                'terms' => [
                    'prelim' => $finalGrade ? number_format($finalGrade->prelim, 2) : 'N/A',
                    'midterm' => $finalGrade ? number_format($finalGrade->midterm, 2) : 'N/A',
                    'final_term' => $finalGrade ? number_format($finalGrade->final_term, 2) : 'N/A',
                ],
                'final_grade' => $finalGrade ? number_format($finalGrade->final_grade, 2) : 'N/A',
                'gradeClass' => $finalGrade ? $this->getGradeClass((float) $finalGrade->final_grade) : '',
                'remarks' => $finalGrade->final_remarks ?? '',
                'updated' => $finalGrade?->updated_at?->format('M d, Y') ?? '',
                'assessments' => $assessmentScores,
            ];
        }
        
    
        return [
            'id' => $child->id,
            'name' => $child->name,
            'avatar' => $child->avatar,
            'grade' => $studentClasses->first()?->class?->name ?? 'N/A',
            'grades' => $grades
        ];
        
        
    }
    
    
    /**
     * Convert percentage to Philippine grading scale (1.0 to 5.0)
     * Higher percentage = lower numeric grade (better performance)
     */
    private function percentageToPhilippineGrade($percentage)
    {
        if ($percentage >= 96) return 1.0;
        if ($percentage >= 94) return 1.25;
        if ($percentage >= 92) return 1.5;
        if ($percentage >= 90) return 1.75;
        if ($percentage >= 87) return 2.0;
        if ($percentage >= 84) return 2.25;
        if ($percentage >= 81) return 2.5;
        if ($percentage >= 78) return 2.75;
        if ($percentage >= 75) return 3.0;
        if ($percentage >= 72) return 3.25;
        if ($percentage >= 69) return 3.5;
        if ($percentage >= 66) return 3.75;
        if ($percentage >= 63) return 4.0;
        if ($percentage >= 60) return 4.25;
        if ($percentage >= 55) return 4.5;
        if ($percentage >= 50) return 4.75;
        return 5.0;
    }

    /**
     * Convert Philippine grade to percentage for visualization
     */
    private function philippineGradeToPercentage($grade)
    {
        // Convert Philippine grade (1.0-5.0) to percentage (100%-50%)
        // 1.0 = 100%, 5.0 = 50%
        return max(50, min(100, 100 - ((($grade - 1.0) / 4.0) * 50)));
    }

    /**
     * Get CSS class based on Philippine grade
     */
    private function getGradeClass($grade)
    {
        if ($grade <= 1.5) return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'; // Excellent
        if ($grade <= 2.0) return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'; // Very Good
        if ($grade <= 2.5) return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'; // Good
        if ($grade <= 3.0) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'; // Satisfactory
        if ($grade <= 4.0) return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300'; // Passing
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'; // Failing
    }
}

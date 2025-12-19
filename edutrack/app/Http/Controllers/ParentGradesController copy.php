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
        // Get the child's class information
        $studentClass = $child->studentClass;
        $className = $studentClass && $studentClass->class ? $studentClass->class->name : 'N/A';
        $gradeLevel = $studentClass && $studentClass->class ? $studentClass->class->name : 'N/A';

        // Get all classes the student is enrolled in
        $studentClasses = $child->studentClasses()->with('class')->get();

        // Get grades for each class
        $grades = [];
        $totalGradePoints = 0;
        $totalClasses = 0;

        foreach ($studentClasses as $enrollment) {
            if (!$enrollment->class) continue;

            $class = $enrollment->class;

            // Get assessments for this class
            $assessments = Assessment::where('class_id', $class->id)->get();

            // Get final grade if available
            $finalGrade = FinalGrade::where('student_id', $child->id)
                ->where('student_class_id', $enrollment->id)
                ->first();

            // Calculate grade based on final grade or assessments
            $numericGrade = 3.0; // Default to middle grade

            if ($finalGrade) {
                // Make sure we're using the correct scale (1.0-5.0)
                $numericGrade = (float)$finalGrade->final_grade;
                $totalGradePoints += $numericGrade;
            } else {
                // Calculate from individual assessments
                $totalScore = 0;
                $totalWeight = 0;

                foreach ($assessments as $assessment) {
                    $assessmentItems = $assessment->items()->get();
                    $itemScores = 0;
                    $itemMaxScores = 0;

                    foreach ($assessmentItems as $item) {
                        $grade = Grade::where('student_id', $child->id)
                            ->where('assessment_item_id', $item->id)
                            ->first();

                        if ($grade) {
                            $itemScores += $grade->score;
                            $itemMaxScores += $grade->total_score > 0 ? $grade->total_score : $item->total_score;
                        }
                    }

                    if ($itemMaxScores > 0) {
                        $assessmentPercentage = ($itemScores / $itemMaxScores) * 100;
                        // Convert percentage to Philippine grading scale (1.0 to 5.0)
                        $assessmentGrade = $this->percentageToPhilippineGrade($assessmentPercentage);
                        $totalScore += $assessmentGrade * ($assessment->weight / 100);
                        $totalWeight += $assessment->weight;
                    }
                }

                if ($totalWeight > 0) {
                    $numericGrade = $totalScore / $totalWeight;
                    $totalGradePoints += $numericGrade;
                }
            }

            // Update the transformChildData method to handle cases where no assignments or assessments are fetched
            if (empty($assessments) && !$finalGrade) {
                $numericGrade = null; // Set to null when no data is available
            }

            // Calculate percentage equivalent for visualization
            $percentage = $this->philippineGradeToPercentage($numericGrade);

            // Get grade class based on numeric grade
            $gradeClass = $this->getGradeClass($numericGrade);

            // Get assignments/assessments for this class
            $assignments = [];
            foreach ($assessments as $assessment) {
                $assessmentItems = $assessment->items()->get();

                foreach ($assessmentItems as $item) {
                    $grade = Grade::where('student_id', $child->id)
                        ->where('assessment_item_id', $item->id)
                        ->first();

                    if ($grade) {
                        // Calculate Philippine grade equivalent
                        $score = $grade->score;
                        $maxScore = $grade->total_score > 0 ? $grade->total_score : $item->total_score;
                        $percentage = ($score / $maxScore) * 100;
                        $philGrade = $this->percentageToPhilippineGrade($percentage);

                        $assignments[] = [
                            'title' => $item->name,
                            'type' => $assessment->assessment_name,
                            'score' => $score,
                            'maxScore' => $maxScore,
                            'philGrade' => number_format($philGrade, 2), // Format to 2 decimal places
                            'date' => $grade->created_at->format('M d, Y')
                        ];
                    }
                }
            }

            // Get professor name
            $professorName = $class->professor ? $class->professor->name : 'N/A';

            // Create subject/class entry
            $grades[] = [
                'subject' => $class->name,
                'code' => $class->id,
                'icon' => '📓', // Generic icon for all subjects
                'iconBg' => 'bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400', // Generic background for all subjects
                'teacher' => $professorName,
                'numericGrade' => number_format($numericGrade, 2), // Format to 2 decimal places
                'gradeClass' => $gradeClass,
                'percentage' => round($percentage),
                'updated' => Carbon::now()->format('M d, Y'),
                'assignments' => $assignments
            ];
        }

        // Adjust GPA calculation to correctly average grades
        $totalGradePoints = 0;
        $totalClasses = 0;

        foreach ($studentClasses as $enrollment) {
            if (!$enrollment->class) continue;

            $class = $enrollment->class;

            // Get final grade if available
            $finalGrade = FinalGrade::where('student_id', $child->id)
                ->where('student_class_id', $enrollment->id)
                ->first();

            if ($finalGrade) {
                $numericGrade = (float)$finalGrade->final_grade;
                $totalGradePoints += $numericGrade;
                $totalClasses++;
            } else {
                // Calculate from individual assessments
                $totalScore = 0;
                $totalWeight = 0;

                $assessments = Assessment::where('class_id', $class->id)->get();
                foreach ($assessments as $assessment) {
                    $assessmentItems = $assessment->items()->get();
                    $itemScores = 0;
                    $itemMaxScores = 0;

                    foreach ($assessmentItems as $item) {
                        $grade = Grade::where('student_id', $child->id)
                            ->where('assessment_item_id', $item->id)
                            ->first();

                        if ($grade) {
                            $itemScores += $grade->score;
                            $itemMaxScores += $grade->total_score > 0 ? $grade->total_score : $item->total_score;
                        }
                    }

                    if ($itemMaxScores > 0) {
                        $assessmentPercentage = ($itemScores / $itemMaxScores) * 100;
                        $assessmentGrade = $this->percentageToPhilippineGrade($assessmentPercentage);
                        $totalScore += $assessmentGrade * ($assessment->weight / 100);
                        $totalWeight += $assessment->weight;
                    }
                }

                if ($totalWeight > 0) {
                    $numericGrade = $totalScore / $totalWeight;
                    $totalGradePoints += $numericGrade;
                    $totalClasses++;
                }
            }
        }

        // Calculate GPA only if there are valid classes
        $gpa = $totalClasses > 0 ? $totalGradePoints / $totalClasses : null;

        // Modify the transformChildData method to handle cases where no grades are fetched
        if (empty($grades)) {
            $grades[] = [
                'subject' => 'No Data',
                'code' => '',
                'icon' => '',
                'iconBg' => '',
                'teacher' => '',
                'numericGrade' => '',
                'gradeClass' => '',
                'percentage' => '',
                'updated' => '',
                'assignments' => []
            ];
        }

        // Ensure GPA is blank if no grades are available
        $gpa = $totalClasses > 0 ? $totalGradePoints / $totalClasses : null;

        return [
            'id' => $child->id,
            'name' => $child->name,
            'grade' => $gradeLevel,
            'avatar' => 'https://i.pravatar.cc/150?img=' . ($child->id % 10), // Placeholder avatar
            'stats' => [
                'gpa' => number_format($gpa, 2) // Format to 2 decimal places
            ],
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

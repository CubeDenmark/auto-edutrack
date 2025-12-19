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

        if (!child) {
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
            $totalClasses++;

            // Get assessments for this class
            $assessments = Assessment::where('class_id', $class->id)->get();

            // Get final grade if available
            $finalGrade = FinalGrade::where('student_id', $child->id)
                ->where('student_class_id', $enrollment->id)
                ->first();

            // Calculate percentage based on final grade or assessments
            $percentage = 0;
            $letterGrade = 'N/A';
            $gradeClass = 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';

            if ($finalGrade) {
                $percentage = $finalGrade->final_grade;
                $totalGradePoints += $this->calculateGradePoints($percentage);
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
                        $totalScore += $assessmentPercentage * ($assessment->weight / 100);
                        $totalWeight += $assessment->weight;
                    }
                }

                if ($totalWeight > 0) {
                    $percentage = ($totalScore / $totalWeight) * 100;
                    $totalGradePoints += $this->calculateGradePoints($percentage);
                }
            }

            // Determine letter grade and class based on percentage
            list($letterGrade, $gradeClass) = $this->determineLetterGrade($percentage);

            // Get assignments/assessments for this class
            $assignments = [];
            foreach ($assessments as $assessment) {
                $assessmentItems = $assessment->items()->get();

                foreach ($assessmentItems as $item) {
                    $grade = Grade::where('student_id', $child->id)
                        ->where('assessment_item_id', $item->id)
                        ->first();

                    if ($grade) {
                        $assignments[] = [
                            'title' => $item->name,
                            'type' => $assessment->assessment_name,
                            'score' => $grade->score,
                            'maxScore' => $grade->total_score > 0 ? $grade->total_score : $item->total_score,
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
                'icon' => $this->getSubjectIcon($class->name),
                'iconBg' => $this->getSubjectIconBg($class->name),
                'teacher' => $professorName,
                'grade' => $letterGrade,
                'gradeClass' => $gradeClass,
                'percentage' => round($percentage),
                'updated' => Carbon::now()->format('M d, Y'),
                'assignments' => $assignments
            ];
        }

        // Calculate GPA
        $gpa = $totalClasses > 0 ? round($totalGradePoints / $totalClasses, 1) : 0;

        return [
            'id' => $child->id,
            'name' => $child->name,
            'grade' => $gradeLevel,
            'avatar' => 'https://i.pravatar.cc/150?img=' . ($child->id % 10), // Placeholder avatar
            'stats' => [
                'gpa' => $gpa
            ],
            'grades' => $grades
        ];
    }

    /**
     * Calculate grade points based on percentage
     */
    private function calculateGradePoints($percentage)
    {
        if ($percentage >= 93) return 4.0;
        if ($percentage >= 90) return 3.7;
        if ($percentage >= 87) return 3.3;
        if ($percentage >= 83) return 3.0;
        if ($percentage >= 80) return 2.7;
        if ($percentage >= 77) return 2.3;
        if ($percentage >= 73) return 2.0;
        if ($percentage >= 70) return 1.7;
        if ($percentage >= 67) return 1.3;
        if ($percentage >= 63) return 1.0;
        if ($percentage >= 60) return 0.7;
        return 0.0;
    }

    /**
     * Determine letter grade and CSS class based on percentage
     */
    private function determineLetterGrade($percentage)
    {
        if ($percentage >= 93) return ['A', 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'];
        if ($percentage >= 90) return ['A-', 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'];
        if ($percentage >= 87) return ['B+', 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'];
        if ($percentage >= 83) return ['B', 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'];
        if ($percentage >= 80) return ['B-', 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'];
        if ($percentage >= 77) return ['C+', 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'];
        if ($percentage >= 73) return ['C', 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'];
        if ($percentage >= 70) return ['C-', 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'];
        if ($percentage >= 67) return ['D+', 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300'];
        if ($percentage >= 63) return ['D', 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300'];
        if ($percentage >= 60) return ['D-', 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300'];
        return ['F', 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'];
    }

    /**
     * Get icon for a subject based on its name
     */
    private function getSubjectIcon($subjectName)
    {
        $subjectName = strtolower($subjectName);

        if (strpos($subjectName, 'math') !== false) return '📐';
        if (strpos($subjectName, 'science') !== false) return '🔬';
        if (strpos($subjectName, 'english') !== false) return '📚';
        if (strpos($subjectName, 'history') !== false) return '🏛️';
        if (strpos($subjectName, 'physical') !== false) return '🏀';
        if (strpos($subjectName, 'art') !== false) return '🎨';
        if (strpos($subjectName, 'music') !== false) return '🎵';
        if (strpos($subjectName, 'computer') !== false) return '💻';
        if (strpos($subjectName, 'language') !== false) return '🗣️';

        return '📓';
    }

    /**
     * Get icon background class for a subject based on its name
     */
    private function getSubjectIconBg($subjectName)
    {
        $subjectName = strtolower($subjectName);

        if (strpos($subjectName, 'math') !== false) return 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400';
        if (strpos($subjectName, 'science') !== false) return 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400';
        if (strpos($subjectName, 'english') !== false) return 'bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-400';
        if (strpos($subjectName, 'history') !== false) return 'bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400';
        if (strpos($subjectName, 'physical') !== false) return 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400';
        if (strpos($subjectName, 'art') !== false) return 'bg-pink-100 dark:bg-pink-900 text-pink-600 dark:text-pink-400';
        if (strpos($subjectName, 'music') !== false) return 'bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400';
        if (strpos($subjectName, 'computer') !== false) return 'bg-cyan-100 dark:bg-cyan-900 text-cyan-600 dark:text-cyan-400';
        if (strpos($subjectName, 'language') !== false) return 'bg-orange-100 dark:bg-orange-900 text-orange-600 dark:text-orange-400';

        return 'bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400';
    }
}

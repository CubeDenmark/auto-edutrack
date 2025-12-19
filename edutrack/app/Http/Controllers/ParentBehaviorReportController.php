<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\BehaviorReport;
use App\Models\ParentStudent;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ParentBehaviorReportController extends Controller
{
    /**
     * Display behavior reports for a parent's children
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        // Get the authenticated parent user
        $parent = Auth::user();

        // Get all children of the parent
        $childrenIds = ParentStudent::where('parent_id', $parent->id)
            ->pluck('student_id');

        $children = User::whereIn('id', $childrenIds)
            ->get()
            ->map(function ($child) {
                // Get behavior reports for this child
                $behaviorReports = BehaviorReport::with(['professor', 'classSection'])
                    ->where('student_id', $child->id)
                    ->orderBy('created_at', 'desc') // Use created_at if date is not available
                    ->get()
                    ->map(function ($report) {
                        // Determine icon and styling based on report type
                        $iconData = $this->getReportIconData($report->type ?? 'Positive');

                        // Format the date (handle both string and Carbon dates)
                        $date = $report->date;
                        if ($date instanceof Carbon) {
                            $formattedDate = $date->format('M d, Y');
                        } elseif (is_string($date)) {
                            $formattedDate = $date;
                        } else {
                            $formattedDate = Carbon::now()->format('M d, Y');
                        }

                        return [
                            'id' => $report->id,
                            'icon' => $iconData['icon'],
                            'iconBg' => $iconData['iconBg'],
                            'iconColor' => $iconData['iconColor'],
                            'title' => $report->title ?? 'Behavior Report',
                            'type' => $report->type ?? 'Positive',
                            'typeClass' => $iconData['typeClass'],
                            'description' => $report->comment ?? '',
                            'teacher' => $report->professor ? $report->professor->name : 'Teacher',
                            'date' => $formattedDate
                        ];
                    });

                // Count report types for statistics
                $positiveCount = $behaviorReports->where('type', 'positive')->count();
                $warningCount = $behaviorReports->where('type', 'warning')->count();
                $incidentCount = $behaviorReports->where('type', 'incident')->count();

                // Determine overall behavior status based on report counts
                $behaviorStatus = $this->calculateBehaviorStatus($positiveCount, $warningCount, $incidentCount);

                // Get student class information
                $studentClass = StudentClass::where('student_id', $child->id)->first();
                $className = null;

                if ($studentClass && $studentClass->class) {
                    $className = $studentClass->class->name;
                }

                $grade = $className ?? 'No Class yet '; // Fallback to a random grade if class name not available

                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'avatar' => $child->avatar,
                    'grade' => $grade,
        
                    'stats' => [
                        'gpa' => rand(25, 40) / 10, // Random GPA between 2.5 and 4.0
                        'attendance' => rand(80, 100), // Random attendance percentage
                        'behavior' => $behaviorStatus
                    ],
                    'behaviorReports' => $behaviorReports->toArray()
                ];
            });

        // Get the current child ID from the request if any
        $currentChildId = $request->query('childId') ?? $request->query('itemId') ?? $request->query('sectionId');

        // If we're using Inertia, render the page with the data
        return Inertia::render('Parent/ParentBehaviorReports', [
            'children' => $children,
            'childId' => $currentChildId
        ]);
    }

    /**
     * Helper method to determine icon and styling based on report type
     *
     * @param string $type
     * @return array
     */
    private function getReportIconData($type)
    {
        switch ($type) {
            case 'positive':
                return [
                    'icon' => '🌟',
                    'iconBg' => 'bg-green-100 dark:bg-green-900',
                    'iconColor' => 'text-green-600 dark:text-green-400',
                    'typeClass' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                ];
            case 'warning':
                return [
                    'icon' => '⚠️',
                    'iconBg' => 'bg-yellow-100 dark:bg-yellow-900',
                    'iconColor' => 'text-yellow-600 dark:text-yellow-400',
                    'typeClass' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
                ];
            case 'incident':
                return [
                    'icon' => '❌',
                    'iconBg' => 'bg-red-100 dark:bg-red-900',
                    'iconColor' => 'text-red-600 dark:text-red-400',
                    'typeClass' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
                ];
            default:
                return [
                    'icon' => '👏',
                    'iconBg' => 'bg-purple-100 dark:bg-purple-900',
                    'iconColor' => 'text-purple-600 dark:text-purple-400',
                    'typeClass' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                ];
        }
    }

    /**
     * Helper method to calculate behavior status based on report counts
     *
     * @param int $positiveCount
     * @param int $warningCount
     * @param int $incidentCount
     * @return string
     */
    private function calculateBehaviorStatus($positiveCount, $warningCount, $incidentCount)
    {
        // Simple algorithm to determine behavior status
        if ($incidentCount > 0) {
            return 'Needs Improvement';
        } elseif ($warningCount > $positiveCount) {
            return 'Fair';
        } elseif ($positiveCount > 0 && $warningCount == 0) {
            return 'Excellent';
        } else {
            return 'Good';
        }
    }
}

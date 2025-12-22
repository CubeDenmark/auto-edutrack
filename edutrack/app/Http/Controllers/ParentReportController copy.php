<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;
use App\Models\ParentStudent;

class ParentReportControllerdsa extends Controller
{
    public function index()
    {
        $parentId = Auth::id();

        // Get all students (wards) associated with this parent
        $parentStudents = ParentStudent::where('parent_id', $parentId)
            ->with(['student' => function ($query) {
                $query->with(['behaviorReports.professor', 'classes.section']);
            }])
            ->get();

        $wards = $parentStudents->map(function ($parentStudent) {
            $student = $parentStudent->student;

            if (!$student) {
                return null;
            }

            // Get all behavior reports for this student
            $behaviorReports = $student->behaviorReports ? $student->behaviorReports->map(function ($report) {
                return [
                    'id' => $report->id,
                    'student' => $report->student->name ?? 'Unknown',
                    'icon' => $this->getIcon($report->type),
                    'iconBg' => $this->getIconBg($report->type),
                    'iconColor' => $this->getIconColor($report->type),
                    'title' => $this->getReportTitle($report->type),
                    'type' => ucfirst($report->type),
                    'typeClass' => $this->getTypeClass($report->type),
                    'description' => $report->comment,
                    'teacher' => $report->professor->name ?? 'Unknown Teacher',
                    'date' => \Carbon\Carbon::parse($report->created_at)->format('M j, Y'),
                    'date_raw' => $report->created_at, // Use created_at for sorting
                ];
            }) : collect([]);

            // Sort behavior reports in descending order by created_at
            $behaviorReports = $behaviorReports->sortByDesc('date_raw')->values()->toArray();

            // Calculate behavior summary
            $positive = $behaviorReports ? collect($behaviorReports)->where('type', 'Positive')->count() : 0;
            $warnings = $behaviorReports ? collect($behaviorReports)->where('type', 'Warning')->count() : 0;
            $incidents = $behaviorReports ? collect($behaviorReports)->where('type', 'Incident')->count() : 0;

            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'grade' => $student->grade ?? 'Not specified',
                'avatar' => $student->avatar ?? 'https://i.pravatar.cc/150?img=' . ($student->id % 10),
                'stats' => [
                    'gpa' => $student->gpa ?? 'N/A',
                    'attendance' => $student->attendance_percentage ?? 'N/A',
                    'behavior' => $this->getBehaviorStatus($positive, $warnings, $incidents),
                ],
                'behaviorReports' => $behaviorReports,
                'behaviorSummary' => [
                    'positive' => $positive,
                    'warnings' => $warnings,
                    'incidents' => $incidents,
                ],
            ];
        })->filter()->values(); // Remove any null values and reindex

        // Format data for sidebar
        $sidebarItems = $wards->map(function ($ward) {
            return [
                'id' => $ward['id'],
                'name' => $ward['name'],
                'type' => 'ward',
                'icon' => '👤', // You can customize this
                'count' => $ward['behaviorReports'] ? count($ward['behaviorReports']) : 0
            ];
        })->toArray();

        return Inertia::render('Parent/ParentBehaviorReports', [
            'wards' => $wards,
            'sidebarItems' => $sidebarItems, // Pass sidebar items for the AppSidebar component
        ]);
    }

    private function getIcon($type)
    {
        return match (strtolower($type)) {
            'positive' => '🌟',
            'warning' => '⚠️',
            'incident' => '❌',
            default => '📌',
        };
    }

    private function getIconBg($type)
    {
        return match (strtolower($type)) {
            'positive' => 'bg-green-100 dark:bg-green-900',
            'warning' => 'bg-yellow-100 dark:bg-yellow-900',
            'incident' => 'bg-red-100 dark:bg-red-900',
            default => 'bg-gray-100 dark:bg-gray-900',
        };
    }

    private function getIconColor($type)
    {
        return match (strtolower($type)) {
            'positive' => 'text-green-600 dark:text-green-400',
            'warning' => 'text-yellow-600 dark:text-yellow-400',
            'incident' => 'text-red-600 dark:text-red-400',
            default => 'text-gray-600 dark:text-gray-400',
        };
    }

    private function getTypeClass($type)
    {
        return match (strtolower($type)) {
            'positive' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'incident' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
        };
    }

    private function getReportTitle($type)
    {
        return match (strtolower($type)) {
            'positive' => 'Positive Behavior',
            'warning' => 'Behavior Warning',
            'incident' => 'Behavior Incident',
            default => 'Behavior Note',
        };
    }

    private function getBehaviorStatus($positive, $warnings, $incidents)
    {
        if ($incidents > 0) {
            return 'Needs Improvement';
        } elseif ($warnings > 0) {
            return 'Good';
        } else {
            return 'Excellent';
        }
    }
}

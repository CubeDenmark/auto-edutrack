<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\BehaviorReport;
use App\Models\StudentClass;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StudentBehaviorController extends Controller
{
    public function index()
    {
        $student = Auth::user();
        $studentId = $student->id;

        // Get all sections where the student is enrolled
        $studentClasses = StudentClass::with(['class', 'class.professor']) // Also load the professor for each class
            ->where('student_id', $studentId)
            ->get();

        $sections = $studentClasses->map(function ($studentClass) use ($student) {
            $section = $studentClass->class;

            // Get the professor name for the section
            $sectionTeacherName = 'Unknown Teacher';
            if ($section->professor) {
                $sectionTeacherName = $section->professor->name;
            }

            // Get all reports for this student in this section
            $reports = BehaviorReport::where('class_id', $section->id)
                ->where('student_id', $student->id)
                ->with('professor') // Eager load the professor relationship
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($report) use ($student) {
                    // Get professor name from the relationship
                    $professorName = $report->professor ? $report->professor->name : 'Unknown';

                    // Format the date from the date field, not created_at
                    $formattedDate = null;
                    if ($report->date) {
                        // Parse the date from the database format and format it
                        $formattedDate = Carbon::parse($report->date)->format('M j, Y');
                    } elseif ($report->created_at) {
                        // Fallback to created_at if date is not available
                        $formattedDate = $report->created_at->format('M j, Y');
                    }

                    return [
                        'student' => $student->name,
                        'icon' => $this->getIcon($report->type),
                        'iconBg' => $this->getIconBg($report->type),
                        'iconColor' => $this->getIconColor($report->type),
                        'type' => $report->type,
                        'typeClass' => $this->getTypeClass($report->type),
                        'description' => $report->comment,
                        'title' => $report->title ?? 'Behavior Report',
                        'teacher' => $professorName, // Keep this for reference but we won't display it in the card
                        'date' => $formattedDate,
                    ];
                });

            return [
                'id' => (string) $section->id,
                'name' => $section->name,
                'icon' => $section->icon ?? '📚',
                'term' => $section->term ?? 'Unknown Term',
                'teacher' => $sectionTeacherName, // Use the properly fetched teacher name
                'studentCount' => 1, // Only the logged-in student
                'behaviorSummary' => [
                    'positive' => $reports->where('type', 'positive')->count(),
                    'warnings' => $reports->where('type', 'warning')->count(),
                    'incidents' => $reports->where('type', 'incident')->count(),
                ],
                'students' => [
                    [
                        'id' => (string) $student->id,
                        'name' => $student->name,
                        'email' => $student->email,
                    ]
                ],
                'behaviorReports' => $reports,
            ];
        });

        return Inertia::render('Student/StudentBehaviorReports', [
            'classSections' => $sections,
        ]);
    }

    // Helper functions remain the same
    private function getIcon($type)
    {
        return match ($type) {
            'positive' => '🌟',
            'warning' => '⚠️',
            'incident' => '🚨',
            default => '❓',
        };
    }

    private function getIconBg($type)
    {
        return match ($type) {
            'positive' => 'bg-green-100 dark:bg-green-900',
            'warning' => 'bg-yellow-100 dark:bg-yellow-900',
            'incident' => 'bg-red-100 dark:bg-red-900',
            default => 'bg-gray-100 dark:bg-gray-900',
        };
    }

    private function getIconColor($type)
    {
        return match ($type) {
            'positive' => 'text-green-600 dark:text-green-400',
            'warning' => 'text-yellow-600 dark:text-yellow-400',
            'incident' => 'text-red-600 dark:text-red-400',
            default => 'text-gray-600 dark:text-gray-400',
        };
    }

    private function getTypeClass($type)
    {
        return match ($type) {
            'positive' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'incident' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
        };
    }
}

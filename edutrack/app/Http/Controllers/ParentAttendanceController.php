<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Models\ClassModel;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class ParentAttendanceController extends Controller
{
    /**
     * Display the attendance view for a parent's children
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

        // Get upcoming events (this would come from your events table)
        $upcomingEvents = $this->getUpcomingEvents();

        // Return the Inertia view with the data
        return Inertia::render('Parent/ParentAttendance', [
            'initialChildren' => $transformedChildren,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }

    /**
     * Get attendance data for a specific child
     */
    public function getChildAttendance(Request $request, $childId)
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

        // Get the month and year from the request, default to current month
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        // Get attendance records for this child
        $attendanceRecords = $this->getAttendanceRecords($child, $month, $year);

        // Get attendance summary for this child
        $attendanceSummary = $this->getAttendanceSummary($child);

        // Get monthly attendance trends
        $monthlyAttendance = $this->getMonthlyAttendance($child);

        // Get child data with stats
        $childData = $this->transformChildData($child);

        // Important: Make sure attendanceRecords is included in the response
        return response()->json([
            'child' => $childData,
            'attendanceRecords' => $attendanceRecords,
            'attendanceSummary' => $attendanceSummary,
            'monthlyAttendance' => $monthlyAttendance
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
     * Transform a single child's data
     */
    private function transformChildData($child)
    {
        // Get the child's class information
        $studentClass = $child->studentClass;
        $className = $studentClass && $studentClass->class ? $studentClass->class->name : 'N/A';

        // Calculate attendance rate
        $attendanceSummary = $this->getAttendanceSummary($child);
        $attendanceRate = 0;

        if ($attendanceSummary['total'] > 0) {
            $attendanceRate = round(($attendanceSummary['present'] / $attendanceSummary['total']) * 100);
        }

        return [
            'id' => $child->id,
            'name' => $child->name,
            'avatar' => $child->avatar,
            'grade' => $className,
            'stats' => [
                'attendance' => $attendanceRate
            ],
            'attendanceSummary' => $attendanceSummary,
            // Initialize with empty arrays - will be populated by getChildAttendance
            'attendanceRecords' => [],
            'monthlyAttendance' => []
        ];
    }

    /**
     * Get attendance records for a specific child in a given month/year
     */
    private function getAttendanceRecords($child, $month, $year)
    {
        // Get the student's classes
        $studentClasses = StudentClass::where('student_id', $child->id)->pluck('class_id')->toArray();

        // If no classes are found, return empty array
        if (empty($studentClasses)) {
            \Log::warning("No classes found for student {$child->id}");
            return [];
        }

        // Get attendance records for this month
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');

        \Log::info("Fetching attendance for student {$child->id} from {$startDate} to {$endDate}");

        $records = Attendance::where('student_id', $child->id)
            ->whereIn('class_id', $studentClasses)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();

        \Log::info("Found {$records->count()} attendance records for child {$child->id} in {$month}/{$year}");

        // If no records, return empty array - REMOVED dummy data generation
        if ($records->count() === 0) {
            return [];
        }

        return $records->map(function ($record) {
            // Get class name
            $className = 'All Classes';
            if ($record->class) {
                $className = $record->class->name;
            }

            // Ensure date is properly formatted
            $date = $record->date;
            if (!is_string($date)) {
                $date = $date->format('Y-m-d');
            }

            return [
                'date' => $date,
                'status' => $record->status,
                'class' => $className,
                'notes' => $record->notes ?? ''
            ];
        })->toArray();
    }

    /**
     * Get attendance summary for a child
     */
    private function getAttendanceSummary($child)
    {
        // Get all attendance records for this child
        $records = Attendance::where('student_id', $child->id)->get();

        // Initialize counters
        $present = 0;
        $absent = 0;
        $late = 0;
        $excused = 0;

        foreach ($records as $record) {
            switch ($record->status) {
                case 'present':
                    $present++;
                    break;
                case 'absent':
                    $absent++;
                    break;
                case 'late':
                    $late++;
                    break;
                case 'excused':
                    $excused++;
                    break;
            }
        }

        $total = $present + $absent + $late + $excused;

        // Return actual counts, even if zero
        return [
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'excused' => $excused,
            'total' => $total
        ];
    }

    /**
     * Get monthly attendance trends for a child
     */
    private function getMonthlyAttendance($child)
    {
        // Get the current school year's start and end dates
        // This is an example - adjust based on your school year definition
        $currentYear = Carbon::now()->year;
        $schoolYearStart = Carbon::createFromDate($currentYear, 8, 1); // August 1st

        if (Carbon::now()->month < 8) {
            $schoolYearStart = Carbon::createFromDate($currentYear - 1, 8, 1);
        }

        $schoolYearEnd = Carbon::createFromDate($schoolYearStart->year + 1, 7, 31);

        // Get all months in the school year
        $months = [];
        $currentMonth = $schoolYearStart->copy();

        while ($currentMonth->lte($schoolYearEnd) && $currentMonth->lte(Carbon::now())) {
            $monthName = $currentMonth->format('F');
            $monthNumber = $currentMonth->month;
            $year = $currentMonth->year;

            // Get attendance for this month
            $startDate = $currentMonth->copy()->startOfMonth();
            $endDate = $currentMonth->copy()->endOfMonth();

            $records = Attendance::where('student_id', $child->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            // Count statuses
            $present = $records->where('status', 'present')->count();
            $absent = $records->where('status', 'absent')->count();
            $late = $records->where('status', 'late')->count();
            $excused = $records->where('status', 'excused')->count();
            $total = $present + $absent + $late + $excused;

            // Only add months that have actual attendance records
            if ($total > 0) {
                $months[] = [
                    'month' => $monthName,
                    'present' => $present,
                    'absent' => $absent,
                    'late' => $late,
                    'excused' => $excused,
                    'total' => $total
                ];
            }

            // Move to next month
            $currentMonth->addMonth();
        }

        return $months;
    }

    /**
     * Get upcoming events
     */
    private function getUpcomingEvents()
    {
        // This would typically come from your events table
        // For now, returning static data similar to the frontend
        return [
            [
                'title' => 'Parent-Teacher Conference',
                'date' => Carbon::now()->addDays(5)->format('M d, Y')
            ],
            [
                'title' => 'School Holiday',
                'date' => Carbon::now()->addDays(10)->format('M d, Y')
            ],
            [
                'title' => 'End of Term',
                'date' => Carbon::now()->addDays(30)->format('M d, Y')
            ]
        ];
    }
}

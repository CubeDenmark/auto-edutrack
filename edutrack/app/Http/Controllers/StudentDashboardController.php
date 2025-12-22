<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ClassModel;
use App\Models\StudentClass;
use App\Models\Assessment;
use App\Models\AssessmentItem;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\BehaviorReport;
use App\Models\FinalGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class StudentDashboardController extends Controller
{
    /**
     * Display the student dashboard
     */
    public function index(Request $request)
    {
        $student = Auth::user();
    
        if ($student->user_type !== 'student') {
            abort(403, 'Only students can access this page');
        }
    
        $sectionId = $request->input('sectionId');
        $itemId = $request->input('itemId');
        $selectedSectionId = $sectionId ?? $itemId;
    
        $studentInfo = $this->getStudentInfo($student);
        $classSections = $this->getClassSections($student->id);
        $academicPerformance = $this->getAcademicPerformance($student->id);
        $recentActivities = $this->getRecentActivities($student->id);
    
        // Fetch classes with terms
        $studentClasses = StudentClass::where('student_id', $student->id)
            ->with('class')
            ->get();
    
        // Extract unique terms from classes
        $terms = $studentClasses->pluck('class.term')->filter()->unique()->values();
    
        return Inertia::render('Student/StudentDashboard', [
            'currentStudent' => $studentInfo,
            'classSections' => $classSections,
            'academicPerformance' => $academicPerformance,
            'recentActivities' => $recentActivities,
            'sectionId' => $selectedSectionId,
            'classTerms' => $terms, // Pass the terms array here
        ]);
    }

    public function getAvailableTerms()
    {
        $student = Auth::user();

        $terms = StudentClass::where('student_id', $student->id)
            ->with('class')
            ->get()
            ->pluck('class.term')
            ->unique()
            ->filter()
            ->values();

        return response()->json([
            'terms' => $terms
        ]);
    }

    /**
     * Get student's classes for the sidebar
     */
    public function getClasses()
    {
        $student = Auth::user();

        // Get all classes the student is enrolled in
        $studentClasses = StudentClass::where('student_id', $student->id)
            ->with(['class', 'class.professor'])
            ->get();

        $classes = $studentClasses->map(function ($enrollment) {
            $class = $enrollment->class;

            if (!$class) {
                return null;
            }

            // Get teacher name if available
            $teacherName = 'Unknown Teacher';
            if ($class->professor) {
                $teacherName = $class->professor->name;
            }

            return [
                'id' => $class->id,
                'name' => $class->name,
                'icon' => $this->getSubjectIcon($class->name),
                'term' => $class->term ?? 'Current Term',
                'teacher' => $teacherName,
                'room' => $class->room ?? 'TBD'
            ];
        })->filter()->values();

        return response()->json(['classes' => $classes]);
    }

    /**
     * Get student information
     */
    private function getStudentInfo($student)
    {
        // Get attendance statistics
        $attendanceStats = $this->getAttendanceStats($student->id);

        // Get behavior status
        $behaviorData = $this->getBehaviorData($student->id);

        // Get GPA
        $gpa = $this->calculateGPA($student->id);

        return [
            'id' => $student->id,
            'name' => $student->name,
            'email' => $student->email,
            'grade' => $this->getStudentGradeLevel($student->id),
            'gpa' => $gpa,
            'behaviorStatus' => $behaviorData['status'],
            'behaviorReports' => $behaviorData['reportCount'],
            'attendanceRate' => $attendanceStats['rate'],
            'presentDays' => $attendanceStats['present'],
            'totalDays' => $attendanceStats['total']
        ];
    }

    /**
     * Get student's grade level
     */
    private function getStudentGradeLevel($studentId)
    {
        // Get the student's class enrollment to determine grade level
        $studentClass = StudentClass::where('student_id', $studentId)
            ->with('class')
            ->first();

        if ($studentClass && $studentClass->class) {
            return $studentClass->class->name;
        }

        return 'N/A';
    }

    /**
     * Get attendance statistics for a student
     */
    private function getAttendanceStats($studentId)
    {
        // Get attendance records for the current school year
        $attendanceRecords = Attendance::where('student_id', $studentId)
            ->whereDate('date', '>=', Carbon::now()->subMonths(4))
            ->get();

        $totalDays = $attendanceRecords->count();

        if ($totalDays === 0) {
            return [
                'rate' => 100,
                'present' => 0,
                'total' => 0
            ];
        }

        $presentDays = $attendanceRecords->where('status', 'present')->count();
        $attendanceRate = round(($presentDays / $totalDays) * 100);

        return [
            'rate' => $attendanceRate,
            'present' => $presentDays,
            'total' => $totalDays
        ];
    }

    /**
     * Get behavior data for a student
     */
    private function getBehaviorData($studentId)
    {
        // Get behavior reports for the student
        $behaviorReports = BehaviorReport::where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get();

        $reportCount = $behaviorReports->count();

        if ($reportCount === 0) {
            return [
                'status' => 'Good',
                'reportCount' => 0
            ];
        }

        // Count positive and negative reports
        $positiveReports = $behaviorReports->where('type', 'positive')->count();
        $negativeReports = $behaviorReports->where('type', 'negative')->count();

        // Determine behavior status
        $status = 'Good'; // Default
        if ($reportCount > 0) {
            $positiveRatio = $positiveReports / $reportCount;
            if ($positiveRatio >= 0.8) {
                $status = 'Excellent';
            } elseif ($positiveRatio >= 0.6) {
                $status = 'Good';
            } elseif ($positiveRatio >= 0.4) {
                $status = 'Fair';
            } else {
                $status = 'Needs Improvement';
            }
        }

        return [
            'status' => $status,
            'reportCount' => $negativeReports
        ];
    }

    /**
     * Calculate GPA for a student
     */
    // private function calculateGPA($studentId)
    // {
    //     // Get all classes the student is enrolled in
    //     $studentClasses = StudentClass::where('student_id', $studentId)->get();

    //     if ($studentClasses->isEmpty()) {
    //         return 1.75; // Default GPA if no classes found (Philippines scale)
    //     }

    //     $totalGradePoints = 0;
    //     $totalClasses = 0;

    //     foreach ($studentClasses as $enrollment) {
    //         // Get all assessments for this class
    //         $assessments = Assessment::where('class_id', $enrollment->class_id)->get();

    //         if ($assessments->isEmpty()) {
    //             continue;
    //         }

    //         $classScore = 0;
    //         $totalWeight = 0;

    //         foreach ($assessments as $assessment) {
    //             $assessmentItems = $assessment->items;
    //             $itemScores = 0;
    //             $itemMaxScores = 0;

    //             foreach ($assessmentItems as $item) {
    //                 $grade = Grade::where('student_id', $studentId)
    //                     ->where('assessment_item_id', $item->id)
    //                     ->first();

    //                 if ($grade) {
    //                     $itemScores += $grade->score;
    //                     $itemMaxScores += $grade->total_score > 0 ? $grade->total_score : $item->total_score;
    //                 }
    //             }

    //             if ($itemMaxScores > 0) {
    //                 $assessmentPercentage = ($itemScores / $itemMaxScores) * 100;
    //                 $classScore += $assessmentPercentage * ($assessment->weight / 100);
    //                 $totalWeight += $assessment->weight;
    //             }
    //         }

    //         if ($totalWeight > 0) {
    //             $classPercentage = $classScore / $totalWeight;
    //             // Convert percentage to Philippines GPA (1.0-5.0)
    //             $gradePoint = $this->percentageToPhGPA($classPercentage);
    //             $totalGradePoints += $gradePoint;
    //             $totalClasses++;
    //         }
    //     }

    //     // Calculate GPA
    //     return $totalClasses > 0 ? number_format($totalGradePoints / $totalClasses, 2) : 1.75;
    // }

    private function calculateGPA($studentId)
{
    // Get all classes the student is enrolled in
    $studentClasses = StudentClass::where('student_id', $studentId)->get();

    if ($studentClasses->isEmpty()) {
        return 'N/A'; // No classes found
    }

    $totalFinalGrades = 0;
    $totalClasses = 0;

    foreach ($studentClasses as $enrollment) {
        // Get the final grade using student_class_id
        $finalGrade = FinalGrade::where('student_class_id', $enrollment->id)->first();

        if ($finalGrade && $finalGrade->final_grade !== null) {
            $totalFinalGrades += $finalGrade->final_grade;
            $totalClasses++;
        }
    }

    if ($totalClasses === 0) {
        return 'N/A'; // No valid grades
    }

    // Return the raw average of final grades
    return number_format($totalFinalGrades / $totalClasses, 2);
}

    


    /**
     * Convert percentage to Philippines GPA (1.0-5.0 scale where 1.0 is highest)
     */
    private function percentageToPhGPA($percentage)
    {
        if ($percentage >= 96) return 1.0;
        if ($percentage >= 94) return 1.25;
        if ($percentage >= 92) return 1.5;
        if ($percentage >= 89) return 1.75;
        if ($percentage >= 87) return 2.0;
        if ($percentage >= 84) return 2.25;
        if ($percentage >= 82) return 2.5;
        if ($percentage >= 79) return 2.75;
        if ($percentage >= 76) return 3.0;
        if ($percentage >= 73) return 3.25;
        if ($percentage >= 70) return 3.5;
        if ($percentage >= 67) return 3.75;
        if ($percentage >= 65) return 4.0;
        if ($percentage >= 60) return 4.5;
        return 5.0;
    }

    /**
     * Get class sections for a student
     */
    private function getClassSections($studentId)
    {
        // Get all classes the student is enrolled in
        $studentClasses = StudentClass::where('student_id', $studentId)
            ->with(['class', 'class.professor'])
            ->get();

        return $studentClasses->map(function ($enrollment) use ($studentId) {
            $class = $enrollment->class;

            if (!$class) {
                return null;
            }

            // Get teacher information
            $teacherName = 'Unknown Teacher';
            $teacherEmail = null;
            $teacherAvatar = null;
            
            if ($class->professor) {
                $teacherName = $class->professor->name;
                $teacherEmail = $class->professor->email;
                $teacherAvatar = $class->professor->avatar; // Assuming the 'avatar' field exists
            }
            

            // Get attendance data for this class
            $attendanceData = $this->getClassAttendance($studentId, $class->id);

            // Get grade data for this class
            $gradeData = $this->getClassGrade($studentId, $class->id);

            // Get upcoming assessments for this class
            $upcomingAssessments = $this->getUpcomingAssessments($studentId, $class->id);

            // Get schedule from the class model
            $schedule = [];

            // Check if schedule exists in the class model
            if ($class->schedule) {
                // If it's a JSON string, decode it
                if (is_string($class->schedule)) {
                    try {
                        $scheduleData = json_decode($class->schedule, true);
                        if (is_array($scheduleData)) {
                            foreach ($scheduleData as $item) {
                                $schedule[] = [
                                    'day' => $item['day'] ?? 'Unknown',
                                    'time' => $item['time'] ?? 'TBD',
                                    'room' => $item['room'] ?? $class->room ?? 'TBD',
                                    'teacher' => $teacherName
                                ];
                            }
                        }
                    } catch (\Exception $e) {
                        // If JSON decode fails, generate a schedule
                        $schedule = $this->getClassSchedule($class, $teacherName);
                    }
                }
                // If it's already an array (Laravel might have already cast it)
                else if (is_array($class->schedule)) {
                    foreach ($class->schedule as $item) {
                        $schedule[] = [
                            'day' => $item['day'] ?? 'Unknown',
                            'time' => $item['time'] ?? 'TBD',
                            'room' => $item['room'] ?? $class->room ?? 'TBD',
                            'teacher' => $teacherName
                        ];
                    }
                }
            }

            // If no schedule was found or decoded, generate one
            if (empty($schedule)) {
                $schedule = $this->getClassSchedule($class, $teacherName);
            }

            return [
                'id' => $class->id,
                'name' => $class->name,
                'icon' => $this->getSubjectIcon($class->name),
                'term' => $class->term ?? 'Current Term',
                'teacher' => $teacherName,
                'teacherEmail' => $teacherEmail,
                'room' => $class->room ?? 'TBD',
                'gradeValue' => $gradeData['percentage'],
                'attendance' => $attendanceData['rate'],
                'pendingAssessments' => count($upcomingAssessments),
                'schedule' => $schedule,
                'avatar' => $teacherAvatar,
                'assignments' => $upcomingAssessments,
                'attendanceSummary' => [
                    'present' => $attendanceData['present'],
                    'absent' => $attendanceData['absent'],
                    'late' => $attendanceData['late'],
                    'excused' => $attendanceData['excused']
                ]
            ];
        })->filter()->values();
    }

    /**
     * Get class attendance for a student
     */
    private function getClassAttendance($studentId, $classId)
    {
        // Get attendance records for this class
        $attendanceRecords = Attendance::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->get();

        $totalRecords = $attendanceRecords->count();

        if ($totalRecords === 0) {
            return [
                'rate' => 100,
                'present' => 100,
                'absent' => 0,
                'late' => 0,
                'excused' => 0
            ];
        }

        // Count attendance statuses
        $present = $attendanceRecords->where('status', 'present')->count();
        $absent = $attendanceRecords->where('status', 'absent')->count();
        $late = $attendanceRecords->where('status', 'late')->count();
        $excused = $attendanceRecords->where('status', 'excused')->count();

        // Calculate attendance rate
        $attendanceRate = round(($present / $totalRecords) * 100);

        return [
            'rate' => $attendanceRate,
            'present' => $present > 0 ? round(($present / $totalRecords) * 100) : 0,
            'absent' => $absent > 0 ? round(($absent / $totalRecords) * 100) : 0,
            'late' => $late > 0 ? round(($late / $totalRecords) * 100) : 0,
            'excused' => $excused > 0 ? round(($excused / $totalRecords) * 100) : 0
        ];
    }

    /**
     * Get class grade for a student
     */
    // private function getClassGrade($studentId, $classId)
    // {
    //     // Get all assessments for this class
    //     $assessments = Assessment::where('class_id', $classId)->get();

    //     if ($assessments->isEmpty()) {
    //         return [
    //             'percentage' => 85, // Default percentage
    //             'phGrade' => '2.0'  // Default Philippines grade
    //         ];
    //     }

    //     $totalScore = 0;
    //     $totalWeight = 0;

    //     foreach ($assessments as $assessment) {
    //         $assessmentItems = $assessment->items;
    //         $itemScores = 0;
    //         $itemMaxScores = 0;

    //         foreach ($assessmentItems as $item) {
    //             $grade = Grade::where('student_id', $studentId)
    //                 ->where('assessment_item_id', $item->id)
    //                 ->first();

    //             if ($grade) {
    //                 $itemScores += $grade->score;
    //                 $itemMaxScores += $grade->total_score > 0 ? $grade->total_score : $item->total_score;
    //             }
    //         }

    //         if ($itemMaxScores > 0) {
    //             $assessmentPercentage = ($itemScores / $itemMaxScores) * 100;
    //             $totalScore += $assessmentPercentage * ($assessment->weight / 100);
    //             $totalWeight += $assessment->weight;
    //         }
    //     }

    //     if ($totalWeight > 0) {
    //         $percentage = round($totalScore / $totalWeight);
    //         $phGrade = $this->percentageToPhGrade($percentage);

    //         return [
    //             'percentage' => $percentage,
    //             'phGrade' => $phGrade
    //         ];
    //     }

    //     return [
    //         'percentage' => 85, // Default percentage
    //         'phGrade' => '2.0'  // Default Philippines grade
    //     ];
    // }

    private function getClassGrade($studentId, $classId)
    {
        // Find the student_class record to get the student_class_id
        $studentClass = StudentClass::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->first();
    
        if (!$studentClass) {
            return [
                'percentage' => 'N/A',
                'phGrade' => 'N/A'
            ];
        }
    
        // Find the final grade for this specific student_class
        $finalGrade = FinalGrade::where('student_class_id', $studentClass->id)->first();
    
        if ($finalGrade && $finalGrade->final_grade !== null) {
            $percentage = round($finalGrade->final_grade);
            $phGrade = $this->percentageToPhGrade($percentage);
    
            return [
                'percentage' => $percentage,
                'phGrade' => $phGrade
            ];
        }
    
        // No final grade found
        return [
            'percentage' => 'N/A',
            'phGrade' => 'N/A'
        ];
    }
    

    /**
     * Convert percentage to Philippines grade (1.0-5.0)
     */
    private function percentageToPhGrade($percentage)
    {
        if ($percentage >= 96) return '1.0';
        if ($percentage >= 94) return '1.25';
        if ($percentage >= 92) return '1.5';
        if ($percentage >= 89) return '1.75';
        if ($percentage >= 87) return '2.0';
        if ($percentage >= 84) return '2.25';
        if ($percentage >= 82) return '2.5';
        if ($percentage >= 79) return '2.75';
        if ($percentage >= 76) return '3.0';
        if ($percentage >= 73) return '3.25';
        if ($percentage >= 70) return '3.5';
        if ($percentage >= 67) return '3.75';
        if ($percentage >= 65) return '4.0';
        if ($percentage >= 60) return '4.5';
        return '5.0';
    }

    /**
     * Get upcoming assessments for a class
     */
    private function getUpcomingAssessments($studentId, $classId)
    {
        // Get assessments for this class
        $assessments = Assessment::where('class_id', $classId)
            ->with('assessmentItems')
            ->get();

        if ($assessments->isEmpty()) {
            return [];
        }

        $upcomingAssessments = [];

        foreach ($assessments as $assessment) {
            foreach ($assessment->assessmentItems as $item) {
                // Check if the student has already completed this assessment item
                $grade = Grade::where('student_id', $studentId)
                    ->where('assessment_item_id', $item->id)
                    ->first();

                // If no grade exists, it's an upcoming assessment
                if (!$grade) {
                    // Determine status based on due date (if available)
                    $status = 'Not Started';
                    $statusClass = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';

                    // Generate a due date (this would come from your database in a real app)
                    $dueDate = Carbon::now()->addDays(rand(1, 14))->format('M d, Y');

                    $upcomingAssessments[] = [
                        'icon' => $this->getAssessmentIcon($assessment->assessment_name),
                        'iconBg' => 'bg-blue-100 dark:bg-blue-900',
                        'iconColor' => 'text-blue-500',
                        'title' => $item->name,
                        'description' => "Complete this {$assessment->assessment_name} for {$assessment->weight}% of your grade.",
                        'dueDate' => $dueDate,
                        'status' => $status,
                        'statusClass' => $statusClass
                    ];
                }
            }
        }

        // Sort by due date
        usort($upcomingAssessments, function($a, $b) {
            return strtotime($a['dueDate']) - strtotime($b['dueDate']);
        });

        // Limit to 3 assessments
        return array_slice($upcomingAssessments, 0, 3);
    }

    /**
     * Get class schedule
     */
    private function getClassSchedule($class, $teacherName)
    {
        // In a real app, this would come from a schedule table
        // For now, generate a simple schedule based on class ID
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $times = [
            '8:00 AM - 9:30 AM',
            '9:45 AM - 11:15 AM',
            '11:30 AM - 1:00 PM',
            '1:15 PM - 2:45 PM',
            '3:00 PM - 4:30 PM'
        ];

        $schedule = [];
        $classIdNum = crc32($class->id) % 5; // Use hash of class ID to get consistent but random-looking results

        // Add 2-3 days to the schedule
        $numDays = ($classIdNum % 3) + 2;
        $dayIndices = array_rand($days, $numDays);

        if (!is_array($dayIndices)) {
            $dayIndices = [$dayIndices];
        }

        $timeIndex = $classIdNum % 5;

        foreach ($dayIndices as $dayIndex) {
            $schedule[] = [
                'day' => $days[$dayIndex],
                'time' => $times[$timeIndex],
                'room' => $class->room ?? 'Room ' . (100 + $classIdNum),
                'teacher' => $teacherName
            ];
        }

        return $schedule;
    }

    /**
     * Get academic performance data
     */
    private function getAcademicPerformance($studentId)
    {
        // Get all classes the student is enrolled in
        $studentClasses = StudentClass::where('student_id', $studentId)
            ->with('class')
            ->get();

        return $studentClasses->map(function ($enrollment) use ($studentId) {
            $class = $enrollment->class;

            if (!$class) {
                return null;
            }

            // Get grade data for this class
            $gradeData = $this->getClassGrade($studentId, $class->id);

            return [
                'subject' => $class->name,
                'percentage' => $gradeData['percentage'],
                'phGrade' => $gradeData['phGrade'],
                'color' => $this->getGradeColor($gradeData['percentage'])
            ];
        })->filter()->values();
    }

    /**
     * Get color for grade visualization
     */
    private function getGradeColor($percentage)
    {
        if ($percentage >= 90) return 'bg-green-600';
        if ($percentage >= 80) return 'bg-blue-600';
        if ($percentage >= 70) return 'bg-yellow-600';
        if ($percentage >= 60) return 'bg-orange-600';
        return 'bg-red-600';
    }

    /**
     * Get recent activities for a student
     */
    private function getRecentActivities($studentId)
    {
        $activities = [];

        // Get recent grades
        $recentGrades = Grade::where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get();

        foreach ($recentGrades as $grade) {
            $assessmentItem = AssessmentItem::find($grade->assessment_item_id);
            if ($assessmentItem) {
                $assessment = Assessment::find($assessmentItem->assessment_id);
                if ($assessment) {
                    $class = ClassModel::find($assessment->class_id);
                    if ($class) {
                        $percentage = ($grade->score / ($grade->total_score > 0 ? $grade->total_score : $assessmentItem->total_score)) * 100;
                        $phGrade = $this->percentageToPhGrade($percentage);

                        $activities[] = [
                            'icon' => '📝',
                            'iconBg' => 'bg-blue-100 dark:bg-blue-900',
                            'iconColor' => 'text-blue-500',
                            'title' => "{$class->name} {$assessment->assessment_name} Graded",
                            'description' => "You received a {$phGrade} on your {$assessmentItem->name}.",
                            'time' => Carbon::parse($grade->created_at)->diffForHumans()
                        ];
                    }
                }
            }
        }

        // Get recent attendance
        $recentAttendance = Attendance::where('student_id', $studentId)
            ->where('status', '!=', 'present')
            ->orderBy('date', 'desc')
            ->first();

        if ($recentAttendance) {
            $class = ClassModel::find($recentAttendance->class_id);
            $className = $class ? $class->name : 'Class';

            $activities[] = [
                'icon' => '🕒',
                'iconBg' => 'bg-yellow-100 dark:bg-yellow-900',
                'iconColor' => 'text-yellow-500',
                'title' => 'Attendance Recorded',
                'description' => "You were marked {$recentAttendance->status} for {$className} on " . Carbon::parse($recentAttendance->date)->format('M d, Y') . ".",
                'time' => Carbon::parse($recentAttendance->date)->diffForHumans()
            ];
        }

        // Get recent behavior reports
        $recentBehavior = BehaviorReport::where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($recentBehavior) {
            $icon = $recentBehavior->type === 'positive' ? '🏆' : '📢';
            $iconBg = $recentBehavior->type === 'positive' ? 'bg-green-100 dark:bg-green-900' : 'bg-purple-100 dark:bg-purple-900';
            $iconColor = $recentBehavior->type === 'positive' ? 'text-green-500' : 'text-purple-500';
            $title = $recentBehavior->type === 'positive' ? 'Positive Behavior Recognition' : 'Behavior Note';

            $activities[] = [
                'icon' => $icon,
                'iconBg' => $iconBg,
                'iconColor' => $iconColor,
                'title' => $title,
                'description' => $recentBehavior->comment ?? 'No details provided',
                'time' => Carbon::parse($recentBehavior->created_at)->diffForHumans()
            ];
        }

        // Add a generic announcement if we don't have enough activities
        if (count($activities) < 4) {
            $activities[] = [
                'icon' => '📢',
                'iconBg' => 'bg-purple-100 dark:bg-purple-900',
                'iconColor' => 'text-purple-500',
                'title' => 'School Announcement',
                'description' => 'Parent-Teacher conferences scheduled for next week.',
                'time' => '2d ago'
            ];
        }

        // Sort by time (most recent first)
        usort($activities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        // Limit to 4 activities
        return array_slice($activities, 0, 4);
    }

    /**
     * Get icon for a subject based on its name
     */
    private function getSubjectIcon($subjectName)
    {
        $subjectName = strtolower($subjectName);

        if (strpos($subjectName, 'math') !== false) return '🧮';
        if (strpos($subjectName, 'science') !== false) return '🧪';
        if (strpos($subjectName, 'biology') !== false) return '🧬';
        if (strpos($subjectName, 'chemistry') !== false) return '⚗️';
        if (strpos($subjectName, 'physics') !== false) return '🔭';
        if (strpos($subjectName, 'english') !== false) return '📚';
        if (strpos($subjectName, 'history') !== false) return '📜';
        if (strpos($subjectName, 'geography') !== false) return '🌍';
        if (strpos($subjectName, 'art') !== false) return '🎨';
        if (strpos($subjectName, 'music') !== false) return '🎵';
        if (strpos($subjectName, 'physical') !== false) return '🏃';
        if (strpos($subjectName, 'computer') !== false) return '💻';

        // Default icon
        return '📓';
    }

    /**
     * Get icon for an assessment based on its name
     */
    private function getAssessmentIcon($assessmentName)
    {
        $assessmentName = strtolower($assessmentName);

        if (strpos($assessmentName, 'quiz') !== false) return '📝';
        if (strpos($assessmentName, 'test') !== false) return '📄';
        if (strpos($assessmentName, 'exam') !== false) return '📚';
        if (strpos($assessmentName, 'project') !== false) return '🔬';
        if (strpos($assessmentName, 'lab') !== false) return '🧪';
        if (strpos($assessmentName, 'essay') !== false) return '✏️';
        if (strpos($assessmentName, 'presentation') !== false) return '📊';
        if (strpos($assessmentName, 'homework') !== false) return '📓';

        // Default icon
        return '📝';
    }
}

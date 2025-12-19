<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Attendance;
use App\Models\Assessment;
use App\Models\AssessmentItem;
use App\Models\ParentStudent;
use App\Models\StudentClass;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    // public function index()
    // {
    //     $professorId = Auth::id();

    //     $sections = ClassModel::where('professor_id', $professorId)
    //         ->select('id', 'name', 'icon', 'term')
    //         ->with('students.student')
    //         ->get()
    //         ->map(function ($section) {
    //             $students = $section->students;
    //             $studentCount = $students->count();

    //             // Attendance summary
    //             $attendanceSummary = [
    //                 'present' => $students->where('status', 'present')->count(),
    //                 'absent' => $students->where('status', 'absent')->count(),
    //                 'late' => $students->where('status', 'late')->count(),
    //                 'excused' => $students->where('status', 'excused')->count(),
    //             ];

    //             // Pending assignments count
    //             $pendingAssignments = Assessment::where('class_id', $section->id)->count();

    //             // Count assessments to be graded
    //             $toGradeCount = AssessmentItem::whereHas('assessment', function ($query) use ($section) {
    //                 $query->where('class_id', $section->id);
    //             })
    //                 ->whereNull('score')
    //                 ->count();

    //             // Calculate average score
    //             $averageScore = AssessmentItem::whereHas('assessment', function ($query) use ($section) {
    //                 $query->where('class_id', $section->id);
    //             })
    //                 ->avg('score') ?? 0;

    //             // Performance breakdown
    //             $performance = AssessmentItem::whereHas('assessment', function ($query) use ($section) {
    //                 $query->where('class_id', $section->id);
    //             })
    //                 ->get()
    //                 ->groupBy('name')
    //                 ->mapWithKeys(function ($items, $name) {
    //                     return [$name => round($items->avg('score'))];
    //                 });

    //             return [
    //                 'id' => (string) $section->id,
    //                 'name' => $section->name,
    //                 'icon' => $section->icon ?? '📚',
    //                 'term' => $section->term ?? 'Current Term',
    //                 'studentCount' => $studentCount,
    //                 'presentCount' => $attendanceSummary['present'] ?? 0,
    //                 'pendingAssignments' => $pendingAssignments ?? 0,
    //                 'toGradeCount' => $toGradeCount ?? 0,
    //                 'averageScore' => round($averageScore) ?? 0,
    //                 'attendanceSummary' => $attendanceSummary ?? ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0],
    //                 'performance' => $performance ?? ['quiz1' => 0, 'midterm' => 0, 'project' => 0, 'quiz2' => 0],
    //                 'students' => $students->map(function ($student) {
    //                     return [
    //                         'id' => $student->id,
    //                         'name' => $student->student->name ?? 'Unknown',
    //                         'email' => $student->student->email ?? 'No Email',
    //                         'status' => $student->status ?? 'Unknown',
    //                     ];
    //                 }),
    //             ];
    //         });

    //     return Inertia::render('Professor/Students', [
    //         'sections' => $sections,
    //     ]);
    // }

    public function index()
    {
        $professorId = Auth::id();

        $sections = ClassModel::where('professor_id', $professorId)
            ->select('id', 'name', 'icon', 'term')
            ->with(['students.student.parents']) // Eager load students and their parents
            ->get()
            ->map(function ($section) {
                $students = $section->students;
                $studentCount = $students->count();

                // Attendance summary
                $attendanceSummary = [
                    'present' => $students->where('status', 'present')->count(),
                    'absent' => $students->where('status', 'absent')->count(),
                    'late' => $students->where('status', 'late')->count(),
                    'excused' => $students->where('status', 'excused')->count(),
                ];

                // Pending assignments count
                $pendingAssignments = Assessment::where('class_id', $section->id)->count();

                // Count assessments to be graded
                $toGradeCount = AssessmentItem::whereHas('assessment', function ($query) use ($section) {
                    $query->where('class_id', $section->id);
                })
                    ->whereNull('score')
                    ->count();

                // Calculate average score
                $averageScore = AssessmentItem::whereHas('assessment', function ($query) use ($section) {
                    $query->where('class_id', $section->id);
                })
                    ->avg('score') ?? 0;

                // Performance breakdown
                $performance = AssessmentItem::whereHas('assessment', function ($query) use ($section) {
                    $query->where('class_id', $section->id);
                })
                    ->get()
                    ->groupBy('name')
                    ->mapWithKeys(function ($items, $name) {
                        return [$name => round($items->avg('score'))];
                    });

                return [
                    'id' => (string) $section->id,
                    'name' => $section->name,
                   
                    'icon' => $section->icon ?? '📚',
                    'term' => $section->term ?? 'Current Term',
                    'studentCount' => $studentCount,
                    'presentCount' => $attendanceSummary['present'] ?? 0,
                    'pendingAssignments' => $pendingAssignments ?? 0,
                    'toGradeCount' => $toGradeCount ?? 0,
                    'averageScore' => round($averageScore) ?? 0,
                    'attendanceSummary' => $attendanceSummary ?? ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0],
                    'performance' => $performance ?? ['quiz1' => 0, 'midterm' => 0, 'project' => 0, 'quiz2' => 0],
                    'students' => $students->map(function ($student) {
                        // Prepare student details
                        $studentDetails = [
                            'id' => $student->id,
                            'name' => $student->student->name ?? 'Unknown',
                            'avatar' => $student->student->avatar,
                            'school_id' => $student->student->school_id,
                            'email' => $student->student->email ?? 'No Email',
                            'status' => $student->status ?? 'Unknown',
                        ];

                        // Access the parent details through the 'parents' relationship
                        $parentDetails = $student->student->parents->map(function ($parent) {
                            return [
                                'id' => $parent->id,
                                'name' => $parent->name,
                                'avatar' => $parent->avatar,
                                'email' => $parent->email,
                            ];
                        });
                                          

                        // Add the parent details to the student
                        $studentDetails['parents'] = $parentDetails;

                        return $studentDetails;
                    }),
                ];
            });

        return Inertia::render('Professor/Students', [
            'sections' => $sections,
        ]);
    }


    //     public function addStudentsToClass(Request $request)
    // {
    //     $request->validate([
    //         'emails' => 'required|array',
    //         'emails.*' => 'required|email|exists:users,email',
    //         'class_id' => 'required|exists:classes,id',
    //     ]);

    //     $added = [];
    //     $skipped = [];

    //     foreach ($request->emails as $email) {
    //         $student = User::where('email', $email)->first();

    //         if (!$student) {
    //             $skipped[] = $email;
    //             continue;
    //         }

    //         // Add student to class if not already added
    //         if (!StudentClass::where('student_id', $student->id)->where('class_id', $request->class_id)->exists()) {
    //             StudentClass::create([
    //                 'student_id' => $student->id,
    //                 'class_id' => $request->class_id,
    //             ]);
    //             $added[] = $email;
    //         } else {
    //             $skipped[] = $email;
    //         }

    //         // Add parent if exists
    //         if ($student->parent) {
    //             $parent = $student->parent;

    //             if (!StudentClass::where('student_id', $parent->id)->where('class_id', $request->class_id)->exists()) {
    //                 StudentClass::create([
    //                     'student_id' => $parent->id,
    //                     'class_id' => $request->class_id,
    //                 ]);
    //             }
    //         }
    //     }

    //     return response()->json([
    //         'message' => 'Student and parent enrollment processed.',
    //         'added' => $added,
    //         'skipped' => $skipped,
    //     ]);
    // }
    // public function addStudentsToClass(Request $request)
    // {
    //     $request->validate([
    //         'emails' => 'required|array',
    //         'emails.*' => 'required|email|exists:users,email',
    //         'class_id' => 'required|exists:classes,id',
    //     ]);

    //     $added = [];
    //     $skipped = [];

    //     foreach ($request->emails as $email) {
    //         $student = User::where('email', $email)->first();

    //         if (!$student) {
    //             $skipped[] = $email;
    //             continue;
    //         }

    //         // Add student to class if not already added
    //         if (!StudentClass::where('student_id', $student->id)->where('class_id', $request->class_id)->exists()) {
    //             StudentClass::create([
    //                 'student_id' => $student->id,
    //                 'class_id' => $request->class_id,
    //             ]);
    //             $added[] = $email;
    //         } else {
    //             $skipped[] = $email;
    //         }
    //     }

    //     return response()->json([
    //         'message' => 'Student enrollment processed.',
    //         'added' => $added,
    //         'skipped' => $skipped,
    //     ]);
    // }

    public function addStudentsToClass(Request $request)
{
    $request->validate([
        'emails' => 'required|array',
        'emails.*' => 'required|email|exists:users,email',
        'class_id' => 'required|exists:classes,id',
    ]);

    $added = [];
    $skipped = [];

    foreach ($request->emails as $email) {
        // Fetch only users with user_type = student
        $student = User::where('email', $email)
                       ->where('user_type', 'student')
                       ->first();

        // If the user doesn't exist or is not a student
        if (!$student) {
            $skipped[] = $email;
            continue;
        }

        // Add student to class if not already added
        if (!StudentClass::where('student_id', $student->id)->where('class_id', $request->class_id)->exists()) {
            StudentClass::create([
                'student_id' => $student->id,
                'class_id' => $request->class_id,
            ]);
            $added[] = $email;
        } else {
            $skipped[] = $email;
        }
    }

    return response()->json([
        'message' => 'Student enrollment processed.',
        'added' => $added,
        'skipped' => $skipped,
    ]);
}


    public function destroy($id)
    {
        $student = StudentClass::find($id);

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $student->delete();

        return response()->json(['message' => 'Student deleted successfully']);
    }

    // public function linkParent(Request $request)
    // {
    //     $studentClassId = $request->student_class_id;
    //     $parentId = $request->parent_id;
    
    //     // Validate the incoming request data
    //     $validatedData = $request->validate([
    //         'student_class_id' => 'required|exists:students_classes,id',
    //         'parent_id' => 'required|exists:users,id', // Assuming parents are users and are stored in the users table
    //     ]);
    
    //     // Check if the student class exists
    //     $studentClass = StudentClass::find($studentClassId);
    
    //     if (!$studentClass) {
    //         return response()->json(['message' => 'Student class not found.'], 404);
    //     }
    
    //     // Check if the student exists within this student class
    //     $student = $studentClass->student; // Using the defined relationship
    
    //     if (!$student) {
    //         return response()->json(['message' => 'Student not found for this class.'], 404);
    //     }
    
    //     // Optional: Check if the parent already exists for this student class (if needed)
    //     $existingParent = $student->parents()->where('parent_id', $parentId)->first(); // Assuming you have a 'parents' relationship on the student model
    
    //     if ($existingParent) {
    //         return response()->json(['message' => 'Parent is already linked to this student.'], 400);
    //     }
    
    //     // Link the parent to the student class (assuming you have a pivot table or some other way of linking parents)
    //     // If you're storing parents in a separate pivot table, you can do something like this:
    //     $student->parents()->attach($parentId); // Attach the parent to the student using a pivot table
    
    //     // Alternatively, if you want to link them directly without a pivot table, you might have a `parent_id` column in the `students` table
    
    //     // Continue with the parent linking process...
    //     // You can perform any other necessary actions such as notifying the parent or saving additional data
    
    //     return response()->json(['message' => 'Parent successfully linked to student.'], 200);
    // }

    public function linkParent(Request $request)
{
    $studentClassId = $request->student_class_id;
    $parentId = $request->parent_id;

    // Validate the incoming request data
    $validatedData = $request->validate([
        'student_class_id' => 'required|exists:students_classes,id',
        'parent_id' => 'required|exists:users,id', // Assuming parents are users and stored in users table
    ]);

    // Check if the student class exists
    $studentClass = StudentClass::find($studentClassId);

    if (!$studentClass) {
        return response()->json(['message' => 'Student class not found.'], 404);
    }

    // Get the student from the class
    $student = $studentClass->student;

    if (!$student) {
        return response()->json(['message' => 'Student not found for this class.'], 404);
    }

    // Check if the student already has linked parents
    $currentParents = $student->parents;

    if ($currentParents->isNotEmpty()) {
        // Optional: Detach all currently linked parents first (to ensure only one)
        $student->parents()->detach();

        // Then attach the new one
        $student->parents()->attach($parentId);

        return response()->json(['message' => 'Parent link updated successfully.'], 200);
    } else {
        // No parent linked yet, attach the parent
        $student->parents()->attach($parentId);

        return response()->json(['message' => 'Parent successfully linked to student.'], 200);
    }
}

    
    
    
    

    public function getAvailableParents()
    {
        // Fetch users where user_type is "parent"
        $parents = User::where('user_type', 'parent')->select('id', 'name', 'email')->get();

        return response()->json($parents);
    }
}

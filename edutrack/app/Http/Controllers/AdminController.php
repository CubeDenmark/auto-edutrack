<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Course;
use App\Models\Major;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{

    // Store a new department
    public function storeDepartments(Request $request)
    {
        $request->validate([
            'department_name' => 'required|string|unique:departments,department_name',
            'department_code' => 'required|string|unique:departments,department_code',
            'description' => 'nullable|string',
        ]);

        Department::create([
            'department_name' => $request->department_name,
            'department_code' => $request->department_code,
            'description' => $request->description,
        ]);

        return redirect()->route('departments.index')->with('success', 'Department added successfully.');
    }

    // Display a list of departments
    public function departments(): Response
    {
        $departments = Department::select('department_name', 'department_code', 'description')->get();

        return Inertia::render('Admin/ManageDepartments', [
            'departments' => $departments,
        ]);
    }


     // Store a new Courses
     public function storeCourses(Request $request)
     {
         $request->validate([
             'course_name' => 'required|string|unique:courses,course_name',
             'course_code' => 'required|string|unique:courses,course_code',
             'units' => 'required|integer',
             'description' => 'nullable|string',
             'department_id' => 'required|exists:departments,id', // Ensures department exists
         ]);

         Course::create([
             'course_name' => $request->course_name,
             'course_code' => $request->course_code,
             'units' => $request->units,
             'description' => $request->description,
             'department_id' => $request->department_id, // Store the selected department
         ]);

         return redirect()->route('departments.index')->with('success', 'Course added successfully.');
     }

     // courses
     public function courses(): Response
    {

        $departments = Department::select('id', 'department_name')->get();
        $courses = Course::with('department') // Eager load department relation
            ->select('id', 'course_name', 'course_code', 'units', 'department_id')
            ->get()
            ->map(function ($course) {
                return [
                    'course_name' => $course->course_name,
                    'course_code' => $course->course_code,
                    'units' => $course->units,
                    'department' => $course->department ? $course->department->department_name : 'Unknown',
                ];
            });

        return Inertia::render('Admin/ManageCourses', [
            'courses' => $courses,
            'departments' => $departments,
        ]);
    }




    // store new majors
    public function storeMajors(Request $request)
    {
        $request->validate([
            'major_name' => 'required|string|unique:majors,major_name',
            'major_code' => 'required|string|unique:majors,major_code',
            'description' => 'nullable|string',
            'course_id' => 'required|exists:courses,id', // Ensures department exists
        ]);

        Major::create([
            'major_name' => $request->major_name,
            'major_code' => $request->major_code,
            'description' => $request->description,
            'course_id' => $request->course_id, // Store the selected department
        ]);

        return redirect()->route('majors.index')->with('success', 'Course added successfully.');
    }

     // major
     public function majors(): Response
     {
         // Fetch courses with only the required fields
         $courses = Course::select('id', 'course_name')->get();

         // Fetch majors and eager-load the associated course
         $majors = Major::with('course') // Ensure the 'course' relationship is correctly loaded
             ->select('id', 'major_name', 'major_code', 'course_id')
             ->get()
             ->map(function ($major) { // Fix variable name from $course to $major
                 return [
                     'id' => $major->id,
                     'major_name' => $major->major_name,
                     'major_code' => $major->major_code,
                     'course' => $major->course ? $major->course->course_name : 'Unknown',
                 ];
             });

         return Inertia::render('Admin/ManageMajors', [
             'majors' => $majors,
             'courses' => $courses, // Fix variable name to match frontend usage
         ]);
     }

     // store subjects
     public function storeSubjects(Request $request)
     {
         $request->validate([
             'subject_name' => 'required|string|unique:subjects,subject_name',
             'subject_code' => 'required|string|unique:subjects,subject_code',
             'units' => 'required|integer',
             'year_level' => 'required|in:1,2,3,4', // Ensures only valid year levels
             'subject_type' => 'required|in:minor,major',
             'semester' => 'required|in:1,2', // Ensures only valid semesters
             'major_id' => 'required|exists:majors,id', // Ensures major exists
         ]);

         Subject::create([
             'subject_name' => $request->subject_name,
             'subject_code' => $request->subject_code,
             'units' => $request->units,
             'subject_type' => $request->subject_type,
             'year_level' => $request->year_level,
             'semester' => $request->semester,
             'major_id' => $request->major_id, // Store the selected major
         ]);

         return redirect()->route('subjects.index')->with('success', 'Subject added successfully.');
     }


     public function subjects(): Response
     {
         // Fetch unique subject types from the database
         $subjectTypes = Subject::select('subject_type')->distinct()->pluck('subject_type');
     
         // Fetch majors
         $majors = Major::select('id', 'major_name')->get();
// <<<<<<< HEAD
     
//          // Fetch subjects and eager-load the associated major
//          $subjects = Subject::with('major')
//              ->select('id', 'subject_name', 'subject_code', 'subject_type', 'major_id', 'units', 'year_level', 'semester')
// =======

         // Fetch majors and eager-load the associated course
         $subjects = Subject::with('major') // Ensure the 'course' relationship is correctly loaded
             ->select('id', 'subject_name', 'subject_code', 'major_id', 'units', 'year_level', 'semester')

             ->get()
             ->map(function ($subject) {
                 return [
                     'id' => $subject->id,
                     'subject_name' => $subject->subject_name,
                     'subject_code' => $subject->subject_code,
                     'units' => $subject->units,
                     'year_level' => $subject->year_level,
                     'semester' => $subject->semester,
// <<<<<<< HEAD
//                      'subject_type' => $subject->subject_type,
// =======

                     'major' => $subject->major ? $subject->major->major_name : 'Unknown',
                 ];
             });

         return Inertia::render('Admin/ManageSubjects', [
             'majors' => $majors,
             'subjects' => $subjects,
             'subjectTypes' => $subjectTypes, // Pass the dynamic subject types
         ]);
     }
     

     // school year
     public function storeSchoolYear(Request $request)
     {
         $request->validate([
             'year' => 'required|string|unique:school_years,year|regex:/^\d{4}-\d{4}$/',
             'semester' => 'required|string|in:1st Semester,2nd Semester,Summer',
             'is_active' => 'nullable|boolean',
         ]);

         SchoolYear::create([
             'year' => $request->year,
             'semester' => $request->semester,
             'is_active' => $request->is_active ?? true, // Default to true if not provided
         ]);

         return redirect()->route('schoolYear.index')->with('success', 'School Year added successfully.');
     }



     public function schoolYear()
    {
        $schoolYears = SchoolYear::orderBy('year', 'desc')->get();
        return Inertia::render('Admin/ManageYearLevels', [
            'schoolYears' => $schoolYears
        ]);
    }



    public function storeSections(Request $request)
    {
        $request->validate([
            'section_name' => 'required|string',
            'year_level' => 'required|string',
            'major_id' => 'required|exists:majors,id', // Validate that course exists
            'user_id' => 'required|exists:users,id', // Validate that adviser exists
        ]);
    
        Section::create([
            'section_name' => $request->section_name,
            'year_level' => $request->year_level,
            'major_id' => $request->major_id,
            'user_id' => $request->user_id, // Adviser ID
        ]);
    
        return redirect()->route('sections.index')->with('success', 'Section added successfully.');
    }

    public function section()
    {
        $sections = Section::with(['major.course', 'teacher'])->get()->map(function ($section) {
            return [
                'id' => $section->id,
                'section_name' => $section->section_name,
                'course_name' => optional($section->major->course)->course_name ?? 'N/A',
                'major_name' => optional($section->major)->major_name ?? 'N/A',
                'yearLevel' => $section->year_level,
                'teacher' => optional($section->teacher)->name ?? 'N/A',
                
            ];
        });
    
        $majors = Major::all();
        $advisers = User::where('user_type', 'teacher')->get();
    
        return Inertia::render('ManageSections', [
            'sections' => $sections,
            'majors' => $majors,
            'advisers' => $advisers,
        ]);
    }
    
    






    

}


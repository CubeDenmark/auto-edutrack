<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\MailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\ClassSectionController;
use App\Http\Controllers\ProfDashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\GradesController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AssessRecordsController;
use App\Http\Controllers\BehaviorReportController;
use App\Http\Controllers\NavStudentController;
use App\Http\Controllers\NavParentController;
use App\Http\Controllers\StudentAttendanceController;
use App\Http\Controllers\StudentBehaviorController;
use App\Http\Controllers\StudentGradesController;
use App\Http\Controllers\ParentBehaviorReportController;
use App\Http\Controllers\ParentAttendanceController;
use App\Http\Controllers\ParentGradesController;
use App\Http\Controllers\ParentDashboardController;
use App\Http\Controllers\ProfPrintController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\StudentImportController;
use App\Models\ClassModel;
use App\Http\Controllers\StudentPrintController;
use Illuminate\Support\Facades\Auth;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;


Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        $redirectRoute = match ($user->user_type) {
            'prof' => 'prof.dashboard',
            'parent' => 'parent.dashboard',
            'student' => 'student.dashboard',
            default => 'home',
        };

        return redirect()->route($redirectRoute);
    }

    return Inertia::render('Welcome');
})->name('home');


// Route::get('/', function () {
//     return Inertia::render('Welcome');
// })->name('home');

Route::get('/generate-sitemap', function () {

    $sitemap = Sitemap::create()
        ->add(Url::create('/'))
        ->add(Url::create('/about'))
        ->add(Url::create('/contact'));

    $sitemap->writeToFile(public_path('sitemap.xml'));

    return 'Sitemap generated!';
});


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';


Route::middleware(['auth', 'professor', 'verified'])->group(function () {

    Route::get('/prof/dashboard', [ProfDashboardController::class, 'index'])
    ->name('prof.dashboard');

    // Route::get('/prof/dashboard', function () {
    //     return Inertia::render('Professor/ProfDashboard'); // Pages/Professor
    // })->name('prof.dashboard');

    Route::get('/prof/attendance', [ClassSectionController::class, 'index'])->name('prof.attendance');
    Route::post('/attendance/save', [ClassSectionController::class, 'saveAttendance'])->name('attendance.save');

    // Route::get('/prof/attendance', function () {
    //     return Inertia::render('Professor/Attendance'); // Pages/Professor
    // })->name('prof.attendance');

    Route::get('/prof/students', [StudentController::class, 'index'])->name('prof.students');
    Route::post('/add-students-to-class', [StudentController::class, 'addStudentsToClass'])->name('class.addStudents')->middleware('verified');
    Route::delete('/students/{id}', [StudentController::class, 'destroy']);
    Route::post('/link-parent', [StudentController::class, 'linkParent'])->name('parent.link');
    Route::get('/available-parents', [StudentController::class, 'getAvailableParents'])->name('parents.available');
    // Route::get('/prof/students', function () {
    //     return Inertia::render('Professor/Students'); // Pages/Professor
    // })->name('prof.students');

    Route::get('/prof/grades', [GradesController::class, 'index'])->name('prof.students');
    Route::post('/grades/store-or-update', [GradesController::class, 'storeOrUpdateFinalGrade'])->name('grades.storeOrUpdate');

    // Route::get('/prof/grades', function () {
    //     return Inertia::render('Professor/Grades'); // Pages/Professor
    // })->name('prof.grades');

    Route::get('/prof/assessment', [AssessmentController::class, 'index'])->name('prof.assessment');
    Route::post('/assessments', [AssessmentController::class, 'store'])->name('assessments.store');
    Route::put('/assessments/{id}', [AssessmentController::class, 'update'])->name('assessments.update');
    Route::delete('/delete-assessment/{id}', [AssessmentController::class, 'destroyAssessment'])->name('assessments.destroy');

    Route::post('/assessments-items', [AssessmentController::class, 'storeItems'])->name('assessments.storeItems');
    Route::delete('/delete-items/{id}', [AssessmentController::class, 'destroyItems'])->name('assessment-items.destroy');

    // Route::get('/prof/assessment', function () {
    //     return Inertia::render('Professor/Assessment'); // Pages/Professor
    // })->name('prof.assessment');

    Route::get('/prof/assess_records', [AssessRecordsController::class, 'index'])->name('prof.assessment_records');
    Route::post('/update-assessment-scores', [AssessRecordsController::class, 'storeOrUpdateScores'])->name('storeScores');

    // Route::get('/prof/assess_records', function () {
    //     return Inertia::render('Professor/AssessmentRecords'); // Pages/Professor
    // })->name('prof.assessment_records');

    Route::get('/prof/behavior_reports', [BehaviorReportController::class, 'index'])
    ->name('prof.behavior_reports');

    Route::post('/prof/behavior_reports', [BehaviorReportController::class, 'store']);

    // Route::get('/prof/behavior_reports', function () {
    //     return Inertia::render('Professor/BehaviorReports'); // Pages/Professor
    // })->name('prof.behavior_reports');


    Route::get('/classes', [ClassController::class, 'index']);
    Route::post('/classes', [ClassController::class, 'store']);
    Route::put('/classes/{id}', [ClassController::class, 'update']);
    Route::delete('/classes/{id}', [ClassController::class, 'destroy']);


    // Route::get('/classes', [ClassController::class, 'index']);

    // Route::post('/classes', [ClassController::class, 'store']);

    // generate pdf
    Route::get('/grades/generate-pdf/{classId}', [ProfPrintController::class, 'generatePdf']);

});


Route::middleware(['auth', 'parent'])->group(function () {

    Route::get('/parent/dashboard', [ParentDashboardController::class, 'index'])->name('parent.dashboard');
    // Route::get('/parent/dashboard', function () {
    //     return Inertia::render('Parent/ParentDashboard'); // Capital "A"
    // }) ->name('parent.dashboard');;


    Route::get('/parent/grades', [ParentGradesController::class, 'index'])->name('parent.grades');
    Route::get('/parent/grades/child/{childId}', [ParentGradesController::class, 'getChildGrades']);

    // Route::get('/parent/grades', function () {
    //     return Inertia::render('Parent/ParentGrades'); // Capital "A"
    // });

    Route::get('/parent/attendance', [ParentAttendanceController::class, 'index'])->name('parent.attendance');
    Route::get('/parent/attendance/child/{childId}', [ParentAttendanceController::class, 'getChildAttendance'])->name('parent.attendance.child');

    // Route::get('/parent/attendance', function () {
    //     return Inertia::render('Parent/ParentAttendance'); // Capital "A"
    // });

    // Route::get('/parent/behavior-reports', [ParentBehaviorReportController::class, 'index'])->name('student.grades');
    Route::get('/parent/behavior-reports', [ParentBehaviorReportController::class, 'index'])->name('parent.behavior-reports.index');

    // Route::get('/parent/behavior-reports', function () {
    //     return Inertia::render('Parent/ParentBehaviorReports'); // Capital "A"
    // });

    Route::get('/parent/notifications', function () {
        return Inertia::render('Parent/ParentNotifications'); // Capital "A"
    });

    Route::get('/parent/children', [NavParentController::class, 'index']);

});

// Parent Dashboard Route
// Route::middleware(['auth', 'role:parent'])->group(function () {
//     Route::get('/parent/dashboard', [ParentDashboardController::class, 'index'])->name('parent.dashboard');
// });


Route::middleware(['auth', 'student'])->group(function () {

    Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
    // Route::get('/student/classes', [StudentDashboardController::class, 'getClasses'])->name('student.classes');

    Route::get('/student/terms', [StudentDashboardController::class, 'getAvailableTerms']);
    Route::get('/student/generate-pdf-by-term/{studentId}', [StudentPrintController::class, 'generatePdfByTerm'])->middleware('auth');


    // Route::get('/student/dashboard', function () {
    //     return Inertia::render('Student/StudentDashboard'); // Capital "A"
    // }) ->name('student.dashboard');;


    Route::get('/student/grades', [StudentGradesController::class, 'index'])->name('student.grades');
    Route::get('/grades/sections', [StudentGradesController::class, 'getAllSections']);
    Route::get('/grades/section/{sectionId}/assessments', [StudentGradesController::class, 'getSectionAssessments']);
    Route::get('/grades/section/{sectionId}/debug', [StudentGradesController::class, 'debug']);

    // Route::get('/student/grades', function () {
    //     return Inertia::render('Student/StudentGrades'); // Capital "A"
    // });

    Route::get('/student/attendance', [StudentAttendanceController::class, 'index'])->name('student.attendance');

    // Route::get('/student/attendance', function () {
    //     return Inertia::render('Student/StudentAttendance'); // Capital "A"
    // });


    Route::get('/student/behavior-reports', [StudentBehaviorController::class, 'index'])->name('student.nehavior_reports');

    // Route::get('/student/behavior-reports', function () {
    //     return Inertia::render('Student/StudentBehaviorReports'); // Capital "A"
    // });

    Route::get('/student/notifications', function () {
        return Inertia::render('Student/StudentNotifications'); // Capital "A"
    });

    Route::get('/student/classes', [NavStudentController::class, 'index']);

});

// Route::get('/students/upload', [StudentImportController::class, 'showUploadForm'])->name('students.upload.form');
// Route::post('/students/import', [StudentImportController::class, 'import'])->name('students.import');


Route::get('/students/import', function () {
    $classes = ClassModel::select('id', 'name', 'term')->get();
    return Inertia::render('Professor/ImportStudents', [
        'classes' => $classes
    ]);
});


    Route::post('/students/import', [StudentImportController::class, 'import']);







Route::get('prof_test/dashboard', function () {
    return Inertia::render('Professor/TeacherDashboardtest'); // Capital "A"
})->name('prof_test.dashboard');

Route::get('student_test/dashboard', function () {
    return Inertia::render('Student/StudentDashboard-latest'); // Capital "A"
})->name('prof_test.dashboard');

Route::get('/parent_test/dashboard', function () {
    return Inertia::render('Parent/ParentDashboard-test'); // Capital "A"
});

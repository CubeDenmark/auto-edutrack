<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FinalGrade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;

class StudentPrintController extends Controller
{
    public function generatePdfByTerm(Request $request, $studentId)
    {
        try {
            $term = $request->query('term');
    
            // Get all FinalGrades for this student where the related class has the selected term
            $grades = FinalGrade::with([
                    'student',
                    'studentClass.class.professor'
                ])
                ->where('student_id', $studentId)
                ->whereHas('studentClass.class', function ($query) use ($term) {
                    $query->where('term', $term);
                })
                ->get();
    
            if ($grades->isEmpty()) {
                return response()->json(['message' => 'No grades found for this term.'], 404);
            }
    
            // Group grades by class name
            $gradesByClass = $grades->groupBy(function ($grade) {
                return $grade->studentClass->class->name ?? 'Unknown Subject';
            });
    
            $student = $grades->first()->student;
    
            // ✅ GWA Calculation
            $totalGrades = $grades->sum('final_grade'); // Assuming 'final_grade' is the column name
            $subjectCount = $gradesByClass->count();
            $gwa = $subjectCount > 0 ? round($totalGrades / $subjectCount, 2) : 0;
    
            // Render the Blade view to HTML
            $html = View::make('StudentGrades', [
                'gradesByClass' => $gradesByClass,
                'selectedTerm' => $term,
                'student' => $student,
                'gwa' => $gwa, // ✅ Pass GWA to view
            ])->render();
    
            // Generate PDF using Browsershot
            $pdf = Browsershot::html($html)
                ->setOption('args', ['--no-sandbox'])
                ->format('A4')
                ->pdf();
    
            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="student_grades.pdf"');
    
        } catch (\Exception $e) {
            \Log::error("PDF generation failed: " . $e->getMessage());
            return response()->json([
                'message' => 'Failed to generate PDF.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

    
}

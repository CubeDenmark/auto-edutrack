<?php

namespace App\Http\Controllers;

use App\Models\FinalGrade;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


use Illuminate\Http\Request;

class ProfPrintController extends Controller
{



    public function generatePdf($classId)
    {
        try {
            $grades = FinalGrade::with(['student', 'studentClass.class.professor'])
                ->whereHas('studentClass', function ($query) use ($classId) {
                    $query->where('class_id', $classId);
                })
                ->get();

            if ($grades->isEmpty()) {
                return response()->json([
                    'message' => 'No grades found for this class.'
                ], 404);
            }

            $class = optional($grades->first()->studentClass->class);
            $className = $class->name ?? 'Unknown Class';
            $term = $class->term ?? 'Unknown Term';
            $professorName = $class->professor->name ?? 'Unknown Professor';
            $dateGenerated = now()->format('F j, Y'); // ⬅ Add this

            $html = View::make('ProfGrades', [
                'grades' => $grades,
                'className' => $className,
                'term' => $term,
                'professorName' => $professorName,
                'dateGenerated' => $dateGenerated, // ⬅ Pass to view
            ])->render();

            Log::info("Generating PDF for class ID: {$classId} with " . $grades->count() . " grades.");

            $pdf = Browsershot::html($html)
                ->setOption('args', ['--no-sandbox'])
                ->format('A4')
                ->pdf();

            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="class_grades_' . $classId . '.pdf"');
        } catch (\Exception $e) {
            Log::error('PDF Generation error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to generate PDF.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

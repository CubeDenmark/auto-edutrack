<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Assessment;
use App\Models\AssessmentItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AssessRecordsController extends Controller
{
    public function index()
    {
        $professorId = Auth::id();

        $sections = ClassModel::where('professor_id', $professorId)
            ->select('id', 'name', 'icon', 'term')
            ->with([
                'assessments.items', // Fetching assessments and items
            ])
            ->get()
            ->map(function ($section) {
                // Assignments Data
                $assignments = $section->assessments->map(function ($assessment) {
                    return [
                        'id' => $assessment->id,
                        'name' => $assessment->name,
                        'category' => $assessment->type,
                        'weight' => $assessment->weight,
                        'totalPoints' => $assessment->items->sum('total_score'),
                        'dueDate' => $assessment->due_date, // Assuming you have a due_date field in assessments
                        'items' => $assessment->items->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'name' => $item->name,
                                'total_score' => $item->total_score,
                            ];
                        }),
                    ];
                });

                return [
                    'id' => (string) $section->id,
                    'name' => $section->name,
                    'icon' => $section->icon ?? '📚',
                    'term' => $section->term ?? 'Current Term',
                    'assignments' => $assignments,
                ];
            });

        return Inertia::render('Professor/AssessmentRecords', [
            'sections' => $sections,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Assessment;
use App\Models\AssessmentItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AssessmentController extends Controller
{
    // public function index()
    // {
    //     $professorId = Auth::id();

    //     $sections = ClassModel::where('professor_id', $professorId)
    //         ->select('id', 'name', 'icon', 'term')
    //         ->with([
    //             'assessments.items', // Fetching assessments and items
    //         ])
    //         ->get()
    //         ->map(function ($section) {
    //             // Assignments Data
    //             $assignments = $section->assessments->map(function ($assessment) {
    //                 return [
    //                     'id' => $assessment->id,
    //                     'assessment_name' => $assessment->assessment_name,
    //                     'term' => $assessment->type,
    //                     'weight' => $assessment->weight,
    //                     'totalPoints' => $assessment->items->sum('total_score'),
    //                     'dueDate' => $assessment->due_date, // Assuming you have a due_date field in assessments
    //                     'items' => $assessment->items->map(function ($item) {
    //                         return [
    //                             'id' => $item->id,
    //                             'name' => $item->name,
    //                             'total_score' => $item->total_score,
    //                         ];
    //                     }),
    //                 ];
    //             });

    //             return [
    //                 'id' => (string) $section->id,
    //                 'name' => $section->name,
    //                 'icon' => $section->icon ?? '📚',
    //                 'term' => $section->term ?? 'Current Term',
    //                 'assignments' => $assignments,
    //             ];
    //         });

    //     return Inertia::render('Professor/Assessment', [
    //         'sections' => $sections,
    //     ]);
    // }

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
                // Directly map the assessments data
                $assessments = $section->assessments->map(function ($assessment) {
                    return [
                        'id' => $assessment->id,
                        'assessment_name' => $assessment->assessment_name,
                        'term' => $assessment->term,
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
                    'studentCount' => $section->students()->count(),
                    'assessments' => $assessments,
                ];
            });

        return Inertia::render('Professor/Assessment', [
            'sections' => $sections,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'assessment_name' => 'required|string|max:255',
            'term' => 'required|string|max:50',
            'weight' => 'required|numeric|min:0|max:100',
            'class_id' => 'required|exists:classes,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check for duplicate assessment name within the same term and class
        $existing = Assessment::where('assessment_name', $request->assessment_name)
            ->where('term', $request->term)
            ->where('class_id', $request->class_id)
            ->first();

        if ($existing) {
            return back()->withErrors([
                'assessment_name' => 'This assessment name already exists in this term.'
            ])->withInput();
        }

        $currentTotalWeight = Assessment::where('class_id', $request->class_id)
            ->where('term', $request->term)
            ->sum('weight');

        // Prevent adding any new assessments if total is already 100%
        if ($currentTotalWeight >= 100) {
            return back()->withErrors([
                'weight' => "You cannot add more assessments for the {$request->term} term because the total weight is already 100%."
            ])->withInput();
        }

        // Prevent the total weight from exceeding 100%
        if (($currentTotalWeight + $request->weight) > 100) {
            return back()->withErrors([
                'weight' => "Total weight for the {$request->term} term cannot exceed 100%."
            ])->withInput();
        }

        Assessment::create([
            'assessment_name' => $request->assessment_name,
            'term' => $request->term,
            'weight' => $request->weight,
            'class_id' => $request->class_id,
        ]);

        return redirect()->back()->with('success', 'Assessment created successfully.');
    }



    public function update(Request $request, $id)
    {
        $assessment = Assessment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'assessment_name' => 'required|string|max:255',
            'term' => 'required|string|max:50',
            'weight' => 'required|numeric|min:0|max:100',
            'class_id' => 'required|exists:classes,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Custom duplicate name check within the same term and class (excluding current)
        $existing = Assessment::where('assessment_name', $request->assessment_name)
            ->where('term', $request->term)
            ->where('class_id', $request->class_id)
            ->where('id', '!=', $id)
            ->first();

        if ($existing) {
            return back()->withErrors([
                'assessment_name' => 'This assessment name already exists in this term and class.'
            ])->withInput();
        }

        // Get the current total weight for the specified term and class, excluding the current assessment
        $currentTotalWeight = Assessment::where('class_id', $request->class_id)
            ->where('term', $request->term)
            ->where('id', '!=', $id)
            ->sum('weight');

        if (($currentTotalWeight + $request->weight) > 100) {
            return back()->withErrors([
                'weight' => "Total weight for the {$request->term} term cannot exceed 100%"
            ])->withInput();
        }

        $assessment->update([
            'assessment_name' => $request->assessment_name,
            'term' => $request->term,
            'weight' => $request->weight,
            'class_id' => $request->class_id,
        ]);

        return redirect()->back()->with('success', 'Assessment updated successfully.');
    }

    //delete assessment
    public function destroyAssessment($id)
    {
        $item = Assessment::findOrFail($id);
        $item->delete();

        return redirect()->back()->with('success', 'Assessment deleted successfully.');
    }


    // public function storeItems(Request $request)
    // {
    //     $validated = $request->validate([
    //         'assessment_id' => 'required|exists:assessments,id',
    //         'name' => 'required|string|max:255|unique:assessment_items','name',
    //         'total_score' => 'required|integer|min:1',
    //     ]);

    //     $item = AssessmentItem::create($validated);

    //     return redirect()->back()->with('success', 'Assessment Item added successfully.');
    // }

    public function storeItems(Request $request)
    {
        // Step 1: Basic validation (no uniqueness yet)
        $validated = $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'name' => 'required|string|max:255',
            'total_score' => 'required|integer|min:1',
        ]);

        // Step 2: Retrieve the related assessment to get class_id and term
        $assessment = Assessment::findOrFail($request->assessment_id);

        // Step 3: Check if an item with the same name exists under the same class and term
        $duplicate = AssessmentItem::where('name', $request->name)
            ->whereHas('assessment', function ($query) use ($assessment) {
                $query->where('term', $assessment->term)
                    ->where('class_id', $assessment->class_id);
            })
            ->exists();

        if ($duplicate) {
            return redirect()->back()
                ->withErrors(['name' => 'This assessment item name already exists in this class and term.'])
                ->withInput();
        }

        // Step 4: Create the item
        $item = AssessmentItem::create($validated);

        return redirect()->back()->with('success', 'Assessment Item added successfully.');
    }


    // Delete an item
    public function destroyItems($id)
    {
        $item = AssessmentItem::findOrFail($id);
        $item->delete();

        return redirect()->back()->with('success', 'Assessment Item deleted successfully.');
    }
}

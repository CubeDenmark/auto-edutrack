<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassModel; // Ensure you have a model for the classes table
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClassController extends Controller
{
    public function index()
    {
        $professorId = Auth::id();

        $classes = ClassModel::where('professor_id', $professorId)->get();

        return response()->json([
            'classes' => $classes
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string|unique:classes,id',
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:10',
            'term' => 'required|string|max:255',
            'description' => 'nullable|string',
            'schedule' => 'nullable|json', // Expecting a JSON string
            'room' => 'nullable|string|max:100',
            'max_students' => 'nullable|integer|min:1',
            'professor_id' => 'nullable|exists:users,id',
        ]);

        // Decode and validate schedule
        if ($request->filled('schedule')) {
            $scheduleArray = json_decode($request->schedule, true);

            if (!is_array($scheduleArray)) {
                return back()->withErrors(['schedule' => 'Invalid schedule format.'])->withInput();
            }

            // Check for duplicate day-time combinations
            $dayTimeSet = [];

            foreach ($scheduleArray as $entry) {
                if (!isset($entry['day']) || !isset($entry['time'])) {
                    return back()->withErrors(['schedule' => 'Each schedule entry must include day and time.'])->withInput();
                }

                $key = strtolower($entry['day']) . '_' . $entry['time'];

                if (in_array($key, $dayTimeSet)) {
                    return back()->withErrors(['schedule' => 'Duplicate day and time entries are not allowed.'])->withInput();
                }

                $dayTimeSet[] = $key;
            }

            $validated['schedule'] = $scheduleArray;
        }

        ClassModel::create($validated);

        return redirect()->back()->with('success', 'Class added successfully!');
    }


    /**
     * Update the specified class.
     */

    public function update(Request $request, $id)
    {
        $class = ClassModel::findOrFail($id);

        if ($class->professor_id != Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'id' => ['required', 'string', Rule::unique('classes')->ignore($id, 'id')],
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:10',
            'term' => 'required|string|max:255',
            'description' => 'nullable|string',
            'schedule' => 'nullable', // We'll handle it manually
            'room' => 'nullable|string|max:100',
            'max_students' => 'nullable|integer|min:0',
            'professor_id' => 'nullable|exists:users,id',
        ]);

        // 🧠 Decode schedule and validate for duplicates
        if ($request->has('schedule')) {
            $schedule = $request->schedule;

            if (is_string($schedule)) {
                $decoded = json_decode($schedule, true);
                $validated['schedule'] = is_array($decoded) ? $decoded : [];
            } elseif (is_array($schedule)) {
                $validated['schedule'] = $schedule;
            } else {
                $validated['schedule'] = [];
            }

            // ✅ Check for duplicate day & time
            $combinations = [];
            foreach ($validated['schedule'] as $entry) {
                if (!isset($entry['day']) || !isset($entry['time'])) {
                    return back()->withErrors(['schedule' => 'Each schedule entry must include both day and time.'])->withInput();
                }

                $combo = $entry['day'] . '-' . $entry['time'];
                if (in_array($combo, $combinations)) {
                    return back()->withErrors(['schedule' => 'Duplicate day and time entries are not allowed.'])->withInput();
                }
                $combinations[] = $combo;
            }
        }

        $updateData = [
            'id' => $validated['id'],
            'name' => $validated['name'],
            'icon' => $validated['icon'],
            'term' => $validated['term'],
            'description' => $validated['description'] ?? $class->description,
            'room' => $validated['room'] ?? $class->room,
            'max_students' => $validated['max_students'] ?? $class->max_students,
            'professor_id' => $validated['professor_id'] ?? $class->professor_id,
            'schedule' => $validated['schedule'] ?? $class->schedule,
        ];

        $class->update($updateData);

        return redirect()->back()->with('success', 'Class updated successfully!');
    }



    /**
     * Delete the specified class.
     */
    public function destroy($id)
    {
        // Find the class
        $class = ClassModel::findOrFail($id);

        // Check if the authenticated user is the professor of this class
        if ($class->professor_id != Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Delete the class
        $class->delete();

        // Return an empty response with a 200 status code
        // return response()->noContent();
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SubjectController extends Controller
{
    /**
     * Display a listing of all subjects.
     */
    public function index()
    {
        $query = Subject::query();

        // Filter by year
        if (request('year')) {
            $query->where('year', request('year'));
        }

        // Filter by semester
        if (request('semester')) {
            $query->where('semester', request('semester'));
        }

        $subjects = $query->orderBy('year', 'desc')
            ->orderBy('semester', 'desc')
            ->orderBy('subject_code', 'asc')
            ->paginate(12);

        // Get unique years for filter
        $years = Subject::select('year')->distinct()->orderBy('year', 'desc')->get();

        // Get unique semesters for filter
        $semesters = [1, 2, 3];

        return view('admin.subjects.index', compact('subjects', 'years', 'semesters'));
    }

    /**
     * Show the form for creating a new subject.
     */
    public function create()
    {
        $currentYear = intval(date('Y')) + 543; // Convert to Buddhist year
        $currentSemester = 1; // Assume semester 1 by default

        return view('admin.subjects.create', compact('currentYear', 'currentSemester'));
    }

    /**
     * Store a newly created subject in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_code' => 'required|string|unique:subjects|max:50',
            'subject_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'semester' => 'required|integer|in:1,2,3',
            'year' => 'required|integer',
            'is_enabled' => 'boolean',
            'open_date' => 'nullable|date',
            'close_date' => 'nullable|date|after:open_date',
            'access_open_date' => 'nullable|date',
            'access_close_date' => 'nullable|date|after:access_open_date',
            'evaluation_open_date' => 'nullable|date',
            'evaluation_close_date' => 'nullable|date|after:evaluation_open_date',
            'grade_edit_open_date' => 'nullable|date',
            'grade_edit_close_date' => 'nullable|date|after:grade_edit_open_date',
        ]);

        $validated['is_enabled'] = $request->boolean('is_enabled');

        Subject::create($validated);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'สร้างรายวิชา ' . $request->subject_name . ' สำเร็จ');
    }

    /**
     * Show the form for editing the specified subject.
     */
    public function edit(Subject $subject)
    {
        return view('admin.subjects.edit', compact('subject'));
    }

    /**
     * Update the specified subject in storage.
     */
    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'subject_code' => 'required|string|max:50|unique:subjects,subject_code,' . $subject->subject_id . ',subject_id',
            'subject_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'semester' => 'required|integer|in:1,2,3',
            'year' => 'required|integer',
            'is_enabled' => 'boolean',
            'open_date' => 'nullable|date',
            'close_date' => 'nullable|date|after:open_date',
            'access_open_date' => 'nullable|date',
            'access_close_date' => 'nullable|date|after:access_open_date',
            'evaluation_open_date' => 'nullable|date',
            'evaluation_close_date' => 'nullable|date|after:evaluation_open_date',
            'grade_edit_open_date' => 'nullable|date',
            'grade_edit_close_date' => 'nullable|date|after:grade_edit_open_date',
        ]);

        $validated['is_enabled'] = $request->boolean('is_enabled');

        $subject->update($validated);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'อัพเดตรายวิชา ' . $subject->subject_name . ' สำเร็จ');
    }

    /**
     * Toggle the enabled status of the subject (AJAX).
     */
    public function toggle(Subject $subject)
    {
        $subject->update(['is_enabled' => !$subject->is_enabled]);

        $status = $subject->is_enabled ? 'เปิด' : 'ปิด';

        return response()->json([
            'success' => true,
            'is_enabled' => $subject->is_enabled,
            'message' => $status . 'ใช้งานรายวิชา ' . $subject->subject_name . ' สำเร็จ',
        ]);
    }

    /**
     * Remove the specified subject from storage.
     */
    public function destroy(Subject $subject)
    {
        $name = $subject->subject_name;
        $subject->delete();

        return redirect()->route('admin.subjects.index')
            ->with('success', 'ลบรายวิชา ' . $name . ' สำเร็จ');
    }
}

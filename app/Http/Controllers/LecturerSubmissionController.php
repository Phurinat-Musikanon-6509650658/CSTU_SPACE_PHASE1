<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LecturerSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Project::whereNotNull('submission_file')
            ->whereHas('projectLecturers', fn($q) => $q->where('user_code', $user->user_code))
            ->with(['group.members.student', 'advisorLecturer.user', 'committeeLecturers.user']);

        if ($request->filled('year'))     $query->whereHas('group', fn($q) => $q->where('year', $request->year));
        if ($request->filled('semester')) $query->whereHas('group', fn($q) => $q->where('semester', $request->semester));
        if ($request->filled('subject'))  $query->whereHas('group', fn($q) => $q->where('subject_code', $request->subject));

        $submissions = $query->get();

        $groupIds  = Project::whereNotNull('submission_file')
            ->whereHas('projectLecturers', fn($q) => $q->where('user_code', $user->user_code))
            ->pluck('group_id');
        $years     = \Illuminate\Support\Facades\DB::table('groups')->whereIn('group_id', $groupIds)->distinct()->orderBy('year', 'desc')->pluck('year');
        $semesters = \Illuminate\Support\Facades\DB::table('groups')->whereIn('group_id', $groupIds)->distinct()->orderBy('semester')->pluck('semester');
        $subjects  = \Illuminate\Support\Facades\DB::table('groups')->whereIn('group_id', $groupIds)->distinct()->orderBy('subject_code')->pluck('subject_code');

        return view('submissions.lecturer.index', [
            'submissions'      => $submissions,
            'totalSubmissions' => $submissions->count(),
            'years'            => $years,
            'semesters'        => $semesters,
            'subjects'         => $subjects,
        ]);
    }

    public function show($project_id)
    {
        $user = Auth::user();

        $project = Project::with(['group', 'advisorLecturer.user', 'committeeLecturers.user'])
            ->findOrFail($project_id);

        $isRelated = $project->projectLecturers()->where('user_code', $user->user_code)->exists();
        if (!$isRelated) {
            abort(403, 'Unauthorized');
        }

        if (!$project->submission_file) {
            abort(404, 'Submission not found');
        }

        return view('submissions.lecturer.show', ['project' => $project]);
    }

    public function download($project_id)
    {
        $user = Auth::user();

        $project = Project::findOrFail($project_id);

        $isRelated = $project->projectLecturers()->where('user_code', $user->user_code)->exists();
        if (!$isRelated) {
            abort(403, 'Unauthorized');
        }

        if (!$project->submission_file) {
            abort(404, 'Submission file not found');
        }

        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($project->submission_file)) {
            abort(404, 'File not found');
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download(
            $project->submission_file,
            $project->submission_original_name ?? 'submission.pdf'
        );
    }
}

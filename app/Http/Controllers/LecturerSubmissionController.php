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

        // Show submitted projects where user is advisor OR any committee member
        $submissions = Project::whereNotNull('submission_file')
            ->whereHas('projectLecturers', fn($q) => $q->where('user_code', $user->user_code))
            ->with(['group', 'advisorLecturer.user', 'committeeLecturers.user'])
            ->get();

        // Group by year and semester
        $grouped = $submissions->groupBy(function($item) {
            return $item->group->year;
        })->map(function($yearGroup) {
            return $yearGroup->groupBy(function($item) {
                return $item->group->semester;
            })->sortKeys()->reverse();
        })->sortKeys()->reverse();

        return view('submissions.lecturer.index', [
            'grouped' => $grouped,
            'totalSubmissions' => $submissions->count()
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

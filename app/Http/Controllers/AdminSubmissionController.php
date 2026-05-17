<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class AdminSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $submissions = Project::whereNotNull('submission_file')
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

        return view('submissions.admin.index', [
            'grouped' => $grouped,
            'totalSubmissions' => $submissions->count()
        ]);
    }

    public function show($project_id)
    {
        $project = Project::with(['group', 'advisorLecturer.user', 'committeeLecturers.user'])
            ->findOrFail($project_id);

        if (!$project->submission_file) {
            abort(404, 'Submission not found');
        }

        return view('submissions.admin.show', ['project' => $project]);
    }

    public function download($project_id)
    {
        $project = Project::findOrFail($project_id);

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

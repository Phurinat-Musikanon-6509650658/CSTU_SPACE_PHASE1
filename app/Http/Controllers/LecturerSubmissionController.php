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

        // Get submitted projects where user is the advisor
        $submissions = Project::whereNotNull('submission_file')
            ->whereHas('advisorLecturer', fn($q) => $q->where('user_code', $user->user_code))
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

        if ($project->advisor_code !== $user->user_code) {
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

        // Check if user is the advisor
        if ($project->advisor_code !== $user->user_code) {
            abort(403, 'Unauthorized');
        }

        if (!$project->submission_file) {
            abort(404, 'Submission file not found');
        }

        $filePath = 'submissions/' . $project->submission_file;
        
        if (!\Storage::exists($filePath)) {
            abort(404, 'File not found');
        }

        return \Storage::download($filePath, $project->submission_original_name ?? 'submission.pdf');
    }
}

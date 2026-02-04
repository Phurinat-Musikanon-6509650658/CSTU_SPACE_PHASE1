<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoordinatorSubmissionController extends Controller
{
    public function index(Request $request)
    {
        // Get all submitted projects
        $submissions = Project::whereNotNull('submission_file')
            ->with([
                'group',
                'advisor',
                'committee1',
                'committee2',
                'committee3'
            ])
            ->get();

        // Group by year and semester
        $grouped = $submissions->groupBy(function($item) {
            return $item->group->year;
        })->map(function($yearGroup) {
            return $yearGroup->groupBy(function($item) {
                return $item->group->semester;
            })->sortKeys()->reverse();
        })->sortKeys()->reverse();

        return view('submissions.coordinator.index', [
            'grouped' => $grouped,
            'totalSubmissions' => $submissions->count()
        ]);
    }

    public function show($project_id)
    {
        $project = Project::with([
            'group',
            'advisor',
            'committee1',
            'committee2',
            'committee3'
        ])->findOrFail($project_id);

        if (!$project->submission_file) {
            abort(404, 'Submission not found');
        }

        return view('submissions.coordinator.show', ['project' => $project]);
    }

    public function download($project_id)
    {
        $project = Project::findOrFail($project_id);

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

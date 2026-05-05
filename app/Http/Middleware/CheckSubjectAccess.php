<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Subject;
use App\Helpers\SubjectTimingHelper;

class CheckSubjectAccess
{
    public function handle(Request $request, Closure $next, $subjectCode = null): Response
    {
        // Web users (admin/staff) always bypass
        if (auth('web')->check()) {
            $user = auth('web')->user();
            if (($user->role & 32768) !== 0 || ($user->role & 4096) !== 0) {
                return $next($request);
            }
        }

        // For students: auto-detect subject from their group if not given
        if (!$subjectCode && auth('student')->check()) {
            $student = auth('student')->user();
            $group = $student->groups()->first();
            if ($group && $group->subject_code) {
                $subjectCode = $group->subject_code;
            }
        }

        if (!$subjectCode) {
            return $next($request);
        }

        $subject = Subject::where('subject_code', $subjectCode)->first();

        if (!$subject) {
            return $next($request);
        }

        if (!$subject->isAccessibleNow()) {
            $lockMessage = SubjectTimingHelper::getAccessLockMessage($subject);
            return response()->view('errors.subject-locked', [
                'subject' => $subject,
                'message' => $lockMessage,
                'code' => 403,
            ], 403);
        }

        return $next($request);
    }
}

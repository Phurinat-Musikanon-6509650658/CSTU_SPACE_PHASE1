<?php

namespace App\Helpers;

use App\Models\Subject;
use Carbon\Carbon;

class SubjectTimingHelper
{
    /**
     * Check if a subject is accessible for students/users
     */
    public static function isAccessible(Subject $subject): bool
    {
        return $subject->isAccessibleNow();
    }

    /**
     * Check if a subject can be evaluated by lecturers
     */
    public static function canEvaluate(Subject $subject): bool
    {
        return $subject->canEvaluateNow();
    }

    /**
     * Check if grades can be edited
     */
    public static function canEditGrade(Subject $subject): bool
    {
        return $subject->canEditGradeNow();
    }

    /**
     * Get lock message for student/user
     */
    public static function getAccessLockMessage(Subject $subject): ?string
    {
        if (!$subject->is_enabled) {
            return 'รายวิชานี้ปิดใช้งานแล้ว';
        }

        $now = Carbon::now();

        if ($subject->access_open_date && $now->isBefore($subject->access_open_date)) {
            return 'รายวิชานี้ยังไม่เปิด จะเปิดเวลา ' . $subject->access_open_date->format('d/m/Y H:i');
        }

        if ($subject->access_close_date && $now->isAfter($subject->access_close_date)) {
            return 'รายวิชานี้ปิดแล้วเวลา ' . $subject->access_close_date->format('d/m/Y H:i');
        }

        return null;
    }

    /**
     * Get lock message for evaluation
     */
    public static function getEvaluationLockMessage(Subject $subject): ?string
    {
        if (!$subject->is_enabled) {
            return 'รายวิชานี้ปิดใช้งานแล้ว';
        }

        $now = Carbon::now();

        if ($subject->evaluation_open_date && $now->isBefore($subject->evaluation_open_date)) {
            return 'ยังไม่เปิดให้ประเมินคะแนน จะเปิดเวลา ' . $subject->evaluation_open_date->format('d/m/Y H:i');
        }

        if ($subject->evaluation_close_date && $now->isAfter($subject->evaluation_close_date)) {
            return 'ปิดการประเมินคะแนนแล้วเวลา ' . $subject->evaluation_close_date->format('d/m/Y H:i');
        }

        return null;
    }

    /**
     * Get lock message for grade edit
     */
    public static function getGradeEditLockMessage(Subject $subject): ?string
    {
        if (!$subject->is_enabled) {
            return 'รายวิชานี้ปิดใช้งานแล้ว';
        }

        $now = Carbon::now();

        if ($subject->grade_edit_open_date && $now->isBefore($subject->grade_edit_open_date)) {
            return 'ยังไม่เปิดให้แก้ไขคะแนน จะเปิดเวลา ' . $subject->grade_edit_open_date->format('d/m/Y H:i');
        }

        if ($subject->grade_edit_close_date && $now->isAfter($subject->grade_edit_close_date)) {
            return 'ปิดการแก้ไขคะแนนแล้วเวลา ' . $subject->grade_edit_close_date->format('d/m/Y H:i');
        }

        return null;
    }

    /**
     * Get detailed timing information for a subject
     */
    public static function getTimingInfo(Subject $subject): array
    {
        return [
            'subject_code' => $subject->subject_code,
            'subject_name' => $subject->subject_name,
            'is_enabled' => $subject->is_enabled,
            'access' => [
                'is_accessible' => $subject->isAccessibleNow(),
                'open_date' => $subject->access_open_date,
                'close_date' => $subject->access_close_date,
                'status' => $subject->getAccessStatusAttribute(),
                'lock_message' => self::getAccessLockMessage($subject),
            ],
            'evaluation' => [
                'can_evaluate' => $subject->canEvaluateNow(),
                'open_date' => $subject->evaluation_open_date,
                'close_date' => $subject->evaluation_close_date,
                'status' => $subject->getEvaluationStatusAttribute(),
                'lock_message' => self::getEvaluationLockMessage($subject),
            ],
            'grade_edit' => [
                'can_edit' => $subject->canEditGradeNow(),
                'open_date' => $subject->grade_edit_open_date,
                'close_date' => $subject->grade_edit_close_date,
                'status' => $subject->getGradeEditStatusAttribute(),
                'lock_message' => self::getGradeEditLockMessage($subject),
            ],
        ];
    }
}

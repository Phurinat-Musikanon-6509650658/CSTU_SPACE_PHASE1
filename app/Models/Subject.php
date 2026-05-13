<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Subject extends Model
{
    use HasFactory;

    protected $table = 'subjects';
    protected $primaryKey = 'subject_id';
    protected $fillable = [
        'subject_code',
        'subject_name',
        'description',
        'semester',
        'year',
        'is_enabled',
        'open_date',
        'close_date',
        'access_open_date',
        'access_close_date',
        'evaluation_open_date',
        'evaluation_close_date',
        'grade_edit_open_date',
        'grade_edit_close_date',
    ];

    protected $casts = [
        'open_date' => 'datetime',
        'close_date' => 'datetime',
        'access_open_date' => 'datetime',
        'access_close_date' => 'datetime',
        'evaluation_open_date' => 'datetime',
        'evaluation_close_date' => 'datetime',
        'grade_edit_open_date' => 'datetime',
        'grade_edit_close_date' => 'datetime',
        'is_enabled' => 'boolean',
    ];

    /**
     * ตรวจสอบว่าอนุญาตให้เข้าใช้งานรายวิชานี้สำหรับ user/student หรือไม่
     */
    public function isAccessibleNow(): bool
    {
        if (!$this->is_enabled) {
            return false;
        }

        $now = Carbon::now();

        // ถ้าไม่ได้กำหนดเวลา ให้เข้าได้
        if (is_null($this->access_open_date) && is_null($this->access_close_date)) {
            return true;
        }

        // ตรวจสอบเวลาเปิด
        if ($this->access_open_date && $now->isBefore($this->access_open_date)) {
            return false;
        }

        // ตรวจสอบเวลาปิด
        if ($this->access_close_date && $now->isAfter($this->access_close_date)) {
            return false;
        }

        return true;
    }

    /**
     * ตรวจสอบว่าอนุญาตให้ประเมินคะแนนได้หรือไม่
     */
    public function canEvaluateNow(): bool
    {
        if (!$this->is_enabled) {
            return false;
        }

        $now = Carbon::now();

        // ถ้าไม่ได้กำหนดเวลา ให้ประเมินได้
        if (is_null($this->evaluation_open_date) && is_null($this->evaluation_close_date)) {
            return true;
        }

        // ตรวจสอบเวลาเปิด
        if ($this->evaluation_open_date && $now->isBefore($this->evaluation_open_date)) {
            return false;
        }

        // ตรวจสอบเวลาปิด
        if ($this->evaluation_close_date && $now->isAfter($this->evaluation_close_date)) {
            return false;
        }

        return true;
    }

    /**
     * ตรวจสอบว่าอนุญาตให้แก้ไขคะแนนได้หรือไม่
     */
    public function canEditGradeNow(): bool
    {
        if (!$this->is_enabled) {
            return false;
        }

        $now = Carbon::now();

        // ถ้าไม่ได้กำหนดเวลา ให้แก้ไขได้
        if (is_null($this->grade_edit_open_date) && is_null($this->grade_edit_close_date)) {
            return true;
        }

        // ตรวจสอบเวลาเปิด
        if ($this->grade_edit_open_date && $now->isBefore($this->grade_edit_open_date)) {
            return false;
        }

        // ตรวจสอบเวลาปิด
        if ($this->grade_edit_close_date && $now->isAfter($this->grade_edit_close_date)) {
            return false;
        }

        return true;
    }

    /**
     * ตรวจสอบว่ากำลังอยู่ในช่วงการประเมินหรือไม่
     */
    public function isInEvaluationPeriod(): bool
    {
        $now = Carbon::now();

        if ($this->evaluation_open_date && $this->evaluation_close_date) {
            return $now->isBetween($this->evaluation_open_date, $this->evaluation_close_date);
        }

        return false;
    }

    /**
     * ตรวจสอบว่ากำลังอยู่ในช่วงแก้ไขคะแนนหรือไม่
     */
    public function isInGradeEditPeriod(): bool
    {
        $now = Carbon::now();

        if ($this->grade_edit_open_date && $this->grade_edit_close_date) {
            return $now->isBetween($this->grade_edit_open_date, $this->grade_edit_close_date);
        }

        return false;
    }

    /**
     * Get status string สำหรับ access
     */
    public function getAccessStatusAttribute(): string
    {
        if (!$this->is_enabled) {
            return 'ปิดใช้งาน';
        }

        $now = Carbon::now();

        if ($this->access_open_date && $now->isBefore($this->access_open_date)) {
            return 'ยังไม่เปิด';
        }

        if ($this->access_close_date && $now->isAfter($this->access_close_date)) {
            return 'ปิดแล้ว';
        }

        return 'เปิดใช้งาน';
    }

    /**
     * Get status string สำหรับ evaluation
     */
    public function getEvaluationStatusAttribute(): string
    {
        if (!$this->is_enabled) {
            return 'ปิดใช้งาน';
        }

        $now = Carbon::now();

        if ($this->evaluation_open_date && $now->isBefore($this->evaluation_open_date)) {
            return 'ยังไม่เปิด';
        }

        if ($this->evaluation_close_date && $now->isAfter($this->evaluation_close_date)) {
            return 'ปิดแล้ว';
        }

        return 'เปิดอยู่';
    }

    /**
     * Get status string สำหรับ grade edit
     */
    public function getGradeEditStatusAttribute(): string
    {
        if (!$this->is_enabled) {
            return 'ปิดใช้งาน';
        }

        $now = Carbon::now();

        if ($this->grade_edit_open_date && $now->isBefore($this->grade_edit_open_date)) {
            return 'ยังไม่เปิด';
        }

        if ($this->grade_edit_close_date && $now->isAfter($this->grade_edit_close_date)) {
            return 'ปิดแล้ว';
        }

        return 'เปิดอยู่';
    }

    /**
     * Get status color for view
     */
    public function getStatusColor(): string
    {
        if (!$this->is_enabled) {
            return 'secondary';
        }

        return 'success';
    }

    /**
     * Get status text for view
     */
    public function getStatusText(): string
    {
        if (!$this->is_enabled) {
            return 'ปิดใช้งาน';
        }

        return 'เปิดใช้งาน';
    }

    /**
     * Get group count for this subject
     */
    public function getGroupCount(): int
    {
        return \DB::table('groups')
            ->where('subject_code', $this->subject_code)
            ->where('year',     $this->year)
            ->where('semester', $this->semester)
            ->count();
    }
}

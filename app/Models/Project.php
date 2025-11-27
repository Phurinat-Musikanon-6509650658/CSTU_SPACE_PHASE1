<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'projects';
    protected $primaryKey = 'project_id';
    
    protected $fillable = [
        'group_id',
        'project_name',
        'project_code',
        'advisor_code',
        'committee1_code',
        'committee2_code',
        'committee3_code',
        'exam_datetime',
        'student_type',
        'status_project',
        'project_type',
        'submission_file',
        'submission_original_name',
        'submitted_at',
        'submitted_by'
    ];

    protected $casts = [
        'exam_datetime' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    // Relationships
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }

    public function advisor()
    {
        return $this->belongsTo(User::class, 'advisor_code', 'user_code')
            ->where('role', 8192); // Lecturer role only
    }

    public function committee1()
    {
        return $this->belongsTo(User::class, 'committee1_code', 'user_code')
            ->where('role', 8192); // Lecturer role only
    }

    public function committee2()
    {
        return $this->belongsTo(User::class, 'committee2_code', 'user_code')
            ->where('role', 8192); // Lecturer role only
    }

    public function committee3()
    {
        return $this->belongsTo(User::class, 'committee3_code', 'user_code')
            ->where('role', 8192); // Lecturer role only
    }

    public function examSchedule()
    {
        return $this->hasOne(ExamSchedule::class, 'project_id', 'project_id');
    }

    public function evaluations()
    {
        return $this->hasMany(ProjectEvaluation::class, 'project_id', 'project_id');
    }

    public function grade()
    {
        return $this->hasOne(ProjectGrade::class, 'project_id', 'project_id');
    }

    // Accessor สำหรับ ID ที่ coordinator เห็น (format: 01-01 คือ semester-group_id)
    public function getDisplayIdAttribute()
    {
        return sprintf('%02d-%02d', $this->group->semester, $this->group->group_id);
    }

    // Accessor สำหรับ project_code แบบเต็ม (format: 68-1-01_kdc-r1)
    public function getFullProjectCodeAttribute()
    {
        if (!$this->advisor_code) {
            return $this->project_code;
        }

        $memberCount = $this->group->getMemberCount();
        $advisorCode = $this->advisor_code ?? 'xxx';

        return sprintf(
            '%02d-%d-%02d_%s-%s%d',
            $this->group->year,
            $this->group->semester,
            $this->group->group_id,
            $advisorCode,
            $this->student_type,
            $memberCount
        );
    }

    // ดึงสมาชิกคนที่ 1 (คนสร้างกลุ่ม)
    public function getFirstMemberAttribute()
    {
        return $this->group->first_member;
    }

    // ดึงสมาชิกคนที่ 2 (คนที่กดรับคำเชิญ)
    public function getSecondMemberAttribute()
    {
        return $this->group->second_member;
    }

    // Helper methods สำหรับ project_type
    public function getProjectTypesAttribute()
    {
        if (!$this->project_type) {
            return [];
        }
        return array_map('trim', explode(',', $this->project_type));
    }

    public function hasProjectType($type)
    {
        return in_array($type, $this->project_types);
    }

    public function setProjectTypesAttribute($types)
    {
        if (is_array($types)) {
            $this->attributes['project_type'] = implode(',', $types);
        } else {
            $this->attributes['project_type'] = $types;
        }
    }
}

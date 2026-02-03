<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoginLog extends Model
{
    use HasFactory;

    // ปิด auto timestamps เพราะเราจัดการ login_time/logout_time เอง
    public $timestamps = true;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'username',
        'user_type',
        'user_id',
        'student_id',
        'role',
        'ip_address',
        'user_agent',
        'login_status',
        'failure_reason',
        'login_time',
        'logout_time',
        'session_duration'
    ];

    protected $casts = [
        // ไม่ cast datetime เพราะจัดการเอง เพื่อป้องกัน mutation
    ];

    // Scope สำหรับ filter ข้อมูล
    public function scopeSuccessfulLogins($query)
    {
        return $query->where('login_status', 'success');
    }

    public function scopeFailedLogins($query)
    {
        return $query->where('login_status', 'failed');
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('login_time', [$startDate, $endDate]);
    }

    public function scopeActiveToday($query)
    {
        return $query->whereDate('login_time', Carbon::today());
    }

    // Accessors สำหรับแสดงผล
    public function getSessionDurationFormatAttribute()
    {
        if (!$this->session_duration) {
            return 'กำลังใช้งาน';
        }
        
        $totalSeconds = $this->session_duration;
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;
        
        $result = [];
        
        if ($hours > 0) {
            $result[] = $hours . ' ชั่วโมง';
        }
        
        if ($minutes > 0) {
            $result[] = $minutes . ' นาที';
        }
        
        if ($seconds > 0) {
            $result[] = $seconds . ' วินาที';
        }
        
        if (empty($result)) {
            return 'น้อยกว่า 1 วินาที';
        }
        
        return implode(' ', $result);
    }

    public function getLoginTimeFormatAttribute()
    {
        if (!$this->login_time) {
            return '-';
        }
        // ดึงจาก database attributes โดยตรง ไม่ผ่าน carbon cast
        $loginTime = $this->getOriginal('login_time');
        if (!$loginTime) {
            return '-';
        }
        return Carbon::parse($loginTime)->timezone('Asia/Bangkok')->format('d/m/Y H:i:s');
    }

    public function getLogoutTimeFormatAttribute()
    {
        // ดึงจาก database attributes โดยตรง
        $logoutTime = $this->getOriginal('logout_time');
        if (!$logoutTime) {
            return '<span class="text-muted">ยังไม่ logout</span>';
        }
        return Carbon::parse($logoutTime)->timezone('Asia/Bangkok')->format('d/m/Y H:i:s');
    }

    // Static methods สำหรับสร้าง log
    public static function createLoginLog($username, $userType, $userId, $studentId, $role, $status, $failureReason = null)
    {
        // ใช้ timezone Asia/Bangkok
        $loginTime = Carbon::now('Asia/Bangkok')->format('Y-m-d H:i:s');
        
        return self::create([
            'username' => $username,
            'user_type' => $userType,
            'user_id' => $userId,
            'student_id' => $studentId,
            'role' => $role,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'login_status' => $status,
            'failure_reason' => $failureReason,
            'login_time' => $loginTime,
            'created_at' => $loginTime,
            'updated_at' => $loginTime,
        ]);
    }

    public function updateLogoutTime()
    {
        // ดึง login_time จาก database ตรงๆ
        $originalLoginTime = DB::table('login_logs')
            ->where('id', $this->id)
            ->value('login_time');
            
        // ใช้ timezone เดียวกันทั้งหมด
        $loginTime = Carbon::parse($originalLoginTime, 'Asia/Bangkok');
        $logoutTime = Carbon::now('Asia/Bangkok');
        
        // คำนวณระยะเวลา (บังคับให้เป็นค่าบวก)
        $duration = abs($logoutTime->diffInSeconds($loginTime, false));

        // อัพเดทเฉพาะ logout_time และ session_duration เท่านั้น
        // ไม่แตะ login_time เลย
        DB::table('login_logs')
            ->where('id', $this->id)
            ->update([
                'logout_time' => $logoutTime->format('Y-m-d H:i:s'),
                'session_duration' => $duration,
                'updated_at' => $logoutTime->format('Y-m-d H:i:s'),
            ]);
    }
}
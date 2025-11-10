<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LoginLog extends Model
{
    use HasFactory;

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
        'login_time' => 'datetime',
        'logout_time' => 'datetime',
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
        
        $totalMinutes = floor($this->session_duration / 60);
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        $seconds = $this->session_duration % 60;
        
        $result = [];
        
        if ($hours > 0) {
            $result[] = $hours . ' ชั่วโมง';
        }
        
        if ($minutes > 0) {
            $result[] = $minutes . ' นาที';
        }
        
        if ($seconds > 0 && $hours == 0) { // แสดง วินาที เฉพาะเมื่อไม่เกิน 1 ชั่วโมง
            $result[] = $seconds . ' วินาที';
        }
        
        if (empty($result)) {
            return 'น้อยกว่า 1 วินาที';
        }
        
        return implode(' ', $result);
    }

    public function getLoginTimeFormatAttribute()
    {
        return $this->login_time->setTimezone(config('app.timezone'))->format('d/m/Y H:i:s');
    }

    public function getLogoutTimeFormatAttribute()
    {
        return $this->logout_time ? 
               $this->logout_time->setTimezone(config('app.timezone'))->format('d/m/Y H:i:s') : 
               'ยังไม่ logout';
    }

    // Static methods สำหรับสร้าง log
    public static function createLoginLog($username, $userType, $userId, $studentId, $role, $status, $failureReason = null)
    {
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
            'login_time' => now(),
        ]);
    }

    public function updateLogoutTime()
    {
        $loginTime = $this->login_time;
        $logoutTime = now();
        $duration = $logoutTime->diffInSeconds($loginTime);

        $this->update([
            'logout_time' => $logoutTime,
            'session_duration' => $duration,
        ]);
    }
}
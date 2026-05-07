@extends('layouts.app')

@section('title', 'Login Log Detail | CSTU SPACE')

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-file-text-fill me-2"></i>
                    รายละเอียด Login Log
                </h2>
                <p class="mb-0 opacity-75">Log #{{ $log->id }} — {{ $log->username }}</p>
            </div>
            <a href="{{ route('admin.logs.index') }}" class="btn modern-btn btn-light">
                <i class="bi bi-arrow-left"></i>
                <span>กลับ</span>
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Info -->
        <div class="col-md-8">
            <div class="modern-card detail-card">
                <div class="detail-card-header">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>ข้อมูลการ Login</strong>
                </div>
                <div class="detail-card-body">
                    <div class="detail-row">
                        <div class="detail-label">ID</div>
                        <div class="detail-value">
                            <span class="badge bg-light text-dark border">#{{ $log->id }}</span>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Username</div>
                        <div class="detail-value"><strong>{{ $log->username }}</strong></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">User Type</div>
                        <div class="detail-value">
                            <span class="role-badge role-other">{{ ucfirst($log->user_type) }}</span>
                            @if($log->user_id)
                                <small class="text-muted ms-2">(User ID: {{ $log->user_id }})</small>
                            @endif
                            @if($log->student_id)
                                <small class="text-muted ms-2">(Student ID: {{ $log->student_id }})</small>
                            @endif
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Role</div>
                        <div class="detail-value">
                            @php
                                $roleClass = match($log->role) {
                                    'admin'       => 'role-admin',
                                    'coordinator' => 'role-coordinator',
                                    'advisor'     => 'role-advisor',
                                    'student'     => 'role-student',
                                    'staff'       => 'role-staff',
                                    default       => 'role-other'
                                };
                            @endphp
                            <span class="role-badge {{ $roleClass }}">{{ ucfirst($log->role) }}</span>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">สถานะการ Login</div>
                        <div class="detail-value">
                            @if($log->login_status === 'success')
                                <span class="status-badge status-success">
                                    <i class="bi bi-check-circle-fill"></i> สำเร็จ
                                </span>
                            @else
                                <span class="status-badge status-failed">
                                    <i class="bi bi-x-circle-fill"></i> ไม่สำเร็จ
                                </span>
                            @endif
                        </div>
                    </div>
                    @if($log->failure_reason)
                    <div class="detail-row">
                        <div class="detail-label">เหตุผลที่ไม่สำเร็จ</div>
                        <div class="detail-value">
                            <span class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>{{ $log->failure_reason }}</span>
                        </div>
                    </div>
                    @endif
                    <div class="detail-row">
                        <div class="detail-label">เวลา Login</div>
                        <div class="detail-value">{{ $log->login_time_format }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">เวลา Logout</div>
                        <div class="detail-value">
                            @if($log->logout_time)
                                {{ $log->logout_time_format }}
                            @else
                                <span class="text-warning fw-semibold">
                                    <i class="bi bi-hourglass-split me-1"></i>ยังไม่ logout
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="detail-row last">
                        <div class="detail-label">ระยะเวลาใช้งาน</div>
                        <div class="detail-value">
                            @if($log->session_duration)
                                <span class="badge bg-light text-dark border fs-6">
                                    <i class="bi bi-clock me-1"></i>{{ $log->session_duration_format }}
                                </span>
                            @else
                                <span class="status-badge" style="background: var(--gradient-warning); color: white;">
                                    <i class="bi bi-broadcast"></i> กำลังใช้งาน
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Technical Info -->
        <div class="col-md-4">
            <div class="modern-card detail-card mb-4">
                <div class="detail-card-header">
                    <i class="bi bi-globe me-2"></i>
                    <strong>ข้อมูลเครือข่าย</strong>
                </div>
                <div class="detail-card-body">
                    <div class="detail-row last">
                        <div class="detail-label">IP Address</div>
                        <div class="detail-value"><code>{{ $log->ip_address }}</code></div>
                    </div>
                </div>
            </div>

            <div class="modern-card detail-card mb-4">
                <div class="detail-card-header">
                    <i class="bi bi-browser-chrome me-2"></i>
                    <strong>ข้อมูลเบราว์เซอร์</strong>
                </div>
                <div class="detail-card-body">
                    <small class="text-muted" style="word-break: break-all; line-height: 1.6;">{{ $log->user_agent }}</small>
                </div>
            </div>

            <div class="modern-card detail-card">
                <div class="detail-card-header">
                    <i class="bi bi-clock-history me-2"></i>
                    <strong>Timestamps</strong>
                </div>
                <div class="detail-card-body">
                    <div class="detail-row">
                        <div class="detail-label">สร้างเมื่อ</div>
                        <div class="detail-value"><small>{{ $log->created_at->format('d/m/Y H:i:s') }}</small></div>
                    </div>
                    <div class="detail-row last">
                        <div class="detail-label">อัพเดทเมื่อ</div>
                        <div class="detail-value"><small>{{ $log->updated_at->format('d/m/Y H:i:s') }}</small></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Detail Card */
    .detail-card:hover { transform: none; }

    .detail-card-header {
        padding: 1rem 1.5rem;
        border-bottom: 2px solid #f0f0f0;
        color: #2c3e50;
        font-size: 0.95rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #f0f0f0 100%);
    }

    .detail-card-body { padding: 0.5rem 1.5rem; }

    .detail-row {
        display: flex;
        align-items: center;
        padding: 0.875rem 0;
        border-bottom: 1px solid #f5f5f5;
        gap: 1rem;
    }
    .detail-row.last { border-bottom: none; }

    .detail-label {
        width: 40%;
        min-width: 120px;
        font-weight: 600;
        color: #6c757d;
        font-size: 0.875rem;
    }
    .detail-value { flex: 1; }

    /* Role Badges */
    .role-badge {
        padding: 0.3rem 0.75rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    .role-admin      { background: linear-gradient(45deg, #ff6b6b, #ee5a24); color: white; }
    .role-coordinator{ background: linear-gradient(45deg, #4834d4, #686de0); color: white; }
    .role-advisor    { background: linear-gradient(45deg, #0abde3, #006ba6); color: white; }
    .role-staff      { background: linear-gradient(45deg, #f39c12, #e67e22); color: white; }
    .role-student    { background: linear-gradient(45deg, #55a3ff, #003d82); color: white; }
    .role-other      { background: linear-gradient(45deg, #95a5a6, #34495e); color: white; }

    /* Status Badges */
    .status-badge {
        padding: 0.4rem 1rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
    .status-success { background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%); color: white; }
    .status-failed  { background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%); color: white; }
</style>
@endpush

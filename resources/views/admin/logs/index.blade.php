@extends('layouts.app')

@section('title', 'Login Logs | CSTU SPACE')

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-shield-lock-fill me-2"></i>
                    Login Logs
                </h2>
                <p class="mb-0 opacity-75">ประวัติการเข้าสู่ระบบทั้งหมด</p>
            </div>
            <a href="{{ route('menu') }}" class="btn modern-btn btn-light">
                <i class="bi bi-arrow-left"></i>
                <span>กลับ</span>
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card stat-total">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Total Logins</div>
                        <div class="stat-number">{{ number_format($stats['total_logins']) }}</div>
                    </div>
                    <i class="bi bi-people-fill stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Successful Logins</div>
                        <div class="stat-number">{{ number_format($stats['successful_logins']) }}</div>
                    </div>
                    <i class="bi bi-check-circle-fill stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-failed">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Failed Logins</div>
                        <div class="stat-number">{{ number_format($stats['failed_logins']) }}</div>
                    </div>
                    <i class="bi bi-x-circle-fill stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-today">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Today's Logins</div>
                        <div class="stat-number">{{ number_format($stats['today_logins']) }}</div>
                    </div>
                    <i class="bi bi-calendar-day stat-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="modern-card mb-4" style="overflow: visible;">
        <div class="filter-header">
            <i class="bi bi-funnel-fill me-2"></i>
            <strong>ค้นหา / กรองข้อมูล</strong>
        </div>
        <div class="filter-body">
            <form method="GET" action="{{ route('admin.logs.index') }}">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label for="role" class="form-label fw-semibold">Role</label>
                        <select name="role" id="role" class="form-select">
                            <option value="all"  {{ request('role') == 'all'   ? 'selected' : '' }}>ทั้งหมด</option>
                            <option value="32768"{{ request('role') == '32768' ? 'selected' : '' }}>Admin</option>
                            <option value="16384"{{ request('role') == '16384' ? 'selected' : '' }}>Coordinator</option>
                            <option value="8192" {{ request('role') == '8192'  ? 'selected' : '' }}>Lecturer</option>
                            <option value="4096" {{ request('role') == '4096'  ? 'selected' : '' }}>Staff</option>
                            <option value="2048" {{ request('role') == '2048'  ? 'selected' : '' }}>Student</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label fw-semibold">สถานะ</label>
                        <select name="status" id="status" class="form-select">
                            <option value="all"    {{ request('status') == 'all'     ? 'selected' : '' }}>ทั้งหมด</option>
                            <option value="success"{{ request('status') == 'success' ? 'selected' : '' }}>สำเร็จ</option>
                            <option value="failed" {{ request('status') == 'failed'  ? 'selected' : '' }}>ไม่สำเร็จ</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_from" class="form-label fw-semibold">จากวันที่</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="date_to" class="form-label fw-semibold">ถึงวันที่</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="username" class="form-label fw-semibold">Username</label>
                        <input type="text" name="username" id="username" class="form-control" placeholder="ค้นหา username" value="{{ request('username') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn modern-btn btn-primary-modern flex-fill">
                            <i class="bi bi-search"></i>
                            <span>ค้นหา</span>
                        </button>
                        <a href="{{ route('admin.logs.index') }}" class="btn modern-btn btn-light" title="รีเซ็ต">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Section -->
    <div class="section-header">
        <h5 class="mb-0">
            <i class="bi bi-table me-2"></i>
            รายการ Login Logs
            <span class="badge bg-secondary ms-2">{{ $logs->total() }}</span>
        </h5>
        <div class="action-buttons">
            <a href="{{ route('admin.logs.export', request()->all()) }}" class="btn modern-btn btn-success-modern">
                <i class="bi bi-file-earmark-excel"></i>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <div class="modern-card table-modern">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-header">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>IP Address</th>
                        <th>สถานะ</th>
                        <th>เวลา Login</th>
                        <th>เวลา Logout</th>
                        <th>ระยะเวลา</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td><span class="badge bg-light text-dark">{{ $log->id }}</span></td>
                        <td>
                            <strong>{{ $log->username }}</strong>
                            <br><small class="text-muted">{{ ucfirst($log->user_type) }}</small>
                        </td>
                        <td>
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
                        </td>
                        <td><code>{{ $log->ip_address }}</code></td>
                        <td>
                            @if($log->login_status === 'success')
                                <span class="status-badge status-success">
                                    <i class="bi bi-check-circle-fill"></i> สำเร็จ
                                </span>
                            @else
                                <span class="status-badge status-failed">
                                    <i class="bi bi-x-circle-fill"></i> ไม่สำเร็จ
                                </span>
                            @endif
                        </td>
                        <td><small>{{ $log->login_time_format }}</small></td>
                        <td><small>{!! $log->logout_time_format !!}</small></td>
                        <td>
                            @if($log->session_duration)
                                <span class="badge bg-light text-dark border">{{ $log->session_duration_format }}</span>
                            @else
                                <span class="badge" style="background: var(--gradient-warning); color: white;">กำลังใช้งาน</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.logs.show', $log->id) }}" class="btn btn-sm modern-btn btn-primary-modern">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="empty-state">
                                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2 mb-0">ไม่พบข้อมูล Login Logs</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="p-3 d-flex justify-content-center border-top">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Stat Cards */
    .stat-card {
        border-radius: var(--border-radius);
        padding: 1.5rem;
        color: white;
        box-shadow: var(--shadow-light);
        transition: var(--transition);
    }
    .stat-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-hover); }
    .stat-icon  { font-size: 2.5rem; opacity: 0.75; }
    .stat-number{ font-size: 2rem; font-weight: 700; line-height: 1.1; }
    .stat-label { font-size: 0.85rem; opacity: 0.9; margin-bottom: 0.25rem; }
    .stat-total  { background: var(--gradient-primary); }
    .stat-success{ background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%); }
    .stat-failed { background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%); }
    .stat-today  { background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); }

    /* Filter */
    .filter-header {
        padding: 1rem 1.5rem;
        border-bottom: 2px solid #f0f0f0;
        color: #2c3e50;
        font-size: 0.95rem;
    }
    .filter-body { padding: 1.25rem 1.5rem; }

    .form-select {
        border-radius: 15px;
        border: 2px solid #e9ecef;
        padding: 0.75rem 1rem;
        transition: var(--transition);
    }
    .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    /* Section Header */
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding: 1.25rem 1.5rem;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-light);
    }
    .section-header h5 { color: #2c3e50; font-weight: 600; }
    .action-buttons { display: flex; gap: 0.75rem; }

    /* Table */
    .table-header th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        font-weight: 600;
        color: #2c3e50;
        border: none;
        padding: 1rem 0.75rem;
    }
    .table-hover tbody tr { transition: var(--transition); }
    .table-hover tbody tr:hover { background-color: rgba(102, 126, 234, 0.05); }

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
        padding: 0.3rem 0.75rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        white-space: nowrap;
    }
    .status-success { background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%); color: white; }
    .status-failed  { background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%); color: white; }

    .btn-sm.modern-btn { padding: 0.4rem 0.9rem; border-radius: 25px; }
    .empty-state { padding: 1rem; }
</style>
@endpush

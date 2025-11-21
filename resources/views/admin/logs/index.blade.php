@extends('layouts.app')

@section('title', 'Login Logs | CSTU SPACE')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2><i class="bi bi-shield-lock"></i> Login Logs</h2>
                <a href="{{ route('menu') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> กลับ
                </a>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6>Total Logins</h6>
                                    <h3>{{ number_format($stats['total_logins']) }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-people-fill fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6>Successful Logins</h6>
                                    <h3>{{ number_format($stats['successful_logins']) }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-check-circle-fill fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6>Failed Logins</h6>
                                    <h3>{{ number_format($stats['failed_logins']) }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-x-circle-fill fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6>Today's Logins</h6>
                                    <h3>{{ number_format($stats['today_logins']) }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-calendar-day fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-funnel"></i> ตัวกรอง</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.logs.index') }}">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label for="role" class="form-label">Role</label>
                                <select name="role" id="role" class="form-select">
                                    <option value="all" {{ request('role') == 'all' ? 'selected' : '' }}>ทั้งหมด</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->role }}" {{ request('role') == $role->role ? 'selected' : '' }}>
                                            {{ ucfirst($role->role) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">สถานะ</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>ทั้งหมด</option>
                                    <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>สำเร็จ</option>
                                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>ไม่สำเร็จ</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">จากวันที่</label>
                                <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="date_to" class="form-label">ถึงวันที่</label>
                                <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" name="username" id="username" class="form-control" placeholder="ค้นหา username" value="{{ request('username') }}">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bi bi-search"></i> ค้นหา
                                </button>
                                <a href="{{ route('admin.logs.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Export Button -->
            <div class="mb-3">
                <a href="{{ route('admin.logs.export', request()->all()) }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel"></i> Export CSV
                </a>
            </div>

            <!-- Logs Table -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-table"></i> รายการ Login Logs ({{ $logs->total() }} รายการ)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>IP Address</th>
                                    <th>สถานะ</th>
                                    <th>เวลา Login</th>
                                    <th>เวลา Logout</th>
                                    <th>ระยะเวลาใช้งาน</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>
                                        <strong>{{ $log->username }}</strong>
                                        <br><small class="text-muted">{{ ucfirst($log->user_type) }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $roleBadgeClass = match($log->role) {
                                                'admin' => 'bg-danger',
                                                'coordinator' => 'bg-warning',
                                                'advisor' => 'bg-info', 
                                                'student' => 'bg-primary',
                                                'staff' => 'bg-success',
                                                'committee' => 'bg-dark',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $roleBadgeClass }}">{{ ucfirst($log->role) }}</span>
                                    </td>
                                    <td><code>{{ $log->ip_address }}</code></td>
                                    <td>
                                        @if($log->login_status === 'success')
                                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> สำเร็จ</span>
                                        @else
                                            <span class="badge bg-danger"><i class="bi bi-x-circle"></i> ไม่สำเร็จ</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->login_time_format }}</td>
                                    <td>{{ $log->logout_time_format }}</td>
                                    <td>{{ $log->session_duration_format }}</td>
                                    <td>
                                        <a href="{{ route('admin.logs.show', $log->id) }}" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-eye"></i> ดู
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="bi bi-inbox fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">ไม่พบข้อมูล Login Logs</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($logs->hasPages())
                <div class="card-footer">
                    {{ $logs->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
@endpush
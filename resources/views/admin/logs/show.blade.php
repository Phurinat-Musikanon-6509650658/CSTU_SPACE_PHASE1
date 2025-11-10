@extends('layouts.app')

@section('title', 'Admin - Login Log Detail')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2><i class="bi bi-eye"></i> รายละเอียด Login Log #{{ $log->id }}</h2>
                <a href="{{ route('admin.logs.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> กลับ
                </a>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="bi bi-info-circle"></i> ข้อมูลการ Login</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">ID:</th>
                                    <td>{{ $log->id }}</td>
                                </tr>
                                <tr>
                                    <th>Username:</th>
                                    <td><strong>{{ $log->username }}</strong></td>
                                </tr>
                                <tr>
                                    <th>User Type:</th>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst($log->user_type) }}</span>
                                        @if($log->user_id)
                                            <small class="text-muted">(User ID: {{ $log->user_id }})</small>
                                        @endif
                                        @if($log->student_id)
                                            <small class="text-muted">(Student ID: {{ $log->student_id }})</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Role:</th>
                                    <td>
                                        @switch($log->role)
                                            @case('admin')
                                                <span class="badge bg-danger">Admin</span>
                                                @break
                                            @case('coordinator')
                                                <span class="badge bg-warning">Coordinator</span>
                                                @break
                                            @case('advisor')
                                                <span class="badge bg-info">Advisor</span>
                                                @break
                                            @case('student')
                                                <span class="badge bg-primary">Student</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($log->role) }}</span>
                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <th>สถานะการ Login:</th>
                                    <td>
                                        @if($log->login_status === 'success')
                                            <span class="badge bg-success fs-6">
                                                <i class="bi bi-check-circle"></i> สำเร็จ
                                            </span>
                                        @else
                                            <span class="badge bg-danger fs-6">
                                                <i class="bi bi-x-circle"></i> ไม่สำเร็จ
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @if($log->failure_reason)
                                <tr>
                                    <th>เหตุผลที่ไม่สำเร็จ:</th>
                                    <td><span class="text-danger">{{ $log->failure_reason }}</span></td>
                                </tr>
                                @endif
                                <tr>
                                    <th>เวลา Login:</th>
                                    <td>{{ $log->login_time_format }}</td>
                                </tr>
                                <tr>
                                    <th>เวลา Logout:</th>
                                    <td>
                                        @if($log->logout_time)
                                            {{ $log->logout_time_format }}
                                        @else
                                            <span class="text-warning">ยังไม่ logout</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>ระยะเวลาใช้งาน:</th>
                                    <td>
                                        @if($log->session_duration)
                                            <strong>{{ $log->session_duration_format }}</strong>
                                        @else
                                            <span class="text-info">กำลังใช้งาน</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6><i class="bi bi-globe"></i> ข้อมูลเครือข่าย</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th>IP Address:</th>
                                    <td><code>{{ $log->ip_address }}</code></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h6><i class="bi bi-browser-chrome"></i> ข้อมูลเบราว์เซอร์</h6>
                        </div>
                        <div class="card-body">
                            <small class="text-muted">{{ $log->user_agent }}</small>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h6><i class="bi bi-calendar"></i> เวลา</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th>สร้างเมื่อ:</th>
                                    <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>อัพเดทเมื่อ:</th>
                                    <td>{{ $log->updated_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
@endpush
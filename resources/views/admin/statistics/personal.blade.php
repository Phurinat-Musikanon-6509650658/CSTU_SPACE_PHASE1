@extends('layouts.app')

@section('title', 'สถิติส่วนตัว | CSTU SPACE')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="bi bi-graph-up me-2"></i>สถิติการใช้งานส่วนตัว
                </h2>
                <a href="{{ route('menu') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> กลับ
                </a>
            </div>
        </div>
    </div>

    <!-- User Info Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>ข้อมูลผู้ใช้</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Username:</strong></p>
                            <p class="text-muted">{{ $personalStats['username'] }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>อัตราความสำเร็จ:</strong></p>
                            <h4 class="text-success mb-0">{{ $personalStats['success_rate'] }}%</h4>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>เข้าสู่ระบบล่าสุด:</strong></p>
                            <p class="text-muted">
                                @if($personalStats['last_login'])
                                    {{ \Carbon\Carbon::parse($personalStats['last_login']->login_time)->format('d/m/Y H:i:s') }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="bi bi-box-arrow-in-right fs-1 text-primary mb-2"></i>
                    <h3 class="text-primary mb-1">{{ number_format($personalStats['total_logins']) }}</h3>
                    <p class="text-muted mb-0">เข้าสู่ระบบทั้งหมด</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle fs-1 text-success mb-2"></i>
                    <h3 class="text-success mb-1">{{ number_format($personalStats['successful_logins']) }}</h3>
                    <p class="text-muted mb-0">สำเร็จ</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <i class="bi bi-x-circle fs-1 text-danger mb-2"></i>
                    <h3 class="text-danger mb-1">{{ number_format($personalStats['failed_logins']) }}</h3>
                    <p class="text-muted mb-0">ไม่สำเร็จ</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-day fs-1 text-info mb-2"></i>
                    <h3 class="text-info mb-1">{{ number_format($personalStats['today_logins']) }}</h3>
                    <p class="text-muted mb-0">วันนี้</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Period Statistics -->
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-calendar-week me-2"></i>สัปดาห์นี้</h6>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-primary mb-0">{{ number_format($personalStats['week_logins']) }}</h2>
                    <p class="text-muted mb-0">ครั้ง</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-calendar-month me-2"></i>เดือนนี้</h6>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-success mb-0">{{ number_format($personalStats['month_logins']) }}</h2>
                    <p class="text-muted mb-0">ครั้ง</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-percent me-2"></i>อัตราสำเร็จ</h6>
                </div>
                <div class="card-body text-center">
                    <h2 class="mb-0">
                        @if($personalStats['success_rate'] >= 90)
                            <span class="text-success">{{ $personalStats['success_rate'] }}%</span>
                        @elseif($personalStats['success_rate'] >= 70)
                            <span class="text-warning">{{ $personalStats['success_rate'] }}%</span>
                        @else
                            <span class="text-danger">{{ $personalStats['success_rate'] }}%</span>
                        @endif
                    </h2>
                    <p class="text-muted mb-0">
                        @if($personalStats['success_rate'] >= 90)
                            ดีมาก
                        @elseif($personalStats['success_rate'] >= 70)
                            ปานกลาง
                        @else
                            ควรปรับปรุง
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
    .card {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
</style>
@endpush

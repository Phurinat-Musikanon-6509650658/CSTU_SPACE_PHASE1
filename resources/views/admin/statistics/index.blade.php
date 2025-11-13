@extends('layouts.app')

@section('title', 'Statistics Dashboard - CSTU SPACE')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="bi bi-graph-up"></i> Statistics Dashboard</h2>
                    <p class="text-muted mb-0">ภาพรวมการใช้งานระบบ CSTU SPACE</p>
                </div>
                <div>
                    <a href="{{ route('statistics.export') }}" class="btn btn-success me-2">
                        <i class="bi bi-download"></i> Export CSV
                    </a>
                    <a href="{{ route('menu') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> กลับ
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- General Statistics Cards -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-speedometer2 me-2"></i>สถิติรวม
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Total Users -->
                        <div class="col-lg-3 col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="card-title mb-0">ผู้ใช้ทั้งหมด</h6>
                                        <i class="bi bi-people-fill text-primary fs-4"></i>
                                    </div>
                                    <h2 class="text-primary mb-1">{{ number_format($generalStats['total_users']) }}</h2>
                                    <small class="text-muted">จำนวนผู้ใช้ในระบบ</small>
                                </div>
                            </div>
                        </div>

                        <!-- Today's Activity -->
                        <div class="col-lg-3 col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="card-title mb-0">เข้าใช้วันนี้</h6>
                                        <i class="bi bi-calendar-day text-info fs-4"></i>
                                    </div>
                                    <h2 class="text-info mb-1">{{ number_format($generalStats['today_total']) }}</h2>
                                    <small class="text-muted">
                                        {{ number_format($generalStats['today_unique_users']) }} ผู้ใช้ที่แตกต่าง
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Success Rate Today -->
                        <div class="col-lg-3 col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="card-title mb-0">อัตราสำเร็จวันนี้</h6>
                                        <i class="bi bi-check-circle text-success fs-4"></i>
                                    </div>
                                    <h2 class="text-success mb-1">{{ $generalStats['success_rate_today'] }}%</h2>
                                    <small class="text-muted">
                                        {{ number_format($generalStats['today_success']) }}/{{ number_format($generalStats['today_total']) }} 
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Failed Attempts -->
                        <div class="col-lg-3 col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="card-title mb-0">พยายามไม่สำเร็จ</h6>
                                        <i class="bi bi-x-circle text-danger fs-4"></i>
                                    </div>
                                    <h2 class="text-danger mb-1">{{ number_format($generalStats['today_failed']) }}</h2>
                                    <small class="text-muted">วันนี้</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Period Comparison -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-bar-chart me-2"></i>เปรียบเทียบช่วงเวลา
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Today -->
                        <div class="col-lg-4">
                            <div class="card border border-info">
                                <div class="card-header bg-info text-white text-center">
                                    <h6 class="mb-0"><i class="bi bi-calendar-day me-1"></i>วันนี้</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <h5 class="text-info">{{ number_format($generalStats['today_total']) }}</h5>
                                            <small>การเข้าใช้</small>
                                        </div>
                                        <div class="col-4">
                                            <h5 class="text-success">{{ number_format($generalStats['today_success']) }}</h5>
                                            <small>สำเร็จ</small>
                                        </div>
                                        <div class="col-4">
                                            <h5 class="text-primary">{{ number_format($generalStats['today_unique_users']) }}</h5>
                                            <small>ผู้ใช้</small>
                                        </div>
                                    </div>
                                    <div class="text-center mt-2">
                                        <span class="badge bg-info">{{ $generalStats['success_rate_today'] }}% สำเร็จ</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- This Week -->
                        <div class="col-lg-4">
                            <div class="card border border-warning">
                                <div class="card-header bg-warning text-dark text-center">
                                    <h6 class="mb-0"><i class="bi bi-calendar-week me-1"></i>สัปดาห์นี้</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <h5 class="text-warning">{{ number_format($generalStats['week_total']) }}</h5>
                                            <small>การเข้าใช้</small>
                                        </div>
                                        <div class="col-4">
                                            <h5 class="text-success">{{ number_format($generalStats['week_success']) }}</h5>
                                            <small>สำเร็จ</small>
                                        </div>
                                        <div class="col-4">
                                            <h5 class="text-primary">{{ number_format($generalStats['week_unique_users']) }}</h5>
                                            <small>ผู้ใช้</small>
                                        </div>
                                    </div>
                                    <div class="text-center mt-2">
                                        <span class="badge bg-warning text-dark">{{ $generalStats['success_rate_week'] }}% สำเร็จ</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- This Month -->
                        <div class="col-lg-4">
                            <div class="card border border-success">
                                <div class="card-header bg-success text-white text-center">
                                    <h6 class="mb-0"><i class="bi bi-calendar-month me-1"></i>เดือนนี้</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <h5 class="text-success">{{ number_format($generalStats['month_total']) }}</h5>
                                            <small>การเข้าใช้</small>
                                        </div>
                                        <div class="col-4">
                                            <h5 class="text-success">{{ number_format($generalStats['month_success']) }}</h5>
                                            <small>สำเร็จ</small>
                                        </div>
                                        <div class="col-4">
                                            <h5 class="text-primary">{{ number_format($generalStats['month_unique_users']) }}</h5>
                                            <small>ผู้ใช้</small>
                                        </div>
                                    </div>
                                    <div class="text-center mt-2">
                                        <span class="badge bg-success">{{ $generalStats['success_rate_month'] }}% สำเร็จ</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Role-based Statistics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person-badge me-2"></i>สถิติแยกตาม Role
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach(['admin' => 'danger', 'coordinator' => 'primary', 'advisor' => 'info', 'student' => 'success'] as $role => $color)
                        <div class="col-lg-3 col-md-6">
                            <div class="card border-{{ $color }}">
                                <div class="card-header bg-{{ $color }} text-white">
                                    <h6 class="mb-0 text-center">
                                        <i class="bi bi-person-circle me-1"></i>{{ ucfirst($role) }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <h4 class="text-{{ $color }}">{{ number_format($roleStats[$role]['total_users']) }}</h4>
                                        <small class="text-muted">ผู้ใช้ในระบบ</small>
                                    </div>
                                    
                                    <div class="row text-center small">
                                        <div class="col-6">
                                            <strong class="text-{{ $color }}">{{ number_format($roleStats[$role]['today_logins']) }}</strong>
                                            <br><small>Login วันนี้</small>
                                        </div>
                                        <div class="col-6">
                                            <strong class="text-success">{{ number_format($roleStats[$role]['active_today']) }}</strong>
                                            <br><small>Active วันนี้</small>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-2">
                                    
                                    <div class="text-center">
                                        <div class="row">
                                            <div class="col-6">
                                                <span class="badge bg-success">
                                                    {{ $roleStats[$role]['success_rate'] }}%
                                                </span>
                                                <br><small class="text-muted">อัตราสำเร็จ</small>
                                            </div>
                                            <div class="col-6">
                                                @php
                                                    $avgDuration = $roleStats[$role]['avg_session_duration'];
                                                    $hours = floor($avgDuration / 3600);
                                                    $minutes = floor(($avgDuration % 3600) / 60);
                                                @endphp
                                                <span class="text-info">
                                                    @if($hours > 0)
                                                        {{ $hours }}h {{ $minutes }}m
                                                    @elseif($minutes > 0)
                                                        {{ $minutes }}m
                                                    @else
                                                        < 1m
                                                    @endif
                                                </span>
                                                <br><small class="text-muted">เฉลี่ย/Session</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Daily Chart -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-graph-up me-2"></i>สถิติรายวัน (7 วันล่าสุด)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="dailyChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Hourly Chart -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock me-2"></i>สถิติรายชั่วโมง (24 ชั่วโมงล่าสุด)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="hourlyChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Users & Security -->
    <div class="row mb-4">
        <!-- Top Active Users -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-trophy me-2"></i>ผู้ใช้งานมากที่สุดวันนี้
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>อันดับ</th>
                                    <th>ผู้ใช้</th>
                                    <th>Role</th>
                                    <th class="text-end">จำนวน Login</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topUsers['most_active_today'] as $index => $user)
                                <tr>
                                    <td>
                                        @if($index == 0)
                                            <i class="bi bi-trophy-fill text-warning"></i>
                                        @elseif($index == 1)
                                            <i class="bi bi-trophy text-secondary"></i>
                                        @elseif($index == 2)
                                            <i class="bi bi-trophy text-warning"></i>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $user->username }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ ucfirst($user->role) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-success">{{ number_format($user->login_count) }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Alerts -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-shield-exclamation me-2"></i>การรักษาความปลอดภัย
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <h4 class="text-danger">{{ number_format($securityStats['failed_attempts_today']) }}</h4>
                                <small class="text-muted">ความพยายามไม่สำเร็จวันนี้</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <h4 class="text-warning">{{ number_format($securityStats['failed_attempts_last_hour']) }}</h4>
                                <small class="text-muted">ในชั่วโมงที่ผ่านมา</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <h4 class="text-info">{{ number_format($securityStats['suspicious_ips']->count()) }}</h4>
                                <small class="text-muted">IP ที่น่าสงสัย</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 bg-light rounded">
                                <h4 class="text-primary">{{ number_format($securityStats['concurrent_sessions']->count()) }}</h4>
                                <small class="text-muted">Session ซ้ำซ้อน</small>
                            </div>
                        </div>
                    </div>
                    
                    @if($securityStats['suspicious_ips']->count() > 0)
                    <div class="mt-3">
                        <h6 class="text-danger">IP ที่น่าสงสัย:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>IP Address</th>
                                        <th>ความพยายาม</th>
                                        <th>ผู้ใช้</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($securityStats['suspicious_ips']->take(5) as $ip)
                                    <tr>
                                        <td><code>{{ $ip->ip_address }}</code></td>
                                        <td><span class="badge bg-danger">{{ $ip->attempt_count }}</span></td>
                                        <td><span class="badge bg-warning">{{ $ip->unique_usernames }}</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Session Duration Statistics -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-stopwatch me-2"></i>สถิติระยะเวลาการใช้งาน
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <div class="text-center p-3 bg-light rounded">
                                @php
                                    $avgDuration = $sessionStats['avg_duration'];
                                    $hours = floor($avgDuration / 3600);
                                    $minutes = floor(($avgDuration % 3600) / 60);
                                    $seconds = $avgDuration % 60;
                                @endphp
                                <h4 class="text-info">
                                    @if($hours > 0)
                                        {{ $hours }}h {{ $minutes }}m
                                    @elseif($minutes > 0)
                                        {{ $minutes }}m {{ $seconds }}s
                                    @else
                                        {{ $seconds }}s
                                    @endif
                                </h4>
                                <small class="text-muted">เฉลี่ยทั้งหมด</small>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="text-center p-3 bg-light rounded">
                                @php
                                    $minDuration = $sessionStats['min_duration'];
                                    $minMinutes = floor($minDuration / 60);
                                    $minSeconds = $minDuration % 60;
                                @endphp
                                <h4 class="text-success">
                                    @if($minMinutes > 0)
                                        {{ $minMinutes }}m {{ $minSeconds }}s
                                    @else
                                        {{ $minSeconds }}s
                                    @endif
                                </h4>
                                <small class="text-muted">น้อยที่สุด</small>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="text-center p-3 bg-light rounded">
                                @php
                                    $maxDuration = $sessionStats['max_duration'];
                                    $maxHours = floor($maxDuration / 3600);
                                    $maxMinutes = floor(($maxDuration % 3600) / 60);
                                @endphp
                                <h4 class="text-warning">
                                    @if($maxHours > 0)
                                        {{ $maxHours }}h {{ $maxMinutes }}m
                                    @else
                                        {{ $maxMinutes }}m
                                    @endif
                                </h4>
                                <small class="text-muted">มากที่สุด</small>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-primary">{{ number_format($sessionStats['total_sessions']) }}</h4>
                                <small class="text-muted">จำนวน Sessions</small>
                            </div>
                        </div>
                    </div>

                    <!-- Average by Role -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-center mb-3">ระยะเวลาเฉลี่ยแยกตาม Role</h6>
                            <div class="row">
                                @foreach(['admin' => 'danger', 'coordinator' => 'primary', 'advisor' => 'info', 'student' => 'success'] as $role => $color)
                                <div class="col-lg-3 col-md-6">
                                    <div class="text-center p-2 border border-{{ $color }} rounded">
                                        @php
                                            $roleDuration = $sessionStats['avg_duration_by_role'][$role] ?? 0;
                                            $roleHours = floor($roleDuration / 3600);
                                            $roleMinutes = floor(($roleDuration % 3600) / 60);
                                        @endphp
                                        <h5 class="text-{{ $color }}">
                                            @if($roleHours > 0)
                                                {{ $roleHours }}h {{ $roleMinutes }}m
                                            @elseif($roleMinutes > 0)
                                                {{ $roleMinutes }}m
                                            @else
                                                < 1m
                                            @endif
                                        </h5>
                                        <small class="text-muted">{{ ucfirst($role) }}</small>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Daily Chart
const dailyCtx = document.getElementById('dailyChart').getContext('2d');
const dailyChart = new Chart(dailyCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($dailyStats, 'date_format')) !!},
        datasets: [{
            label: 'สำเร็จ',
            data: {!! json_encode(array_column($dailyStats, 'success')) !!},
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'ไม่สำเร็จ',
            data: {!! json_encode(array_column($dailyStats, 'failed')) !!},
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'การใช้งานรายวัน'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Hourly Chart
const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
const hourlyChart = new Chart(hourlyCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_column($hourlyStats, 'hour')) !!},
        datasets: [{
            label: 'การเข้าใช้',
            data: {!! json_encode(array_column($hourlyStats, 'total')) !!},
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'การใช้งานรายชั่วโมง'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<style>
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.badge {
    font-size: 0.75em;
}

.table th {
    border-top: none;
}

.bg-light {
    background-color: #f8f9fa !important;
}

.text-center h4 {
    margin-bottom: 0.25rem;
}

.text-center small {
    display: block;
    margin-top: 0.25rem;
}

canvas {
    max-height: 200px !important;
}
</style>
@endsection
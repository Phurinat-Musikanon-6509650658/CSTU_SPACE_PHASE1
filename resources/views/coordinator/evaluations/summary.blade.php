@extends('layouts.app')

@section('title', 'สรุปคะแนนทั้งหมด - Coordinator')

@push('styles')
<style>
    :root {
        --color-primary: #667eea;
        --color-success: #48bb78;
        --color-warning: #f6ad55;
        --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --shadow-light: 0 8px 25px rgba(0, 0, 0, 0.1);
        --shadow-medium: 0 15px 35px rgba(0, 0, 0, 0.15);
        --border-radius: 20px;
    }

    .page-header {
        background: white;
        border-radius: var(--border-radius);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-light);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--shadow-light);
        transition: all 0.3s ease;
        border-left: 4px solid;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-medium);
    }

    .stat-card.primary { border-left-color: #667eea; }
    .stat-card.success { border-left-color: #48bb78; }
    .stat-card.warning { border-left-color: #f6ad55; }
    .stat-card.info { border-left-color: #4299e1; }

    .stat-icon {
        font-size: 3rem;
        opacity: 0.15;
        position: absolute;
        right: 1.5rem;
        top: 50%;
        transform: translateY(-50%);
    }

    .stat-title {
        font-size: 0.875rem;
        color: #718096;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2c3e50;
    }

    .filter-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--shadow-light);
        margin-bottom: 2rem;
    }

    .project-table-card {
        background: white;
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--shadow-light);
    }

    .table-header {
        background: var(--gradient-primary);
        color: white;
        padding: 1.5rem;
    }

    .modern-table {
        margin-bottom: 0;
    }

    .modern-table thead th {
        background: #f8f9fa;
        font-weight: 600;
        color: #2c3e50;
        padding: 1rem 0.75rem;
        border-bottom: 2px solid #e9ecef;
        white-space: nowrap;
    }

    .modern-table tbody tr {
        transition: all 0.2s ease;
    }

    .modern-table tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }

    .modern-table td {
        padding: 1.25rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }

    .grade-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        font-weight: 800;
        font-size: 1.75rem;
        background: linear-gradient(135deg, #ffd700, #ffed4e);
        color: #2c3e50;
        box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
    }

    .score-display {
        font-weight: 600;
        font-size: 1.1rem;
        color: #2c3e50;
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .status-released {
        background: linear-gradient(135deg, #48bb78, #38a169);
        color: white;
    }

    .status-confirmed {
        background: linear-gradient(135deg, #4299e1, #3182ce);
        color: white;
    }

    .status-pending {
        background: linear-gradient(135deg, #f6ad55, #ed8936);
        color: white;
    }

    .member-list {
        font-size: 0.85rem;
        color: #718096;
    }

    .action-btn {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #718096;
    }

    .empty-state i {
        font-size: 5rem;
        opacity: 0.2;
        margin-bottom: 1.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-clipboard-data-fill me-2"></i>
                    สรุปคะแนนทั้งหมด
                </h2>
                <p class="mb-0 text-muted">ภาพรวมคะแนนและเกรดโครงงานทั้งหมด</p>
            </div>
            <a href="{{ route('coordinator.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                กลับ Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <i class="bi bi-folder-fill stat-icon"></i>
            <div class="stat-title">โครงงานทั้งหมด</div>
            <div class="stat-value">{{ $stats['total_projects'] }}</div>
        </div>

        <div class="stat-card success">
            <i class="bi bi-check-circle-fill stat-icon"></i>
            <div class="stat-title">ปล่อยเกรดแล้ว</div>
            <div class="stat-value">{{ $stats['grade_released'] }}</div>
        </div>

        <div class="stat-card warning">
            <i class="bi bi-hourglass-split stat-icon"></i>
            <div class="stat-title">รอยืนยัน</div>
            <div class="stat-value">{{ $stats['pending_confirmation'] }}</div>
        </div>

        <div class="stat-card info">
            <i class="bi bi-graph-up stat-icon"></i>
            <div class="stat-title">คะแนนเฉลี่ย</div>
            <div class="stat-value">{{ number_format($stats['average_score'] ?? 0, 2) }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('coordinator.evaluations.summary') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">ภาคเรียน</label>
                    <select name="semester" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <option value="1" {{ request('semester') == '1' ? 'selected' : '' }}>1</option>
                        <option value="2" {{ request('semester') == '2' ? 'selected' : '' }}>2</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">ปีการศึกษา</label>
                    <select name="year" class="form-select">
                        <option value="">ทั้งหมด</option>
                        @for($y = 2568; $y >= 2560; $y--)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">สถานะการปล่อยเกรด</label>
                    <select name="grade_released" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <option value="1" {{ request('grade_released') == '1' ? 'selected' : '' }}>ปล่อยแล้ว</option>
                        <option value="0" {{ request('grade_released') == '0' ? 'selected' : '' }}>ยังไม่ปล่อย</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-2"></i>กรองข้อมูล
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Projects Table -->
    <div class="project-table-card">
        <div class="table-header">
            <h5 class="mb-0">
                <i class="bi bi-table me-2"></i>
                รายการคะแนนและเกรด
            </h5>
        </div>

        @if($projects->count() > 0)
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>รหัสโครงงาน</th>
                            <th>ชื่อโครงงาน</th>
                            <th>สมาชิก</th>
                            <th>เกรด</th>
                            <th>คะแนน</th>
                            <th>สถานะ</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projects as $project)
                            <tr>
                                <td>
                                    <code class="text-primary fw-bold">{{ $project->project_code }}</code>
                                </td>
                                <td>
                                    <strong>{{ $project->project_name ?? 'ยังไม่ระบุ' }}</strong>
                                </td>
                                <td>
                                    <div class="member-list">
                                        @foreach($project->group->members as $member)
                                            {{ $member->student->firstname_std ?? 'N/A' }} {{ $member->student->lastname_std ?? '' }}@if(!$loop->last),<br>@endif
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    @if($project->grade)
                                        <div class="grade-badge">
                                            {{ $project->grade->grade }}
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($project->grade)
                                        <div class="score-display">{{ number_format($project->grade->final_score, 2) }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($project->grade)
                                        @if($project->grade->grade_released)
                                            <span class="status-badge status-released">
                                                <i class="bi bi-check-circle-fill"></i>
                                                ปล่อยแล้ว
                                            </span>
                                        @elseif($project->grade->all_confirmed)
                                            <span class="status-badge status-confirmed">
                                                <i class="bi bi-shield-check"></i>
                                                ยืนยันครบ
                                            </span>
                                        @else
                                            <span class="status-badge status-pending">
                                                <i class="bi bi-hourglass-split"></i>
                                                รอยืนยัน
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('coordinator.evaluations.scores', $project->project_id) }}" 
                                           class="action-btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                            ดูคะแนน
                                        </a>
                                        @if($project->grade && $project->grade->all_confirmed && !$project->grade->grade_released)
                                            <form action="{{ route('coordinator.evaluations.release', $project->project_id) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการปล่อยเกรดนี้ให้นักศึกษาดู?');">
                                                @csrf
                                                <button type="submit" class="action-btn btn-sm btn-success">
                                                    <i class="bi bi-send-check"></i>
                                                    ปล่อยเกรด
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4">
                {{ $projects->links() }}
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h5>ไม่พบข้อมูลคะแนน</h5>
                <p class="text-muted">ยังไม่มีโครงงานที่มีเกรด</p>
            </div>
        @endif
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Coordinator Dashboard')

@push('styles')
<style>
    .page-header {
        background: white;
        border-radius: var(--border-radius);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-light);
    }

    .page-header h2 {
        color: #2c3e50;
        font-weight: 700;
        font-size: 2rem;
        margin-bottom: 0.5rem;
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
        transition: var(--transition);
        border-left: 4px solid;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-medium);
    }

    .stat-card.primary {
        border-left-color: #667eea;
    }

    .stat-card.warning {
        border-left-color: #f6ad55;
    }

    .stat-card.success {
        border-left-color: #48bb78;
    }

    .stat-card.info {
        border-left-color: #4299e1;
    }

    .stat-card-icon {
        position: absolute;
        top: 50%;
        right: 1.5rem;
        transform: translateY(-50%);
        font-size: 4rem;
        opacity: 0.1;
    }

    .stat-card-title {
        font-size: 0.875rem;
        color: #718096;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .stat-card-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }

    .stat-card-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.875rem;
        transition: var(--transition);
    }

    .stat-card-link:hover {
        color: #764ba2;
        gap: 0.75rem;
    }

    .modern-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-light);
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .modern-card-header {
        padding: 1.5rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 2px solid #dee2e6;
    }

    .modern-card-header h4 {
        margin: 0;
        color: #2c3e50;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .table-modern {
        margin-bottom: 0;
    }

    .table-modern thead th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        font-weight: 600;
        color: #2c3e50;
        border: none;
        padding: 1rem 0.75rem;
        white-space: nowrap;
    }

    .table-modern tbody tr {
        transition: var(--transition);
    }

    .table-modern tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }

    .table-modern td {
        vertical-align: middle;
        padding: 1rem 0.75rem;
        border-bottom: 1px solid #f0f0f0;
    }

    .badge-modern {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 500;
        font-size: 0.8rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .badge-pending {
        background: linear-gradient(45deg, #f6ad55, #ed8936);
        color: white;
    }

    .badge-approved {
        background: linear-gradient(45deg, #48bb78, #38a169);
        color: white;
    }

    .empty-state {
        padding: 3rem;
        text-align: center;
        color: #718096;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }

    .group-id-badge {
        background: var(--gradient-primary);
        color: white;
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .member-list {
        font-size: 0.85rem;
        color: #718096;
        margin-top: 0.25rem;
    }
</style>
@endpush

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Coordinator Dashboard
                </h2>
                <p class="mb-0 opacity-75">ภาพรวมการจัดการกลุ่มและโครงงาน</p>
            </div>
            <a href="{{ route('menu') }}" class="btn modern-btn btn-light">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Menu</span>
            </a>
        </div>
    </div>

    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <i class="bi bi-people-fill stat-card-icon"></i>
            <div class="stat-card-title">กลุ่มทั้งหมด</div>
            <div class="stat-card-value">{{ $stats['total_groups'] }}</div>
            <a href="{{ route('coordinator.groups.index') }}" class="stat-card-link">
                ดูรายละเอียด
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="stat-card warning">
            <i class="bi bi-clock-fill stat-card-icon"></i>
            <div class="stat-card-title">รออนุมัติ</div>
            <div class="stat-card-value">{{ $stats['pending_groups'] }}</div>
            <a href="{{ route('coordinator.groups.index', ['status' => 'pending']) }}" class="stat-card-link">
                ดูรายละเอียด
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="stat-card success">
            <i class="bi bi-check-circle-fill stat-card-icon"></i>
            <div class="stat-card-title">อนุมัติแล้ว</div>
            <div class="stat-card-value">{{ $stats['approved_groups'] }}</div>
            <a href="{{ route('coordinator.groups.index', ['status' => 'approved']) }}" class="stat-card-link">
                ดูรายละเอียด
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="stat-card info">
            <i class="bi bi-folder-fill stat-card-icon"></i>
            <div class="stat-card-title">โครงงานทั้งหมด</div>
            <div class="stat-card-value">{{ $stats['total_projects'] }}</div>
            <a href="{{ route('coordinator.groups.index') }}" class="stat-card-link">
                ดูรายละเอียด
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    </div>

    <!-- Pending Groups Table -->
    <div class="modern-card">
        <div class="modern-card-header">
            <h4>
                <i class="bi bi-clock text-warning"></i>
                กลุ่มที่รออนุมัติ
            </h4>
        </div>
        <div class="card-body p-0">
            @if($pendingGroups->count() > 0)
                <div class="table-responsive">
                    <table class="table table-modern table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 10%;">Group ID</th>
                                <th style="width: 12%;">รหัสวิชา</th>
                                <th style="width: 10%;">ปี/เทอม</th>
                                <th style="width: 25%;">สมาชิก</th>
                                <th style="width: 12%;">สถานะ</th>
                                <th style="width: 16%;">สร้างเมื่อ</th>
                                <th style="width: 15%; text-align: center;">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingGroups as $group)
                            <tr>
                                <td>
                                    <span class="group-id-badge">
                                        {{ sprintf('%02d-%02d', $group->semester, $group->group_id) }}
                                    </span>
                                </td>
                                <td>
                                    <i class="bi bi-book me-1"></i>
                                    <strong>{{ $group->subject_code }}</strong>
                                </td>
                                <td>
                                    <i class="bi bi-calendar-event me-1"></i>
                                    {{ $group->year }}/{{ $group->semester }}
                                </td>
                                <td>
                                    <div>
                                        <i class="bi bi-people me-1"></i>
                                        <strong>{{ $group->members->count() }}/2 คน</strong>
                                    </div>
                                    <div class="member-list">
                                        @foreach($group->members as $member)
                                            {{ $member->student->firstname_std ?? 'N/A' }}{{ !$loop->last ? ', ' : '' }}
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-modern badge-pending">
                                        <i class="bi bi-hourglass-split"></i>
                                        รออนุมัติ
                                    </span>
                                </td>
                                <td>
                                    <i class="bi bi-clock me-1"></i>
                                    {{ $group->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td style="text-align: center;">
                                    <a href="{{ route('coordinator.groups.show', $group->group_id) }}" class="btn btn-sm modern-btn btn-primary">
                                        <i class="bi bi-eye"></i>
                                        <span>ดูรายละเอียด</span>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p class="mb-0">ไม่มีกลุ่มที่รออนุมัติในขณะนี้</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

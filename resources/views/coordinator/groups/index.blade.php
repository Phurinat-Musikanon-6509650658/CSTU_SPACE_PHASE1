@extends('layouts.app')

@section('title', 'จัดการกลุ่มโครงงาน')

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

    .filter-section {
        padding: 1.5rem;
    }

    .filter-section .form-label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .filter-section .form-select {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        padding: 0.6rem 1rem;
        transition: var(--transition);
    }

    .filter-section .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
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
        font-size: 0.9rem;
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

    .table-modern td small {
        display: block;
        color: #718096;
        font-size: 0.85rem;
        margin-top: 0.25rem;
    }

    .group-id-badge {
        background: var(--gradient-primary);
        color: white;
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-block;
    }

    .badge-modern {
        padding: 0.45rem 0.9rem;
        border-radius: 50px;
        font-weight: 500;
        font-size: 0.8rem;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        white-space: nowrap;
    }

    .badge-not-created {
        background: linear-gradient(45deg, #cbd5e0, #a0aec0);
        color: white;
    }

    .badge-created {
        background: linear-gradient(45deg, #4299e1, #3182ce);
        color: white;
    }

    .badge-member-left {
        background: linear-gradient(45deg, #f6ad55, #ed8936);
        color: white;
    }

    .badge-member-added {
        background: linear-gradient(45deg, #667eea, #764ba2);
        color: white;
    }

    .badge-disbanded {
        background: linear-gradient(45deg, #fc8181, #e53e3e);
        color: white;
    }

    .badge-pending {
        background: linear-gradient(45deg, #f6ad55, #ed8936);
        color: white;
    }

    .badge-approved {
        background: linear-gradient(45deg, #48bb78, #38a169);
        color: white;
    }

    .badge-rejected {
        background: linear-gradient(45deg, #fc8181, #e53e3e);
        color: white;
    }

    .badge-in-progress {
        background: linear-gradient(45deg, #4299e1, #3182ce);
        color: white;
    }

    .badge-submitted {
        background: linear-gradient(45deg, #48bb78, #2f855a);
        color: white;
    }

    .badge-late {
        background: linear-gradient(45deg, #ed8936, #dd6b20);
        color: white;
    }

    .badge-not-proposed {
        background: linear-gradient(45deg, #cbd5e0, #a0aec0);
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

    .pagination-wrapper {
        padding: 1.5rem;
        border-top: 1px solid #e9ecef;
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
                    <i class="bi bi-people-fill me-2"></i>
                    จัดการกลุ่มโครงงาน
                </h2>
                <p class="mb-0 opacity-75">ดูและจัดการกลุ่มโครงงานทั้งหมด</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('coordinator.projects.export.csv', request()->all()) }}" class="btn modern-btn btn-success">
                    <i class="bi bi-file-earmark-spreadsheet"></i>
                    <span>Export CSV</span>
                </a>
                <a href="{{ route('menu') }}" class="btn modern-btn btn-light">
                    <i class="bi bi-arrow-left"></i>
                    <span>Back to Menu</span>
                </a>
            </div>
        </div>
    </div>

    </div>

    <!-- Filter Form -->
    <div class="modern-card">
        <div class="modern-card-header">
            <h4>
                <i class="bi bi-funnel text-primary"></i>
                ค้นหาและกรองข้อมูล
            </h4>
        </div>
        <div class="filter-section">
            <form method="GET" action="{{ route('coordinator.groups.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="bi bi-flag me-1"></i>
                            สถานะ
                        </label>
                        <select name="status" class="form-select">
                            <option value="">ทั้งหมด</option>
                            <option value="created" {{ request('status') == 'created' ? 'selected' : '' }}>สร้างแล้ว</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>รออนุมัติ</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>อนุมัติแล้ว</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>ปฏิเสธ</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="bi bi-book me-1"></i>
                            รหัสวิชา
                        </label>
                        <select name="subject" class="form-select">
                            <option value="">ทั้งหมด</option>
                            <option value="CS303" {{ request('subject') == 'CS303' ? 'selected' : '' }}>CS303</option>
                            <option value="CS403" {{ request('subject') == 'CS403' ? 'selected' : '' }}>CS403</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="bi bi-calendar-event me-1"></i>
                            เทอม
                        </label>
                        <select name="semester" class="form-select">
                            <option value="">ทั้งหมด</option>
                            <option value="1" {{ request('semester') == '1' ? 'selected' : '' }}>ภาคต้น</option>
                            <option value="2" {{ request('semester') == '2' ? 'selected' : '' }}>ภาคปลาย</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn modern-btn btn-primary w-100">
                            <i class="bi bi-search"></i>
                            <span>ค้นหา</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    </div>

    <!-- Groups Table -->
    <div class="modern-card">
        <div class="modern-card-header">
            <h4>
                <i class="bi bi-table text-success"></i>
                รายการกลุ่มทั้งหมด
                <span class="badge bg-primary ms-2">{{ $groups->total() }} กลุ่ม</span>
            </h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-modern table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 8%;">Group ID</th>
                            <th style="width: 8%;">รหัสวิชา</th>
                            <th style="width: 8%;">ปี/เทอม</th>
                            <th style="width: 18%;">ชื่อโครงงาน</th>
                            <th style="width: 12%;">สมาชิก</th>
                            <th style="width: 10%;">สถานะกลุ่ม</th>
                            <th style="width: 12%;">สถานะโครงงาน</th>
                            <th style="width: 14%;">อาจารย์ที่ปรึกษา</th>
                            <th style="width: 10%; text-align: center;">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groups as $group)
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
                                @if($group->project)
                                    <div><strong>{{ $group->project->project_name }}</strong></div>
                                    <small>{{ $group->project->project_code }}</small>
                                @elseif($group->latestProposal)
                                    <div><strong>{{ $group->latestProposal->proposed_title }}</strong></div>
                                    <small class="text-warning">(รอดำเนินการ)</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <i class="bi bi-people me-1"></i>
                                    <strong>{{ $group->members->count() }}/2 คน</strong>
                                </div>
                                <small>
                                    @foreach($group->members as $member)
                                        {{ $member->student->firstname_std ?? 'N/A' }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                </small>
                            </td>
                            <td>
                                @if($group->status_group === 'not_created')
                                    <span class="badge-modern badge-not-created">
                                        <i class="bi bi-circle"></i>
                                        ยังไม่ได้สร้าง
                                    </span>
                                @elseif($group->status_group === 'created')
                                    <span class="badge-modern badge-created">
                                        <i class="bi bi-check-circle"></i>
                                        สร้างแล้ว
                                    </span>
                                @elseif($group->status_group === 'member_left')
                                    <span class="badge-modern badge-member-left">
                                        <i class="bi bi-person-dash"></i>
                                        สมาชิกออกกลุ่ม
                                    </span>
                                @elseif($group->status_group === 'member_added')
                                    <span class="badge-modern badge-member-added">
                                        <i class="bi bi-person-plus"></i>
                                        สมาชิกเพิ่มเข้ามา
                                    </span>
                                @elseif($group->status_group === 'disbanded')
                                    <span class="badge-modern badge-disbanded">
                                        <i class="bi bi-x-circle"></i>
                                        กลุ่มถูกยุบ
                                    </span>
                                @else
                                    <span class="badge-modern badge-not-created">{{ $group->status_group }}</span>
                                @endif
                            </td>
                            <td>
                                @if($group->project && $group->project->status_project)
                                    @if($group->project->status_project === 'not_proposed')
                                        <span class="badge-modern badge-not-proposed">
                                            <i class="bi bi-circle"></i>
                                            ยังไม่ได้กำหนดหัวข้อ
                                        </span>
                                    @elseif($group->project->status_project === 'pending')
                                        <span class="badge-modern badge-pending">
                                            <i class="bi bi-clock"></i>
                                            รอพิจารณา
                                        </span>
                                    @elseif($group->project->status_project === 'approved')
                                        <span class="badge-modern badge-approved">
                                            <i class="bi bi-check-circle"></i>
                                            อนุมัติ
                                        </span>
                                    @elseif($group->project->status_project === 'rejected')
                                        <span class="badge-modern badge-rejected">
                                            <i class="bi bi-x-circle"></i>
                                            ปฏิเสธ
                                        </span>
                                    @elseif($group->project->status_project === 'in_progress')
                                        <span class="badge-modern badge-in-progress">
                                            <i class="bi bi-gear-fill"></i>
                                            กำลังดำเนินงาน
                                        </span>
                                    @elseif($group->project->status_project === 'late_submission')
                                        <span class="badge-modern badge-late">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            ส่งเล่มล่าช้า
                                        </span>
                                    @elseif($group->project->status_project === 'submitted')
                                        <span class="badge-modern badge-submitted">
                                            <i class="bi bi-check-circle-fill"></i>
                                            ส่งเล่มแล้ว
                                        </span>
                                    @else
                                        <span class="badge-modern badge-not-proposed">{{ $group->project->status_project }}</span>
                                    @endif
                                @elseif($group->latestProposal)
                                    @if($group->latestProposal->status === 'pending')
                                        <span class="badge-modern badge-pending">
                                            <i class="bi bi-clock"></i>
                                            รอพิจารณา
                                        </span>
                                    @elseif($group->latestProposal->status === 'approved')
                                        <span class="badge-modern badge-approved">
                                            <i class="bi bi-check-circle"></i>
                                            อนุมัติ
                                        </span>
                                    @else
                                        <span class="badge-modern badge-rejected">
                                            <i class="bi bi-x-circle"></i>
                                            ปฏิเสธ
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($group->latestProposal && $group->latestProposal->lecturer)
                                    <div>
                                        <i class="bi bi-person me-1"></i>
                                        <strong>{{ $group->latestProposal->lecturer->firstname_user }} {{ $group->latestProposal->lecturer->lastname_user }}</strong>
                                    </div>
                                    <small>(ข้อเสนอ)</small>
                                @elseif($group->project && $group->project->advisor)
                                    <div>
                                        <i class="bi bi-person-check me-1"></i>
                                        <strong>{{ $group->project->advisor->firstname_user }} {{ $group->project->advisor->lastname_user }}</strong>
                                    </div>
                                    <small>{{ $group->project->advisor_code }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('coordinator.groups.show', $group->group_id) }}" class="btn btn-sm modern-btn btn-primary">
                                    <i class="bi bi-eye"></i>
                                    <span>ดูรายละเอียด</span>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p class="mb-0">ไม่พบข้อมูลกลุ่มโครงงาน</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($groups->hasPages())
            <div class="pagination-wrapper">
                {{ $groups->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

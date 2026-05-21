@extends('layouts.app')

@section('title', 'ตรวจสอบโครงงาน | CSTU SPACE')

@push('styles')
<style>
    .status-badge {
        font-size: .75rem;
        padding: .3rem .65rem;
        border-radius: 20px;
        font-weight: 600;
        letter-spacing: .02em;
    }
    .form-select-status {
        font-size: .8rem;
        padding: .25rem .5rem;
        border-radius: 8px;
        min-width: 130px;
    }
    .project-table th {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #6c757d;
        border-bottom: 2px solid #e9ecef;
        padding: .75rem 1rem;
        background: #f8f9fa;
    }
    .project-table td {
        padding: .85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
        font-size: .875rem;
    }
    .project-table tbody tr:hover { background: #f8faff; }
    .filter-card { border: none; box-shadow: 0 2px 8px rgba(0,0,0,.07); border-radius: 12px; }
    .main-card  { border: none; box-shadow: 0 2px 10px rgba(0,0,0,.08); border-radius: 12px; overflow: hidden; }
</style>
@endpush

@section('content')
<div class="container">

    {{-- Page Header --}}
    <div style="background:white;border-radius:var(--border-radius);padding:2rem;margin-bottom:2rem;box-shadow:var(--shadow-light);">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 style="color:#2c3e50;font-weight:700;font-size:2rem;margin-bottom:.5rem;">
                    <i class="bi bi-clipboard-check me-2"></i>ตรวจสอบโครงงาน
                </h2>
                <p class="mb-0 opacity-75">ดูรายละเอียดโครงงาน คณะกรรมการ และสถานะทั้งหมด</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('coordinator.projects.export.csv') }}" class="btn btn-success">
                    <i class="bi bi-download me-1"></i>Export CSV
                </a>
                <a href="{{ route('coordinator.dashboard') }}" class="btn" style="background:#f8f9fa;color:#2c3e50;font-weight:600;display:inline-flex;align-items:center;gap:.5rem;border-radius:10px;">
                    <i class="bi bi-arrow-left"></i><span>กลับ Dashboard</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 shadow-sm">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card filter-card mb-4">
        <div class="card-body py-3">
            <form class="row g-2 align-items-end" method="GET" action="{{ route('coordinator.projects.review') }}">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold mb-1">ค้นหาโครงงาน</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control"
                               placeholder="ชื่อโครงงาน หรือ รหัสโครงงาน"
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">สถานะ</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">-- ทั้งหมด --</option>
                        @php
                            $statusLabels = [
                                'not_proposed'    => 'ยังไม่เสนอ',
                                'pending'         => 'รออนุมัติ',
                                'approved'        => 'อนุมัติแล้ว',
                                'rejected'        => 'ถูกปฏิเสธ',
                                'in_progress'     => 'กำลังดำเนินการ',
                                'submitted'       => 'ส่งงานแล้ว',
                                'late_submission'  => 'ส่งงานล่าช้า',
                                'passed'          => 'ผ่าน',
                                'failed'          => 'ไม่ผ่าน',
                            ];
                        @endphp
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                {{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">ปีการศึกษา</label>
                    <select name="year" class="form-select form-select-sm">
                        <option value="">-- ทั้งหมด --</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">เทอม</label>
                    <select name="semester" class="form-select form-select-sm">
                        <option value="">-- ทั้งหมด --</option>
                        @foreach($semesters as $sem)
                            <option value="{{ $sem }}" {{ request('semester') == $sem ? 'selected' : '' }}>เทอม {{ $sem }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-search me-1"></i>ค้นหา
                    </button>
                    <a href="{{ route('coordinator.projects.review') }}" class="btn btn-outline-secondary btn-sm px-3">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card main-card">
        <div class="table-responsive">
            <table class="table project-table mb-0">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="12%">รหัสโครงงาน</th>
                        <th width="22%">ชื่อโครงงาน</th>
                        <th width="17%">อาจารย์ที่ปรึกษา</th>
                        <th width="13%">สถานะ</th>
                        <th width="16%">วันสอบ</th>
                        <th width="10%"></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $isCoordinator = auth()->check() && (auth()->user()->role & 16384 || auth()->user()->role & 32768);
                        $statusColors = [
                            'not_proposed'    => 'secondary',
                            'pending'         => 'warning',
                            'approved'        => 'info',
                            'rejected'        => 'danger',
                            'in_progress'     => 'primary',
                            'submitted'       => 'info',
                            'late_submission'  => 'warning',
                            'passed'          => 'success',
                            'failed'          => 'danger',
                        ];
                    @endphp
                    @forelse($projects as $project)
                        <tr>
                            <td class="text-muted small">
                                {{ ($projects->currentPage() - 1) * $projects->perPage() + $loop->iteration }}
                            </td>
                            <td>
                                <span class="fw-bold text-primary">{{ $project->project_code ?? '-' }}</span>
                            </td>
                            <td>{{ $project->project_name ?? '-' }}</td>
                            <td>
                                @if($project->advisor)
                                    <div class="fw-semibold small">{{ $project->advisor->user_code }}</div>
                                    <div class="text-muted" style="font-size:.78rem;">
                                        {{ $project->advisor->firstname_user }} {{ $project->advisor->lastname_user }}
                                    </div>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">ยังไม่กำหนด</span>
                                @endif
                            </td>
                            <td>
                                @if($isCoordinator)
                                    <form action="{{ route('coordinator.projects.update-review', $project->project_id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <select name="status_project" class="form-select form-select-status" onchange="this.form.submit()">
                                            @foreach($statuses as $status)
                                                <option value="{{ $status }}" {{ $project->status_project === $status ? 'selected' : '' }}>
                                                    {{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                @else
                                    @php $color = $statusColors[$project->status_project] ?? 'secondary'; @endphp
                                    <span class="status-badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}-subtle">
                                        {{ $statusLabels[$project->status_project] ?? $project->status_project }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($project->examSchedule)
                                    <div class="fw-semibold small">{{ thaiDate($project->examSchedule->ex_start_time) }}</div>
                                    <div class="text-muted" style="font-size:.78rem;">
                                        {{ $project->examSchedule->ex_start_time->format('H:i') }}
                                        – {{ $project->examSchedule->ex_end_time->format('H:i') }}
                                    </div>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">ยังไม่กำหนด</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#detailModal{{ $project->project_id }}">
                                    <i class="bi bi-eye me-1"></i>รายละเอียด
                                </button>
                            </td>
                        </tr>

                        {{-- Detail Modal --}}
                        <div class="modal fade" id="detailModal{{ $project->project_id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header border-0"
                                         style="background:linear-gradient(135deg,#2E75B6,#1F4E79);border-radius:12px 12px 0 0;">
                                        <h5 class="modal-title text-white fw-bold">
                                            <i class="bi bi-clipboard-check me-2"></i>{{ $project->project_code }}
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">

                                        {{-- Project Name --}}
                                        <p class="fw-semibold mb-3" style="font-size:1rem;">{{ $project->project_name }}</p>

                                        <div class="row g-3 mb-3">
                                            <div class="col-md-6">
                                                <div class="p-3 rounded-3 bg-light">
                                                    <div class="small text-muted fw-semibold mb-1">
                                                        <i class="bi bi-person-badge me-1"></i>อาจารย์ที่ปรึกษา
                                                    </div>
                                                    @if($project->advisor)
                                                        <div class="fw-bold">{{ $project->advisor->user_code }}</div>
                                                        <div class="small text-muted">{{ $project->advisor->firstname_user }} {{ $project->advisor->lastname_user }}</div>
                                                    @else
                                                        <span class="badge bg-warning">ยังไม่กำหนด</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="p-3 rounded-3 bg-light">
                                                    <div class="small text-muted fw-semibold mb-1">
                                                        <i class="bi bi-flag me-1"></i>สถานะ
                                                    </div>
                                                    @php $color = $statusColors[$project->status_project] ?? 'secondary'; @endphp
                                                    <span class="badge bg-{{ $color }} px-3 py-2" style="font-size:.85rem;">
                                                        {{ $statusLabels[$project->status_project] ?? $project->status_project }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Committee --}}
                                        <div class="mb-3">
                                            <div class="small text-muted fw-semibold mb-2">
                                                <i class="bi bi-people me-1"></i>คณะกรรมการ
                                            </div>
                                            @php
                                                $committees = array_filter([
                                                    $project->committee1,
                                                    $project->committee2,
                                                    $project->committee3,
                                                ]);
                                            @endphp
                                            @if(count($committees))
                                                <div class="d-flex flex-column gap-2">
                                                    @foreach($committees as $i => $committee)
                                                        <div class="d-flex align-items-center gap-2 p-2 rounded-3 bg-light">
                                                            <span class="badge bg-primary">{{ $i + 1 }}</span>
                                                            <span class="fw-semibold small">{{ $committee->user_code }}</span>
                                                            <span class="text-muted small">{{ $committee->firstname_user }} {{ $committee->lastname_user }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-muted small">ยังไม่มีการกำหนดคณะกรรมการ</p>
                                            @endif
                                        </div>

                                        {{-- Members --}}
                                        <div>
                                            <div class="small text-muted fw-semibold mb-2">
                                                <i class="bi bi-person-lines-fill me-1"></i>สมาชิกกลุ่ม
                                            </div>
                                            @if($project->group && $project->group->members->count())
                                                <div class="d-flex flex-column gap-2">
                                                    @foreach($project->group->members as $member)
                                                        <div class="d-flex align-items-center gap-2 p-2 rounded-3 bg-light">
                                                            <i class="bi bi-person-circle text-muted"></i>
                                                            <span class="small">
                                                                {{ $member->student->firstname_std }} {{ $member->student->lastname_std }}
                                                                <span class="text-muted">({{ $member->username_std }})</span>
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-muted small">ไม่มีข้อมูลสมาชิก</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 pt-0">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox" style="font-size:2.5rem;opacity:.3;"></i>
                                <p class="mt-2 mb-0">ไม่พบข้อมูลโครงงาน</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="card-footer bg-white d-flex align-items-center border-top" style="border-radius:0 0 12px 12px;">
            <p class="text-muted small m-0">
                แสดง {{ $projects->firstItem() ?? 0 }}–{{ $projects->lastItem() ?? 0 }}
                จากทั้งหมด <strong>{{ $projects->total() }}</strong> รายการ
            </p>
            <div class="ms-auto">
                {{ $projects->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

</div>
@endsection

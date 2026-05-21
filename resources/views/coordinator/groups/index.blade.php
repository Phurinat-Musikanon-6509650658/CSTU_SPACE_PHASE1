@extends('layouts.app')

@section('title', 'จัดการกลุ่มโครงงาน | CSTU SPACE')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- Header -->
    <div class="mb-4">
        <a href="{{ route('coordinator.dashboard') }}" class="btn btn-link text-decoration-none ps-0">
            <i class="bi bi-chevron-left me-1"></i>กลับ Dashboard
        </a>
        <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
            <div>
                <h1 class="h2 fw-bold mb-0">
                    <i class="bi bi-people-fill me-2 text-primary"></i>จัดการกลุ่มโครงงาน
                </h1>
                <p class="text-muted mb-0">ดูและจัดการกลุ่มโครงงานทั้งหมด</p>
            </div>
            <a href="{{ route('coordinator.projects.export.csv', request()->all()) }}" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export CSV
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('coordinator.groups.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small"><i class="bi bi-flag me-1"></i>สถานะ</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">ทั้งหมด</option>
                            <option value="created"  {{ request('status') == 'created'  ? 'selected' : '' }}>สร้างแล้ว</option>
                            <option value="pending"  {{ request('status') == 'pending'  ? 'selected' : '' }}>รออนุมัติ</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>อนุมัติแล้ว</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>ปฏิเสธ</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small"><i class="bi bi-book me-1"></i>รหัสวิชา</label>
                        <select name="subject" class="form-select form-select-sm">
                            <option value="">ทั้งหมด</option>
                            <option value="CS303" {{ request('subject') == 'CS303' ? 'selected' : '' }}>CS303</option>
                            <option value="CS403" {{ request('subject') == 'CS403' ? 'selected' : '' }}>CS403</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small"><i class="bi bi-calendar-event me-1"></i>เทอม</label>
                        <select name="semester" class="form-select form-select-sm">
                            <option value="">ทั้งหมด</option>
                            <option value="1" {{ request('semester') == '1' ? 'selected' : '' }}>เทอม 1</option>
                            <option value="2" {{ request('semester') == '2' ? 'selected' : '' }}>เทอม 2</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small"><i class="bi bi-calendar me-1"></i>ปีการศึกษา</label>
                        <select name="year" class="form-select form-select-sm">
                            <option value="">ทั้งหมด</option>
                            @foreach($years as $yr)
                                <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill">
                            <i class="bi bi-search me-1"></i>ค้นหา
                        </button>
                        <a href="{{ route('coordinator.groups.index') }}" class="btn btn-outline-secondary btn-sm" title="ล้างตัวกรอง">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
            <span class="fw-semibold">
                <i class="bi bi-table me-2 text-success"></i>รายการกลุ่มทั้งหมด
            </span>
            <span class="badge bg-primary rounded-pill">{{ $groups->total() }} กลุ่ม</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:.88rem;">
                <thead>
                    <tr>
                        <th class="ps-3" style="width:5%">#</th>
                        <th style="width:8%">วิชา</th>
                        <th style="width:8%">ปี/เทอม</th>
                        <th style="width:20%">ชื่อโครงงาน</th>
                        <th style="width:14%">สมาชิก</th>
                        <th style="width:10%">สถานะกลุ่ม</th>
                        <th style="width:12%">สถานะโครงงาน</th>
                        <th style="width:13%">อาจารย์ที่ปรึกษา</th>
                        <th class="text-center" style="width:7%">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groups as $group)
                    <tr>
                        <td class="ps-3 text-center text-muted" style="font-size:.82rem;">
                            {{ ($groups->currentPage() - 1) * $groups->perPage() + $loop->iteration }}
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $group->subject_code }}</span>
                            <div class="text-muted" style="font-size:.72rem;">#{{ $group->group_id }}</div>
                        </td>
                        <td><span class="text-muted">{{ $group->year }}/{{ $group->semester }}</span></td>
                        <td>
                            @if($group->project)
                                <div class="fw-semibold text-dark">{{ $group->project->project_name ?? 'ยังไม่ระบุ' }}</div>
                                <code class="text-muted" style="font-size:.75rem;">{{ $group->project->project_code }}</code>
                            @elseif($group->latestProposal)
                                <div class="fw-semibold">{{ $group->latestProposal->proposed_title }}</div>
                                <small class="text-warning">รอดำเนินการ</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold"><i class="bi bi-people me-1 text-muted"></i>{{ $group->members->count() }}/2 คน</div>
                            <div class="text-muted" style="font-size:.78rem;">
                                @foreach($group->members as $m)
                                    {{ $m->student->firstname_std ?? 'N/A' }}{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            </div>
                        </td>
                        <td>
                            @php
                                $gsBadge = [
                                    'not_created'  => ['bg-secondary-subtle text-secondary border-secondary-subtle',  'circle',           'ยังไม่สร้าง'],
                                    'created'      => ['bg-success-subtle  text-success  border-success-subtle',      'check-circle',     'สร้างแล้ว'],
                                    'member_left'  => ['bg-warning-subtle  text-warning  border-warning-subtle',      'person-dash',      'สมาชิกออก'],
                                    'member_added' => ['bg-info-subtle     text-info     border-info-subtle',         'person-plus',      'เพิ่มสมาชิก'],
                                    'disbanded'    => ['bg-danger-subtle   text-danger   border-danger-subtle',       'x-circle',         'ยุบกลุ่ม'],
                                ][$group->status_group] ?? ['bg-secondary-subtle text-secondary border-secondary-subtle', 'circle', $group->status_group];
                            @endphp
                            <span class="badge {{ $gsBadge[0] }} border" style="font-size:.75rem;">
                                <i class="bi bi-{{ $gsBadge[1] }} me-1"></i>{{ $gsBadge[2] }}
                            </span>
                        </td>
                        <td>
                            @php
                                $ps = null;
                                if ($group->project && $group->project->status_project) {
                                    $ps = $group->project->status_project;
                                } elseif ($group->latestProposal) {
                                    $ps = $group->latestProposal->status;
                                }
                                $psBadge = [
                                    'not_proposed'     => ['bg-secondary-subtle text-secondary border-secondary-subtle', 'circle',                'ยังไม่เสนอ'],
                                    'pending'          => ['bg-warning-subtle   text-warning  border-warning-subtle',    'clock',                 'รออนุมัติ'],
                                    'approved'         => ['bg-info-subtle      text-info     border-info-subtle',       'check-circle',          'อนุมัติแล้ว'],
                                    'rejected'         => ['bg-danger-subtle    text-danger   border-danger-subtle',     'x-circle',              'ถูกปฏิเสธ'],
                                    'in_progress'      => ['bg-primary-subtle   text-primary  border-primary-subtle',   'gear-fill',             'กำลังดำเนินการ'],
                                    'submitted'        => ['bg-info-subtle      text-info     border-info-subtle',       'check-circle-fill',     'ส่งงานแล้ว'],
                                    'late_submission'  => ['bg-warning-subtle   text-warning  border-warning-subtle',   'exclamation-triangle',  'ส่งงานล่าช้า'],
                                    'passed'           => ['bg-success-subtle   text-success  border-success-subtle',   'trophy-fill',           'ผ่าน'],
                                    'failed'           => ['bg-danger-subtle    text-danger   border-danger-subtle',    'x-octagon-fill',        'ไม่ผ่าน'],
                                ][$ps] ?? null;
                            @endphp
                            @if($psBadge)
                                <span class="badge {{ $psBadge[0] }} border" style="font-size:.75rem;">
                                    <i class="bi bi-{{ $psBadge[1] }} me-1"></i>{{ $psBadge[2] }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($group->project && $group->project->advisor)
                                <div class="fw-semibold" style="font-size:.82rem;">{{ $group->project->advisor->firstname_user }} {{ $group->project->advisor->lastname_user }}</div>
                                <code class="text-muted" style="font-size:.72rem;">{{ $group->project->advisor_code }}</code>
                            @elseif($group->latestProposal && $group->latestProposal->lecturer)
                                <div class="fw-semibold" style="font-size:.82rem;">{{ $group->latestProposal->lecturer->firstname_user }} {{ $group->latestProposal->lecturer->lastname_user }}</div>
                                <small class="text-muted">(ข้อเสนอ)</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('coordinator.groups.show', $group->group_id) }}"
                               class="btn btn-sm btn-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted d-block mb-2" style="opacity:.3;"></i>
                            <span class="text-muted">ไม่พบข้อมูลกลุ่มโครงงาน</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white border-top">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <small class="text-muted">
                    แสดง {{ $groups->firstItem() ?? 0 }}–{{ $groups->lastItem() ?? 0 }} จาก {{ $groups->total() }} กลุ่ม
                </small>
                <div>
                    {{ $groups->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    .table thead th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        font-weight: 600;
        color: #2c3e50;
        border: none;
        padding: .85rem .75rem;
        white-space: nowrap;
        font-size: .82rem;
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    .table-hover tbody tr:hover { background: rgba(102,126,234,.04); }
</style>
@endpush

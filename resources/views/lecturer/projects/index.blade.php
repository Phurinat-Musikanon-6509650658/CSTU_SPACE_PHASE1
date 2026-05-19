@extends('layouts.app')

@section('title', 'โครงงานของฉัน')

@push('styles')
<style>
    :root { --theme: #667eea; --theme-dark: #764ba2; --theme-rgb: 102,126,234; }
    .page-banner {
        background: linear-gradient(135deg, var(--theme) 0%, var(--theme-dark) 100%);
        border-radius: 12px;
        padding: 1.1rem 1.75rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 6px 20px rgba(var(--theme-rgb),.35);
    }
    .filter-card {
        background: #fff;
        border-radius: 10px;
        padding: .9rem 1.25rem;
        margin-bottom: 1rem;
        box-shadow: 0 1px 8px rgba(0,0,0,.06);
        border: 1px solid #e6e3f5;
        border-left: 4px solid var(--theme);
    }
    .filter-card .form-select-sm {
        border-color: #d9d4f0;
        font-size: .83rem;
    }
    .filter-card .form-select-sm:focus {
        border-color: var(--theme);
        box-shadow: 0 0 0 .2rem rgba(var(--theme-rgb),.2);
    }
    .table-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,.07);
        border: 1px solid #e6e3f5;
    }
    .table-compact thead th {
        background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #5b21b6;
        padding: .7rem .75rem;
        border-bottom: 2px solid #c4b5fd;
        white-space: nowrap;
    }
    .table-compact tbody td {
        padding: .65rem .75rem;
        vertical-align: middle;
        font-size: .875rem;
        border-bottom: 1px solid #f5f3ff;
    }
    .table-compact tbody tr:last-child td { border-bottom: none; }
    .table-compact tbody tr:hover { background: linear-gradient(90deg,#f5f3ff,#fdfcff); }
    .stat-pill {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .3rem .9rem;
        border-radius: 50px;
        font-size: .8rem;
        font-weight: 600;
        border: 1.5px solid;
    }
    .stat-pill-total   { background:#f8f9fa; color:#6c757d; border-color:#dee2e6; }
    .stat-pill-ok      { background:#f0fdf4; color:#059669; border-color:#48bb78; }
    .stat-pill-prog    { background:#eff6ff; color:#2563eb; border-color:#4299e1; }
    .stat-pill-submit  { background:#fff8e1; color:#d97706; border-color:#f6ad55; }
    .project-code {
        font-family: 'Courier New', monospace;
        background: linear-gradient(135deg,#f5f3ff,#ede9fe);
        color: #5b21b6;
        padding: .2rem .55rem;
        border-radius: 5px;
        font-size: .8rem;
        font-weight: 600;
        white-space: nowrap;
        border: 1px solid #c4b5fd;
    }
    .empty-row td {
        padding: 3rem !important;
        text-align: center;
        color: #bbb;
    }
    .btn-icon {
        padding: .28rem .55rem;
        font-size: .78rem;
        border-radius: 6px;
        line-height: 1;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-3">

    {{-- Banner --}}
    <div class="page-banner d-flex align-items-center justify-content-between">
        <div>
            <h5 class="fw-bold mb-0 text-white"><i class="bi bi-folder-fill me-2"></i>โครงงานของฉัน</h5>
            <small style="color:rgba(255,255,255,.85);">รายการโครงงานที่คุณเป็นอาจารย์ที่ปรึกษา</small>
        </div>
        <a href="{{ route('lecturer.dashboard') }}" class="btn btn-sm"
           style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.5);">
            <i class="bi bi-arrow-left me-1"></i>กลับ
        </a>
    </div>

    {{-- Stats pills --}}
    @php
        $total    = $projects->count();
        $approved = $projects->where('status_project','approved')->count();
        $inprog   = $projects->where('status_project','in_progress')->count();
        $submitted= $projects->whereIn('status_project',['submitted','late_submission'])->count();
    @endphp
    <div class="d-flex flex-wrap gap-2 mb-2">
        <span class="stat-pill stat-pill-total"><i class="bi bi-list-ul"></i>ทั้งหมด {{ $total }}</span>
        <span class="stat-pill stat-pill-ok"><i class="bi bi-check-circle-fill"></i>อนุมัติแล้ว {{ $approved }}</span>
        <span class="stat-pill stat-pill-prog"><i class="bi bi-gear-fill"></i>ดำเนินการ {{ $inprog }}</span>
        <span class="stat-pill stat-pill-submit"><i class="bi bi-file-earmark-check-fill"></i>ส่งเล่มแล้ว {{ $submitted }}</span>
    </div>

    {{-- Filter --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('lecturer.projects.index') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label mb-1 small fw-semibold text-muted">ปีการศึกษา</label>
                <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">— ทั้งหมด —</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1 small fw-semibold text-muted">ภาคเรียน</label>
                <select name="semester" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">— ทั้งหมด —</option>
                    @foreach($semesters as $sem)
                        <option value="{{ $sem }}" {{ request('semester') == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1 small fw-semibold text-muted">รายวิชา</label>
                <select name="subject" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">— ทั้งหมด —</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s }}" {{ request('subject') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-5 d-flex gap-2 align-items-end">
                @if(request()->hasAny(['year','semester','subject']))
                    <a href="{{ route('lecturer.projects.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>ล้างตัวกรอง
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-compact mb-0">
                <thead>
                    <tr>
                        <th style="width:3%">#</th>
                        <th style="width:10%">รหัสโครงงาน</th>
                        <th style="width:28%">ชื่อโครงงาน</th>
                        <th style="width:7%">วิชา</th>
                        <th style="width:7%">ปี/เทอม</th>
                        <th style="width:18%">สมาชิก</th>
                        <th style="width:12%">วันสอบ</th>
                        <th style="width:8%">สถานะ</th>
                        <th style="width:7%" class="text-center">การดำเนิน</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $i => $project)
                        @php
                            $statusMap = [
                                'approved'        => ['bg-success text-white',  'อนุมัติแล้ว'],
                                'in_progress'     => ['bg-primary text-white',  'ดำเนินการ'],
                                'submitted'       => ['bg-warning text-dark',   'ส่งเล่มแล้ว'],
                                'late_submission' => ['bg-danger text-white',   'ส่งล่าช้า'],
                                'passed'          => ['bg-success text-white',  'ผ่าน'],
                                'failed'          => ['bg-danger text-white',   'ไม่ผ่าน'],
                            ];
                            [$bc, $bl] = $statusMap[$project->status_project] ?? ['bg-secondary text-white', $project->status_project];
                        @endphp
                        <tr>
                            <td class="text-muted small">{{ $i + 1 }}</td>
                            <td><span class="project-code">{{ $project->project_code }}</span></td>
                            <td>
                                <div class="fw-semibold lh-sm">{{ $project->project_name ?? 'ยังไม่ระบุ' }}</div>
                            </td>
                            <td><code class="small">{{ $project->group->subject_code ?? '-' }}</code></td>
                            <td class="small text-muted">{{ $project->group->year ?? '-' }}/{{ $project->group->semester ?? '-' }}</td>
                            <td>
                                @foreach($project->group->members as $member)
                                    <div class="small lh-sm">
                                        <i class="bi bi-person-fill text-muted me-1" style="font-size:.7rem;"></i>{{ $member->student->firstname_std ?? '' }} {{ $member->student->lastname_std ?? '' }}
                                    </div>
                                @endforeach
                            </td>
                            <td class="small" style="white-space:nowrap;">
                                @if($project->exam_datetime)
                                    <i class="bi bi-calendar-event text-primary me-1"></i>{{ thaiDateTime($project->exam_datetime) }}
                                @else
                                    <span class="text-muted">ยังไม่กำหนด</span>
                                @endif
                            </td>
                            <td><span class="badge {{ $bc }} small">{{ $bl }}</span></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    @if($project->group->latestProposal)
                                        <a href="{{ route('lecturer.proposals.show', $project->group->latestProposal->proposal_id) }}"
                                           class="btn btn-icon btn-outline-primary" title="ดูข้อเสนอ">
                                            <i class="bi bi-file-text"></i>
                                        </a>
                                    @endif
                                    @if($project->exam_datetime)
                                        <a href="{{ route('lecturer.evaluations.form', $project->project_id) }}"
                                           class="btn btn-icon btn-warning" title="ประเมินโครงงาน">
                                            <i class="bi bi-clipboard-check"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="9">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                ไม่มีโครงงานที่ตรงกับเงื่อนไข
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

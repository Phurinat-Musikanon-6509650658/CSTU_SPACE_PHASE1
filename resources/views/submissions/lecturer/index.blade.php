@extends('layouts.app')

@section('title', 'รายงานโครงงาน')

@push('styles')
<style>
    :root { --theme: #38b2ac; --theme-dark: #2c7a7b; --theme-rgb: 56,178,172; }
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
        border: 1px solid #ccf2f0;
        border-left: 4px solid var(--theme);
    }
    .filter-card .form-select-sm {
        border-color: #b2e4e2;
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
        border: 1px solid #ccf2f0;
    }
    .table-compact thead th {
        background: linear-gradient(135deg, #f0fdfc 0%, #ccfbf8 100%);
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #115e59;
        padding: .7rem .75rem;
        border-bottom: 2px solid #5eead4;
        white-space: nowrap;
    }
    .table-compact tbody td {
        padding: .65rem .75rem;
        vertical-align: middle;
        font-size: .875rem;
        border-bottom: 1px solid #f0fdfc;
    }
    .table-compact tbody tr:last-child td { border-bottom: none; }
    .table-compact tbody tr:hover { background: linear-gradient(90deg,#f0fdfc,#fdfffe); }
    .stat-pill {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .35rem 1rem;
        border-radius: 50px;
        font-size: .82rem;
        font-weight: 600;
        background: #f0fdfc;
        color: #0f766e;
        border: 1.5px solid #38b2ac;
    }
    .project-code {
        font-family: 'Courier New', monospace;
        background: linear-gradient(135deg,#f0fdfc,#ccfbf8);
        color: #115e59;
        padding: .2rem .55rem;
        border-radius: 5px;
        font-size: .8rem;
        font-weight: 600;
        white-space: nowrap;
        border: 1px solid #5eead4;
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
            <h5 class="fw-bold mb-0 text-white"><i class="bi bi-file-earmark-pdf-fill me-2"></i>รายงานโครงงาน</h5>
            <small style="color:rgba(255,255,255,.85);">เล่มโครงงาน (PDF) ที่นักศึกษาส่งมา</small>
        </div>
        <a href="{{ route('lecturer.dashboard') }}" class="btn btn-sm"
           style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.5);">
            <i class="bi bi-arrow-left me-1"></i>กลับ
        </a>
    </div>

    {{-- Stats --}}
    <div class="d-flex flex-wrap gap-2 mb-2">
        <span class="stat-pill" style="color:#2c7a7b;border-color:#38b2ac;">
            <i class="bi bi-file-earmark-pdf"></i> ทั้งหมด {{ $totalSubmissions }} เล่ม
        </span>
    </div>

    {{-- Filter --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('lecturer.submissions.index') }}" class="row g-2 align-items-end">
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
                    <a href="{{ route('lecturer.submissions.index') }}" class="btn btn-sm btn-outline-secondary">
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
                        <th style="width:35%">ชื่อโครงงาน</th>
                        <th style="width:7%">วิชา</th>
                        <th style="width:7%">ปี/เทอม</th>
                        <th style="width:18%">สมาชิก</th>
                        <th style="width:12%">วันที่ส่ง</th>
                        <th style="width:8%" class="text-center">การดำเนิน</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $i => $project)
                        <tr>
                            <td class="text-muted small">{{ $i + 1 }}</td>
                            <td><span class="project-code">{{ $project->project_code }}</span></td>
                            <td class="fw-semibold">{{ $project->project_name ?? 'ยังไม่ระบุ' }}</td>
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
                                @if($project->submitted_at)
                                    <i class="bi bi-clock-history text-teal me-1" style="color:#38b2ac;"></i>{{ thaiDateTime($project->submitted_at) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('lecturer.submissions.show', $project->project_id) }}"
                                       class="btn btn-icon btn-outline-primary" title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('lecturer.submissions.download', $project->project_id) }}"
                                       class="btn btn-icon btn-outline-success" title="ดาวน์โหลด PDF">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="8">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                ยังไม่มีเล่มโครงงานที่ตรงกับเงื่อนไข
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

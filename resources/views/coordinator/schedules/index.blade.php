@extends('layouts.app')

@section('title', 'จัดตารางสอบและคณะกรรมการ | CSTU SPACE')

@push('styles')
<style>
    .stat-pill {
        border-radius: 14px; padding: .9rem 1.2rem;
        display: flex; align-items: center; gap: .75rem;
    }
    .stat-pill .stat-num { font-size: 1.6rem; font-weight: 700; line-height: 1; }
    .stat-pill .stat-lbl { font-size: .78rem; opacity: .85; }

    .project-row {
        background: #fff; border-radius: 10px;
        box-shadow: 0 1px 4px rgba(0,0,0,.07);
        margin-bottom: .5rem;
        border-left: 4px solid transparent;
        transition: box-shadow .15s, transform .15s;
    }
    .project-row:hover { box-shadow: 0 3px 12px rgba(0,0,0,.11); transform: translateY(-1px); }
    .project-row.complete { border-left-color: #198754; }
    .project-row.partial  { border-left-color: #ffc107; }
    .project-row.empty    { border-left-color: #dc3545; }

    .badge-code { font-size: .75rem; font-family: monospace; background: #e8eeff; color: #3730a3; border-radius: 6px; padding: .2rem .5rem; }
    .badge-person { font-size: .72rem; padding: .2rem .5rem; border-radius: 20px; font-weight: 500; }

    .filter-card { background: #fff; border-radius: 12px; box-shadow: 0 1px 6px rgba(0,0,0,.07); }

    .unscheduled-banner {
        background: linear-gradient(135deg,#fff5f5,#ffe4e4);
        border: 1.5px solid #fca5a5; border-radius: 12px;
        padding: .75rem 1.25rem;
        display: flex; align-items: center; gap: .6rem;
        color: #b91c1c; font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Header --}}
    <div class="mb-4">
        <a href="{{ route('coordinator.dashboard') }}" class="btn btn-link text-decoration-none ps-0">
            <i class="bi bi-chevron-left me-1"></i>กลับ Dashboard
        </a>
        <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
            <div>
                <h1 class="h2 fw-bold mb-0">
                    <i class="bi bi-calendar-check me-2 text-primary"></i>จัดตารางสอบและคณะกรรมการ
                </h1>
                <p class="text-muted mb-0">กำหนดวันเวลาสอบและมอบหมายคณะกรรมการสอบโครงงาน</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('coordinator.schedules.import.form') }}" class="btn btn-primary">
                    <i class="bi bi-file-earmark-arrow-up me-1"></i>Import CSV
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Unscheduled banner --}}
    @if($withoutExam > 0 && !request()->hasAny(['search','has_exam','semester','year','course_code']))
    <div class="unscheduled-banner mb-4">
        <i class="bi bi-exclamation-circle-fill fs-5"></i>
        <span>ยังมี <strong>{{ $withoutExam }} โครงงาน</strong> ที่ยังไม่ได้กำหนดวันสอบ</span>
        <a href="{{ route('coordinator.schedules.index', ['has_exam' => '0']) }}" class="ms-auto btn btn-sm btn-danger">
            ดูรายการ <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    @endif

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-pill bg-primary text-white">
                <i class="bi bi-folder2 fs-4 opacity-75"></i>
                <div>
                    <div class="stat-num">{{ $totalCount }}</div>
                    <div class="stat-lbl">โครงงานทั้งหมด</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-pill bg-success text-white">
                <i class="bi bi-calendar-check fs-4 opacity-75"></i>
                <div>
                    <div class="stat-num">{{ $withExam }}</div>
                    <div class="stat-lbl">กำหนดวันสอบแล้ว</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-pill bg-danger text-white">
                <i class="bi bi-calendar-x fs-4 opacity-75"></i>
                <div>
                    <div class="stat-num">{{ $withoutExam }}</div>
                    <div class="stat-lbl">ยังไม่กำหนดวันสอบ</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-pill bg-info text-white">
                <i class="bi bi-file-earmark-text fs-4 opacity-75"></i>
                <div>
                    <div class="stat-num">{{ $projects->lastPage() }}</div>
                    <div class="stat-lbl">หน้าทั้งหมด ({{ $projects->perPage() }}/หน้า)</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="filter-card mb-4 p-3">
        <form method="GET" action="{{ route('coordinator.schedules.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-sm-6 col-md-3">
                    <label class="form-label fw-semibold small mb-1">ค้นหา</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="รหัสโครงงาน, ชื่อโครงงาน..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-sm-6 col-md-2">
                    <label class="form-label fw-semibold small mb-1">วิชา</label>
                    <select name="course_code" class="form-select form-select-sm">
                        <option value="">ทั้งหมด</option>
                        <option value="CS303" {{ request('course_code') === 'CS303' ? 'selected' : '' }}>CS303</option>
                        <option value="CS403" {{ request('course_code') === 'CS403' ? 'selected' : '' }}>CS403</option>
                    </select>
                </div>
                <div class="col-sm-4 col-md-2">
                    <label class="form-label fw-semibold small mb-1">ภาคเรียน</label>
                    <select name="semester" class="form-select form-select-sm">
                        <option value="">ทั้งหมด</option>
                        <option value="1" {{ request('semester') == '1' ? 'selected' : '' }}>เทอม 1</option>
                        <option value="2" {{ request('semester') == '2' ? 'selected' : '' }}>เทอม 2</option>
                    </select>
                </div>
                <div class="col-sm-4 col-md-2">
                    <label class="form-label fw-semibold small mb-1">ปีการศึกษา</label>
                    <select name="year" class="form-select form-select-sm">
                        <option value="">ทั้งหมด</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4 col-md-2">
                    <label class="form-label fw-semibold small mb-1">วันสอบ</label>
                    <select name="has_exam" class="form-select form-select-sm">
                        <option value="">ทั้งหมด</option>
                        <option value="1" {{ request('has_exam') === '1' ? 'selected' : '' }}>กำหนดแล้ว</option>
                        <option value="0" {{ request('has_exam') === '0' ? 'selected' : '' }}>ยังไม่กำหนด</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill" title="กรอง">
                        <i class="bi bi-funnel"></i>
                    </button>
                    <a href="{{ route('coordinator.schedules.index') }}" class="btn btn-outline-secondary btn-sm flex-fill" title="ล้าง">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Active filter badge --}}
    @if(request()->hasAny(['search','has_exam','semester','year','course_code']))
    <div class="mb-3 d-flex align-items-center gap-2 flex-wrap">
        <small class="text-muted">กำลังกรอง:</small>
        @if(request('search'))
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                ค้นหา: "{{ request('search') }}"
            </span>
        @endif
        @if(request('course_code'))
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ request('course_code') }}</span>
        @endif
        @if(request('semester'))
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">เทอม {{ request('semester') }}</span>
        @endif
        @if(request('year'))
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">ปี {{ request('year') }}</span>
        @endif
        @if(request('has_exam') !== null && request('has_exam') !== '')
            <span class="badge {{ request('has_exam') === '1' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }}">
                {{ request('has_exam') === '1' ? 'กำหนดวันสอบแล้ว' : 'ยังไม่กำหนด' }}
            </span>
        @endif
        <span class="text-muted small">— พบ {{ $totalCount }} โครงงาน</span>
    </div>
    @endif

    {{-- Projects list --}}
    @forelse($projects as $project)
        @php
            $hasExam      = (bool) $project->exam_datetime;
            $hasAdvisor   = (bool) $project->advisor_code;
            $hasCommittee = $project->committee1_code && $project->committee2_code;
            $isComplete   = $hasExam && $hasAdvisor && $hasCommittee;
            $cardStatus   = $isComplete ? 'complete' : ($hasExam || $hasAdvisor ? 'partial' : 'empty');
            $isToday      = $hasExam && $project->exam_datetime->isToday();
            $isPast       = $hasExam && $project->exam_datetime->isPast() && !$isToday;
            $isUpcoming   = $hasExam && $project->exam_datetime->isFuture();
        @endphp
        <div class="project-row {{ $cardStatus }}">
            <div class="px-3 py-2">
                <div class="row align-items-center g-2">

                    {{-- Project info --}}
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <span class="badge-code">{{ $project->project_code }}</span>
                            @if($isComplete)
                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:.68rem;">
                                    <i class="bi bi-check-circle-fill"></i> ครบ
                                </span>
                            @endif
                        </div>
                        <div class="fw-semibold text-dark small mb-1" style="line-height:1.3">
                            {{ $project->project_name ?? 'ยังไม่ระบุชื่อ' }}
                        </div>
                        <div class="text-muted" style="font-size:.75rem;">
                            @foreach($project->group->members as $member)
                                {{ $member->student->firstname_std ?? '' }} {{ $member->student->lastname_std ?? '' }}@if(!$loop->last), @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Exam datetime --}}
                    <div class="col-md-2">
                        <div class="text-muted mb-1" style="font-size:.72rem;"><i class="bi bi-clock me-1"></i>วันเวลาสอบ</div>
                        @if($project->exam_datetime)
                            <div class="fw-semibold text-success small">
                                {{ $project->exam_datetime->locale('th')->translatedFormat('j M') }} {{ $project->exam_datetime->year + 543 }}
                            </div>
                            <div class="text-muted" style="font-size:.75rem;">{{ $project->exam_datetime->format('H:i น.') }}</div>
                            <div class="mt-1">
                                @if($isToday)
                                    <span class="badge" style="background:#ffc107;color:#212529;font-size:.68rem;"><i class="bi bi-star-fill me-1"></i>วันนี้</span>
                                @elseif($isPast)
                                    <span class="badge bg-secondary" style="font-size:.68rem;">สอบแล้ว</span>
                                @else
                                    <span class="badge bg-success" style="font-size:.68rem;">กำลังจะถึง</span>
                                @endif
                            </div>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size:.72rem;">
                                <i class="bi bi-x-circle me-1"></i>ยังไม่กำหนด
                            </span>
                        @endif
                    </div>

                    {{-- Committee --}}
                    <div class="col-md-4">
                        <div class="text-muted mb-1" style="font-size:.72rem;"><i class="bi bi-people me-1"></i>คณะกรรมการ</div>
                        <div class="d-flex flex-wrap gap-1">
                            @if($project->advisor_code)
                                <span class="badge-person bg-primary-subtle text-primary border border-primary-subtle">
                                    <i class="bi bi-person-badge me-1"></i>{{ $project->advisor_code }}
                                </span>
                            @else
                                <span class="badge-person bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="bi bi-x-circle me-1"></i>ไม่มีที่ปรึกษา
                                </span>
                            @endif
                            @foreach([$project->committee1_code, $project->committee2_code, $project->committee3_code] as $comm)
                                @if($comm)
                                    <span class="badge-person bg-success-subtle text-success border border-success-subtle">{{ $comm }}</span>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Action --}}
                    <div class="col-md-2 text-end">
                        <a href="{{ route('coordinator.schedules.edit', $project->project_id) }}"
                           class="btn btn-sm {{ $isComplete ? 'btn-outline-success' : 'btn-primary' }}">
                            <i class="bi bi-pencil me-1"></i>{{ $isComplete ? 'แก้ไข' : 'กำหนด' }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-5">
            <i class="bi bi-folder-x display-1 text-muted"></i>
            <h5 class="mt-3 text-muted">ไม่พบข้อมูลโครงงาน</h5>
        </div>
    @endforelse

    {{-- Pagination --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mt-4 gap-2">
        <small class="text-muted">
            แสดง {{ $projects->firstItem() ?? 0 }}–{{ $projects->lastItem() ?? 0 }} จาก {{ $projects->total() }} โครงงาน
        </small>
        <div>{{ $projects->links('pagination::bootstrap-4') }}</div>
    </div>

</div>
@endsection

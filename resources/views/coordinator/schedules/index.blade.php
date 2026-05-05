@extends('layouts.app')

@section('title', 'จัดตารางสอบและคณะกรรมการ | CSTU SPACE')

@push('styles')
<style>
    .page-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,.08);
    }
    .project-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,.07);
        margin-bottom: .75rem;
        transition: box-shadow .2s, transform .2s;
        border-left: 4px solid transparent;
    }
    .project-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,.12);
        transform: translateY(-1px);
    }
    .project-card.complete { border-left-color: #198754; }
    .project-card.partial  { border-left-color: #ffc107; }
    .project-card.empty    { border-left-color: #dc3545; }
    .status-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }
    .stat-card { border-radius: 12px; border: none; }
</style>
@endpush

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
                    <i class="bi bi-calendar-check me-2 text-primary"></i>จัดตารางสอบและคณะกรรมการ
                </h1>
                <p class="text-muted mb-0">กำหนดวันเวลาสอบและมอบหมายคณะกรรมการสอบโครงงาน</p>
            </div>
            <a href="{{ route('coordinator.exam-schedules.index') }}" class="btn btn-outline-success">
                <i class="bi bi-calendar-week me-1"></i>ดูตารางสอบ
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stats -->
    @php
        $allItems = $projects->getCollection();
        $withExam = $allItems->filter(fn($p) => $p->exam_datetime)->count();
        $withCommittee = $allItems->filter(fn($p) => $p->advisor_code && $p->committee1_code)->count();
        $withoutExam = $projects->total() - $withExam;
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body py-3">
                    <div class="small opacity-75">โครงงานทั้งหมด</div>
                    <div class="h3 fw-bold mb-0">{{ $projects->total() }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card bg-success text-white">
                <div class="card-body py-3">
                    <div class="small opacity-75">กำหนดเวลาแล้ว</div>
                    <div class="h3 fw-bold mb-0">{{ $withExam }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card bg-info text-white">
                <div class="card-body py-3">
                    <div class="small opacity-75">มีคณะกรรมการ</div>
                    <div class="h3 fw-bold mb-0">{{ $withCommittee }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body py-3">
                    <div class="small opacity-75">ยังไม่กำหนด</div>
                    <div class="h3 fw-bold mb-0">{{ $withoutExam }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card page-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('coordinator.schedules.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small">ภาคเรียน</label>
                        <select name="semester" class="form-select form-select-sm">
                            <option value="">ทั้งหมด</option>
                            <option value="1" {{ request('semester') == '1' ? 'selected' : '' }}>1</option>
                            <option value="2" {{ request('semester') == '2' ? 'selected' : '' }}>2</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small">ปีการศึกษา</label>
                        <select name="year" class="form-select form-select-sm">
                            <option value="">ทั้งหมด</option>
                            @for($y = 2568; $y >= 2560; $y--)
                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small">สถานะสอบ</label>
                        <select name="has_exam" class="form-select form-select-sm">
                            <option value="">ทั้งหมด</option>
                            <option value="1" {{ request('has_exam') == '1' ? 'selected' : '' }}>กำหนดแล้ว</option>
                            <option value="0" {{ request('has_exam') == '0' ? 'selected' : '' }}>ยังไม่กำหนด</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">ค้นหา</label>
                        <input type="text" class="form-control form-control-sm" id="searchInput"
                               placeholder="รหัสโครงงาน, ชื่อโครงงาน...">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary btn-sm w-100" title="กรองข้อมูล">
                            <i class="bi bi-funnel"></i>
                        </button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('coordinator.schedules.index') }}" class="btn btn-outline-secondary btn-sm w-100" title="ล้างตัวกรอง">
                            <i class="bi bi-x"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Projects List -->
    <div id="projectList">
        @forelse($projects as $project)
            @php
                $hasExam      = (bool) $project->exam_datetime;
                $hasAdvisor   = (bool) $project->advisor_code;
                $hasCommittee = $project->committee1_code && $project->committee2_code;
                $isComplete   = $hasExam && $hasAdvisor && $hasCommittee;
                $cardStatus   = $isComplete ? 'complete' : ($hasExam || $hasAdvisor ? 'partial' : 'empty');
            @endphp
            <div class="project-card card {{ $cardStatus }}"
                 data-search="{{ strtolower($project->project_code . ' ' . ($project->project_name ?? '')) }}">
                <div class="card-body py-3">
                    <div class="row align-items-center g-2">

                        <!-- Project Info -->
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <span class="badge bg-primary">กลุ่ม {{ sprintf('%02d', $project->group_id) }}</span>
                                <code class="text-primary fw-bold small">{{ $project->project_code }}</code>
                                @if($isComplete)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:.7rem;">
                                        <i class="bi bi-check-circle-fill me-1"></i>ครบแล้ว
                                    </span>
                                @endif
                            </div>
                            <div class="fw-semibold text-dark mb-1">{{ $project->project_name ?? 'ยังไม่ระบุชื่อโครงงาน' }}</div>
                            <div class="text-muted small">
                                <i class="bi bi-people me-1"></i>
                                @foreach($project->group->members as $member)
                                    {{ $member->student->firstname_std ?? '' }} {{ $member->student->lastname_std ?? '' }}@if(!$loop->last), @endif
                                @endforeach
                            </div>
                        </div>

                        <!-- Exam DateTime -->
                        <div class="col-md-3">
                            <div class="small text-muted mb-1"><i class="bi bi-clock me-1"></i>วันเวลาสอบ</div>
                            @if($project->exam_datetime)
                                <div class="d-flex align-items-center gap-1">
                                    <span class="status-dot bg-success"></span>
                                    <span class="fw-semibold text-success small">{{ $project->exam_datetime->format('d/m/Y') }}</span>
                                </div>
                                <div class="text-muted small">{{ $project->exam_datetime->format('H:i น.') }}</div>
                            @else
                                <div class="d-flex align-items-center gap-1">
                                    <span class="status-dot bg-danger"></span>
                                    <span class="text-danger small fw-semibold">ยังไม่กำหนด</span>
                                </div>
                            @endif
                        </div>

                        <!-- Committee -->
                        <div class="col-md-3">
                            <div class="small text-muted mb-1"><i class="bi bi-people me-1"></i>คณะกรรมการ</div>
                            <div class="d-flex flex-wrap gap-1">
                                @if($project->advisor_code)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" title="ที่ปรึกษา">
                                        <i class="bi bi-person-badge me-1"></i>{{ $project->advisor_code }}
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size:.7rem;">
                                        <i class="bi bi-x-circle me-1"></i>ไม่มีที่ปรึกษา
                                    </span>
                                @endif
                                @if($project->committee1_code)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle" title="กรรมการ 1">{{ $project->committee1_code }}</span>
                                @endif
                                @if($project->committee2_code)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle" title="กรรมการ 2">{{ $project->committee2_code }}</span>
                                @endif
                                @if($project->committee3_code)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle" title="กรรมการ 3">{{ $project->committee3_code }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
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
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $projects->links() }}
    </div>
</div>

<script>
document.getElementById('searchInput').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.project-card').forEach(card => {
        card.style.display = card.dataset.search.includes(q) ? '' : 'none';
    });
});
</script>
@endsection

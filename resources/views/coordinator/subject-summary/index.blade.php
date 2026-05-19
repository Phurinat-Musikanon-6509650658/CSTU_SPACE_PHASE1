@extends('layouts.app')

@section('title', 'สรุปรายวิชา | CSTU SPACE')

@push('styles')
<style>
    .page-card {
        border: none;
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(0,0,0,.08);
    }
    .filter-bar {
        background: white;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        box-shadow: 0 1px 6px rgba(0,0,0,.06);
        margin-bottom: 1.25rem;
    }
    .export-btn {
        font-size: .82rem;
        padding: .4rem .9rem;
        border-radius: 8px;
        font-weight: 500;
    }
    .subject-tab .nav-link {
        border-radius: 10px 10px 0 0;
        font-weight: 600;
        color: #555;
        padding: .6rem 1.4rem;
    }
    .subject-tab .nav-link.active {
        background: var(--gradient-primary);
        color: white;
    }
    .proj-table thead th {
        background: linear-gradient(135deg, #2E75B6 0%, #1F4E79 100%);
        color: white;
        font-size: .82rem;
        padding: .6rem .55rem;
        white-space: nowrap;
        font-weight: 600;
        border: none;
        vertical-align: middle;
    }
    .proj-table tbody tr:hover { background: rgba(102,126,234,.05); }
    .proj-table td {
        font-size: .82rem;
        padding: .5rem .55rem;
        vertical-align: middle;
        border-color: #f0f0f0;
    }
    .badge-type {
        font-size: .75rem;
        padding: .25rem .55rem;
        border-radius: 20px;
    }
    .badge-s { background: #e3f2fd; color: #0d47a1; }
    .badge-r { background: #e8f5e9; color: #1b5e20; }
    .badge-m { background: #fff3e0; color: #e65100; }
    .status-chip {
        display: inline-block;
        font-size: .72rem;
        padding: .2rem .55rem;
        border-radius: 20px;
        font-weight: 500;
    }
    .status-in_progress  { background:#fff3e0; color:#e65100; }
    .status-submitted    { background:#e8f5e9; color:#2e7d32; }
    .status-approved     { background:#e3f2fd; color:#0d47a1; }
    .status-not_proposed { background:#f5f5f5; color:#757575; }
    .status-pending      { background:#fce4ec; color:#880e4f; }
    .stat-num { font-size: 1.4rem; font-weight: 700; }
    .empty-state { padding: 3rem; text-align: center; color: #aaa; }
    .member-block { line-height: 1.3; }
    .member-block small { color: #888; font-size: .76rem; }
    .adv-code { display:inline-block; background:#f0f4ff; color:#2c3e50; padding:.15rem .4rem; border-radius:5px; font-size:.78rem; font-family:monospace; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- ── Page header ─────────────────────────────────────── --}}
    <div class="mb-3">
        <a href="{{ route('coordinator.dashboard') }}" class="btn btn-link text-decoration-none ps-0 text-secondary">
            <i class="bi bi-chevron-left me-1"></i>กลับ Dashboard
        </a>
        <div class="mt-1">
            <h1 class="h3 fw-bold mb-0">
                <i class="bi bi-table me-2 text-primary"></i>สรุปข้อมูลรายวิชา
            </h1>
            <p class="text-muted small mb-0">ภาพรวมโครงงานแยกตามรหัสวิชา พร้อม export / import</p>
        </div>
    </div>

    {{-- ── Alerts ──────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── Filter bar ──────────────────────────────────────── --}}
    <div class="filter-bar">
        {{-- Row 1: ปีการศึกษา / เทอม (server-side reload) + Export --}}
        <div class="d-flex align-items-center gap-3 flex-wrap mb-2">
            <form method="GET" action="{{ route('coordinator.subject-summary.index') }}"
                  class="d-flex align-items-center gap-2 flex-wrap">
                <label class="fw-semibold text-secondary small mb-0">ปีการศึกษา</label>
                <select name="year" class="form-select form-select-sm" style="width:110px;" onchange="this.form.submit()">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <label class="fw-semibold text-secondary small mb-0">เทอม</label>
                <select name="semester" class="form-select form-select-sm" style="width:80px;" onchange="this.form.submit()">
                    @foreach($semesters as $s)
                        <option value="{{ $s }}" {{ $s == $semester ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </form>

            <div class="ms-auto d-flex gap-2 flex-wrap">
                @if(\App\Helpers\PermissionHelper::isCoordinator() || \App\Helpers\PermissionHelper::isAdmin())
                <a href="{{ route('coordinator.subject-summary.import.form') }}"
                   class="btn btn-outline-primary export-btn">
                    <i class="bi bi-file-earmark-arrow-up me-1"></i>Import
                </a>
                @endif
                <a href="{{ route('coordinator.subject-summary.template') }}"
                   class="btn btn-outline-secondary export-btn">
                    <i class="bi bi-download me-1"></i>Template
                </a>
                <div class="dropdown">
                    <button class="btn btn-success export-btn dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-file-earmark-excel me-1"></i>Export XLSX
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('coordinator.subject-summary.export', ['format'=>'xlsx','year'=>$year,'semester'=>$semester,'subject'=>'CS303']) }}">CS303</a></li>
                        <li><a class="dropdown-item" href="{{ route('coordinator.subject-summary.export', ['format'=>'xlsx','year'=>$year,'semester'=>$semester,'subject'=>'CS403']) }}">CS403</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('coordinator.subject-summary.export', ['format'=>'xlsx','year'=>$year,'semester'=>$semester,'subject'=>'all']) }}">ทุกวิชา</a></li>
                    </ul>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary export-btn dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-filetype-csv me-1"></i>Export CSV
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('coordinator.subject-summary.export', ['format'=>'csv','year'=>$year,'semester'=>$semester,'subject'=>'CS303']) }}">CS303</a></li>
                        <li><a class="dropdown-item" href="{{ route('coordinator.subject-summary.export', ['format'=>'csv','year'=>$year,'semester'=>$semester,'subject'=>'CS403']) }}">CS403</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('coordinator.subject-summary.export', ['format'=>'csv','year'=>$year,'semester'=>$semester,'subject'=>'all']) }}">ทุกวิชา</a></li>
                    </ul>
                </div>
                <button class="btn btn-outline-danger export-btn" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i>พิมพ์
                </button>
            </div>
        </div>

        {{-- Row 2: Search / filter (client-side JS) --}}
        <div class="row g-2 align-items-end pt-2 border-top">
            <div class="col-md-4">
                <label class="form-label fw-semibold small mb-1">
                    <i class="bi bi-search me-1"></i>ค้นหา
                </label>
                <input type="text" id="ss-search" class="form-control form-control-sm"
                       placeholder="รหัสโครงงาน / ชื่อโครงงาน / นักศึกษา / ที่ปรึกษา">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold small mb-1">
                    <i class="bi bi-flag me-1"></i>สถานะโครงงาน
                </label>
                <select id="ss-status" class="form-select form-select-sm">
                    <option value="">ทั้งหมด</option>
                    <option value="in_progress">กำลังดำเนินการ</option>
                    <option value="submitted">ส่งงานแล้ว</option>
                    <option value="late_submission">ส่งงานล่าช้า</option>
                    <option value="approved">อนุมัติแล้ว</option>
                    <option value="pending">รออนุมัติ</option>
                    <option value="not_proposed">ยังไม่เสนอ</option>
                    <option value="rejected">ถูกปฏิเสธ</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold small mb-1">
                    <i class="bi bi-tag me-1"></i>ประเภท
                </label>
                <select id="ss-type" class="form-select form-select-sm">
                    <option value="">ทั้งหมด</option>
                    <option value="r">ปกติ (r)</option>
                    <option value="s">พิเศษ (s)</option>
                    <option value="m">ผสม (m)</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold small mb-1">
                    <i class="bi bi-calendar-event me-1"></i>วันสอบ
                </label>
                <select id="ss-exam" class="form-select form-select-sm">
                    <option value="">ทั้งหมด</option>
                    <option value="1">กำหนดแล้ว</option>
                    <option value="0">ยังไม่กำหนด</option>
                </select>
            </div>
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="button" id="ss-clear" class="btn btn-outline-secondary btn-sm flex-fill">
                    <i class="bi bi-x-lg me-1"></i>ล้างตัวกรอง
                </button>
            </div>
        </div>
    </div>

    {{-- ── Stats row ───────────────────────────────────────── --}}
    <div class="row g-3 mb-3">
        @php
            $allProjects = $cs303->concat($cs403);
            $statuses = ['in_progress','submitted','approved','not_proposed','pending'];
        @endphp
        <div class="col-6 col-md-3 col-lg-2">
            <div class="page-card p-3 text-center">
                <div class="stat-num text-primary">{{ $cs303->count() + $cs403->count() }}</div>
                <div class="small text-muted">โครงงานทั้งหมด</div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-lg-2">
            <div class="page-card p-3 text-center">
                <div class="stat-num text-info">{{ $cs303->count() }}</div>
                <div class="small text-muted">CS303</div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-lg-2">
            <div class="page-card p-3 text-center">
                <div class="stat-num text-warning">{{ $cs403->count() }}</div>
                <div class="small text-muted">CS403</div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-lg-2">
            <div class="page-card p-3 text-center">
                <div class="stat-num text-success">{{ $allProjects->whereIn('status_project',['submitted','approved'])->count() }}</div>
                <div class="small text-muted">ส่งงานแล้ว</div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-lg-2">
            <div class="page-card p-3 text-center">
                <div class="stat-num text-danger">{{ $allProjects->whereNull('exam_datetime')->count() }}</div>
                <div class="small text-muted">ยังไม่กำหนดวันสอบ</div>
            </div>
        </div>
    </div>

    {{-- ── Tabs ────────────────────────────────────────────── --}}
    <ul class="nav subject-tab mb-0" id="subjectTab">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#cs303panel">
                <i class="bi bi-journal-code me-1"></i>CS303
                <span class="badge bg-primary ms-1 rounded-pill">{{ $cs303->count() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#cs403panel">
                <i class="bi bi-journal-text me-1"></i>CS403
                <span class="badge bg-warning text-dark ms-1 rounded-pill">{{ $cs403->count() }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content page-card" style="border-radius: 0 14px 14px 14px;">
        {{-- CS303 --}}
        <div class="tab-pane fade show active" id="cs303panel">
            @include('coordinator.subject-summary._table', ['projects' => $cs303, 'subject' => 'CS303'])
        </div>
        {{-- CS403 --}}
        <div class="tab-pane fade" id="cs403panel">
            @include('coordinator.subject-summary._table', ['projects' => $cs403, 'subject' => 'CS403'])
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
@media print {
    .filter-bar, nav, .btn, .dropdown { display: none !important; }
    .page-card { box-shadow: none !important; }
    body { background: white !important; }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    const searchEl  = document.getElementById('ss-search');
    const statusEl  = document.getElementById('ss-status');
    const typeEl    = document.getElementById('ss-type');
    const examEl    = document.getElementById('ss-exam');
    const clearBtn  = document.getElementById('ss-clear');

    // Tab badge elements
    const badge303 = document.querySelector('#subjectTab [data-bs-target="#cs303panel"] .badge');
    const badge403 = document.querySelector('#subjectTab [data-bs-target="#cs403panel"] .badge');

    function applyFilter() {
        const q      = searchEl.value.toLowerCase().trim();
        const status = statusEl.value;
        const type   = typeEl.value;
        const exam   = examEl.value;

        ['cs303panel', 'cs403panel'].forEach(panelId => {
            const panel   = document.getElementById(panelId);
            if (!panel) return;
            const rows    = panel.querySelectorAll('tbody tr');
            const countEl = panel.querySelector('.visible-count');
            let visible   = 0;

            rows.forEach(row => {
                const matchQ      = !q      || [row.dataset.code, row.dataset.name, row.dataset.members, row.dataset.advisor].some(v => v && v.includes(q));
                const matchStatus = !status || row.dataset.status === status;
                const matchType   = !type   || (row.dataset.type && row.dataset.type.includes(type));
                const matchExam   = !exam   || row.dataset.hasExam === exam;

                const show = matchQ && matchStatus && matchType && matchExam;
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            if (countEl) countEl.textContent = visible;

            // Update tab badge
            if (panelId === 'cs303panel' && badge303) badge303.textContent = visible;
            if (panelId === 'cs403panel' && badge403) badge403.textContent = visible;
        });
    }

    searchEl.addEventListener('input',  applyFilter);
    statusEl.addEventListener('change', applyFilter);
    typeEl.addEventListener('change',   applyFilter);
    examEl.addEventListener('change',   applyFilter);

    clearBtn.addEventListener('click', function () {
        searchEl.value = '';
        statusEl.value = '';
        typeEl.value   = '';
        examEl.value   = '';
        applyFilter();
    });
})();
</script>
@endpush

@extends('layouts.app')

@section('title', 'โครงงานของฉัน')

@push('styles')
<style>
    .page-header { background: white; border-radius: var(--border-radius); padding: 2rem; margin-bottom: 2rem; box-shadow: var(--shadow-light); }
    .page-header h2 { color: #2c3e50; font-weight: 700; font-size: 2rem; margin-bottom: 0.5rem; }
    .modern-card { background: white; border-radius: var(--border-radius); box-shadow: var(--shadow-light); margin-bottom: 2rem; overflow: hidden; }
    .modern-card-header { padding: 1.5rem; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-bottom: 2px solid #dee2e6; }
    .modern-card-header h4 { margin: 0; color: #2c3e50; font-weight: 600; display: flex; align-items: center; gap: 0.75rem; }
    .table-modern { margin-bottom: 0; }
    .table-modern thead th { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); font-weight: 600; color: #2c3e50; border: none; padding: 1rem 0.75rem; white-space: nowrap; }
    .table-modern tbody tr { transition: var(--transition); }
    .table-modern tbody tr:hover { background-color: rgba(102, 126, 234, 0.05); }
    .table-modern td { vertical-align: middle; padding: .75rem 0.75rem; border-bottom: 1px solid #f0f0f0; }
    .modern-btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; border-radius: var(--border-radius); font-weight: 600; transition: var(--transition); border: none; }
    .modern-btn.btn-light { background: #f8f9fa; color: #2c3e50; }
    .modern-btn:hover { transform: translateY(-2px); box-shadow: var(--shadow-medium); }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
    .stat-card { background: white; border-radius: var(--border-radius); padding: 1.5rem; box-shadow: var(--shadow-light); transition: var(--transition); border-left: 4px solid; position: relative; overflow: hidden; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-medium); }
    .stat-card.primary { border-left-color: #667eea; }
    .stat-card.warning { border-left-color: #f6ad55; }
    .stat-card.success { border-left-color: #48bb78; }
    .stat-card.info    { border-left-color: #4299e1; }
    .stat-card-icon { position: absolute; top: 50%; right: 1.5rem; transform: translateY(-50%); font-size: 4rem; opacity: 0.1; }
    .stat-card-title { font-size: 0.875rem; color: #718096; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem; }
    .stat-card-value { font-size: 2.5rem; font-weight: 700; color: #2d3748; margin-bottom: 0; }
    .empty-state { padding: 3rem; text-align: center; color: #718096; }
    .empty-state i { font-size: 4rem; margin-bottom: 1rem; opacity: 0.3; }
    .btn-icon { padding: .28rem .55rem; font-size: .78rem; border-radius: 6px; line-height: 1; }
</style>
@endpush

@section('content')
<div class="container">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-folder-fill me-2"></i>โครงงานของฉัน
                </h2>
                <p class="mb-0 opacity-75">รายการโครงงานที่คุณเป็นอาจารย์ที่ปรึกษาหรือกรรมการ</p>
            </div>
            <a href="{{ route('lecturer.dashboard') }}" class="btn modern-btn btn-light">
                <i class="bi bi-arrow-left"></i>
                <span>กลับ Dashboard</span>
            </a>
        </div>
    </div>

    {{-- Stats --}}
    @php
        $total     = $projects->count();
        $advisorCt = $projects->filter(fn($p) => $p->advisorLecturer?->user_code == $userCode)->count();
        $commitCt  = $total - $advisorCt;
        $submitted = $projects->whereIn('status_project',['submitted','late_submission'])->count();
    @endphp
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-card-icon"><i class="bi bi-folder"></i></div>
            <div class="stat-card-title">โครงงานทั้งหมด</div>
            <div class="stat-card-value">{{ $total }}</div>
        </div>
        <div class="stat-card success">
            <div class="stat-card-icon"><i class="bi bi-person-fill"></i></div>
            <div class="stat-card-title">อาจารย์ที่ปรึกษา</div>
            <div class="stat-card-value">{{ $advisorCt }}</div>
        </div>
        <div class="stat-card warning">
            <div class="stat-card-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-card-title">กรรมการ</div>
            <div class="stat-card-value">{{ $commitCt }}</div>
        </div>
        <div class="stat-card info">
            <div class="stat-card-icon"><i class="bi bi-file-earmark-check"></i></div>
            <div class="stat-card-title">ส่งเล่มแล้ว</div>
            <div class="stat-card-value">{{ $submitted }}</div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="modern-card mb-3">
        <div class="modern-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="bi bi-funnel"></i>ตัวกรอง</h4>
                <span class="badge bg-primary rounded-pill" id="proj-count">{{ $projects->count() }} รายการ</span>
            </div>
        </div>
        <div class="p-3">
            {{-- Text search (client-side) --}}
            <div class="mb-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="projSearch" class="form-control"
                           placeholder="ค้นหา ชื่อโครงงาน / รหัส / ชื่อนักศึกษา...">
                    <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('projSearch').value='';filterProjTable()">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
            {{-- Dropdown filters (server-side) --}}
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
    </div>

    {{-- Table --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h4><i class="bi bi-table"></i>รายการโครงงาน</h4>
        </div>
        <div class="table-responsive">
            <table class="table table-modern mb-0" id="projTable">
                <thead>
                    <tr>
                        <th style="width:3%">ลำดับ</th>
                        <th style="width:10%">รหัสโครงงาน</th>
                        <th style="width:9%">ตำแหน่ง</th>
                        <th style="width:24%">ชื่อโครงงาน</th>
                        <th style="width:6%">วิชา</th>
                        <th style="width:6%">ปี/เทอม</th>
                        <th style="width:16%">สมาชิก</th>
                        <th style="width:11%">วันสอบ</th>
                        <th style="width:8%">สถานะ</th>
                        <th style="width:7%" class="text-center">การดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $i => $project)
                        @php
                            $isAdvisor = $project->advisorLecturer?->user_code == $userCode;
                            $roleLabel = $isAdvisor ? 'อาจารย์ที่ปรึกษา' : 'กรรมการ';
                            $roleBadge = $isAdvisor ? 'bg-primary' : 'bg-success';
                            $statusMap = [
                                'approved'        => ['bg-success text-white',  'อนุมัติแล้ว'],
                                'in_progress'     => ['bg-primary text-white',  'กำลังดำเนินการ'],
                                'submitted'       => ['bg-warning text-dark',   'ส่งเล่มแล้ว'],
                                'late_submission' => ['bg-danger text-white',   'ส่งล่าช้า'],
                                'passed'          => ['bg-success text-white',  'ผ่าน'],
                                'failed'          => ['bg-danger text-white',   'ไม่ผ่าน'],
                            ];
                            [$bc, $bl] = $statusMap[$project->status_project] ?? ['bg-secondary text-white', $project->status_project];
                        @endphp
                        <tr class="proj-row">
                            <td class="text-muted small">{{ $i + 1 }}</td>
                            <td><code class="small">{{ $project->project_code }}</code></td>
                            <td><span class="badge {{ $roleBadge }} small">{{ $roleLabel }}</span></td>
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
                        <tr>
                            <td colspan="10">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>ไม่มีโครงงานที่ตรงกับเงื่อนไข</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
function filterProjTable() {
    const q = document.getElementById('projSearch').value.toLowerCase().trim();
    const rows = document.querySelectorAll('#projTable .proj-row');
    let visible = 0;
    rows.forEach(function(row) {
        const text = row.innerText.toLowerCase();
        const show = !q || text.includes(q);
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const badge = document.getElementById('proj-count');
    if (badge) badge.textContent = visible + ' รายการ';
}
document.getElementById('projSearch').addEventListener('input', filterProjTable);
</script>
@endpush
@endsection

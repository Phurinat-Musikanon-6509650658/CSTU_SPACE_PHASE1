@extends('layouts.app')

@section('title', 'ประเมินโครงงาน')

@push('styles')
<style>
    .page-header { background: white; border-radius: var(--border-radius); padding: 2rem; margin-bottom: 2rem; box-shadow: var(--shadow-light); }
    .page-header h2 { color: #2c3e50; font-weight: 700; font-size: 2rem; margin-bottom: 0.5rem; }
    .modern-card { background: white; border-radius: var(--border-radius); box-shadow: var(--shadow-light); margin-bottom: 2rem; overflow: hidden; }
    .modern-card-header { padding: 1.5rem; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-bottom: 2px solid #dee2e6; }
    .modern-card-header h4 { margin: 0; color: #2c3e50; font-weight: 600; display: flex; align-items: center; gap: 0.75rem; }
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
    .project-card { background: white; border-radius: var(--border-radius); padding: 1.5rem; margin-bottom: 1rem; box-shadow: var(--shadow-light); transition: var(--transition); }
    .project-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-medium); }
    .empty-state { padding: 3rem; text-align: center; color: #718096; }
    .empty-state i { font-size: 4rem; margin-bottom: 1rem; opacity: 0.3; }
</style>
@endpush

@section('content')
<div class="container">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-clipboard-check me-2"></i>ประเมินและให้คะแนนโครงงาน
                </h2>
                <p class="mb-0 opacity-75">โครงงานที่คุณเป็นอาจารย์ที่ปรึกษาหรือคณะกรรมการ</p>
            </div>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <a href="{{ route('lecturer.evaluations.export-all') }}" target="_blank"
                   class="btn modern-btn btn-light">
                    <i class="bi bi-printer"></i>Export รวม PDF
                </a>
                {{-- Sort toggle --}}
                @php
                    $nextSort  = $sortBy === 'exam' ? 'code' : 'exam';
                    $sortIcon  = $sortBy === 'exam' ? 'bi-calendar-event'  : 'bi-sort-alpha-down';
                    $sortLabel = $sortBy === 'exam' ? 'เรียงตามวันเวลาสอบ' : 'เรียงตามรหัสโครงงาน';
                @endphp
                <a href="{{ request()->fullUrlWithQuery(['sort' => $nextSort, 'page' => 1]) }}"
                   class="btn modern-btn {{ $sortBy === 'exam' ? 'btn-warning text-dark' : 'btn-light' }}"
                   title="สลับการเรียง">
                    <i class="bi {{ $sortIcon }}"></i>{{ $sortLabel }}
                    <i class="bi bi-arrow-left-right opacity-75"></i>
                </a>
                <a href="{{ route('lecturer.dashboard') }}" class="btn modern-btn btn-light">
                    <i class="bi bi-arrow-left"></i>
                    <span>กลับ Dashboard</span>
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filter --}}
    <div class="modern-card mb-3">
        <div class="modern-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="bi bi-funnel"></i>ตัวกรอง</h4>
                <span class="badge bg-primary rounded-pill">{{ $projects->count() }} โครงงาน</span>
            </div>
        </div>
        <div class="p-3">
            {{-- Text search (client-side) --}}
            <div class="mb-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="evalSearch" class="form-control"
                           placeholder="ค้นหา ชื่อโครงงาน / รหัส / ชื่อนักศึกษา...">
                    <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('evalSearch').value='';filterEvalCards()">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
            <form method="GET" action="{{ route('lecturer.evaluations.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-sm-6 col-md-2">
                        <label class="form-label">ปีการศึกษา</label>
                        <select name="year" class="form-select form-select-sm">
                            <option value="">ทั้งหมด</option>
                            @foreach($years as $yr)
                                <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <label class="form-label">ภาคเรียน</label>
                        <select name="semester" class="form-select form-select-sm">
                            <option value="">ทั้งหมด</option>
                            <option value="1" {{ request('semester') == '1' ? 'selected' : '' }}>เทอม 1</option>
                            <option value="2" {{ request('semester') == '2' ? 'selected' : '' }}>เทอม 2</option>
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <label class="form-label">รหัสวิชา</label>
                        <select name="subject" class="form-select form-select-sm">
                            <option value="">ทั้งหมด</option>
                            <option value="CS303" {{ request('subject') == 'CS303' ? 'selected' : '' }}>CS303</option>
                            <option value="CS403" {{ request('subject') == 'CS403' ? 'selected' : '' }}>CS403</option>
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <label class="form-label">สถานะ</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">ทั้งหมด</option>
                            <option value="done"    {{ request('status') == 'done'    ? 'selected' : '' }}>ให้คะแนนแล้ว</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>ยังไม่ให้คะแนน</option>
                        </select>
                    </div>
                    <div class="col-auto d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="bi bi-search me-1"></i>ค้นหา
                        </button>
                        <a href="{{ route('lecturer.evaluations.index') }}" class="btn btn-outline-secondary btn-sm" title="ล้างตัวกรอง">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Projects List --}}
    <div class="row">
        @forelse($projects as $project)
            @php
                $myUserCode   = Auth::guard('web')->user()->user_code;
                $isAdvisor    = $project->advisorLecturer?->user_code == $myUserCode;
                $roleLabel    = $isAdvisor ? 'อาจารย์ที่ปรึกษา' : 'กรรมการ';
                $roleBadge    = $isAdvisor ? 'primary' : 'success';
                $myEvaluation = $project->evaluations->first();
                $hasEvaluated = $myEvaluation !== null;
            @endphp

            <div class="col-12 eval-card-wrap">
                <div class="project-card">
                    <div class="row align-items-center">
                        <!-- Project Info -->
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-2">
                                <div class="badge bg-primary me-2" style="font-size: 1rem;">{{ sprintf('%02d', $project->group_id) }}</div>
                                <code class="text-primary fw-bold">{{ $project->project_code }}</code>
                            </div>
                            <h6 class="fw-bold mb-1">{{ $project->project_name ?? 'ยังไม่ระบุชื่อ' }}</h6>
                            <small class="text-muted">
                                @foreach($project->group->members as $member)
                                    {{ $member->student->firstname_std ?? '' }} {{ $member->student->lastname_std ?? '' }}@if(!$loop->last), @endif
                                @endforeach
                            </small>
                        </div>

                        <!-- Your Role -->
                        <div class="col-md-2">
                            <label class="small text-muted d-block mb-1">ตำแหน่งของคุณ</label>
                            <span class="badge bg-{{ $roleBadge }}">{{ $roleLabel }}</span>
                        </div>

                        <!-- Exam DateTime -->
                        <div class="col-md-2">
                            <label class="small text-muted d-block mb-1">วันเวลาสอบ</label>
                            @if($project->exam_datetime)
                                <strong class="text-success" style="white-space:nowrap;">
                                    <i class="bi bi-calendar-event me-1"></i>{{ thaiDateTime($project->exam_datetime) }}
                                </strong>
                            @else
                                <span class="text-muted">ยังไม่กำหนด</span>
                            @endif
                        </div>

                        <!-- Evaluation Status -->
                        <div class="col-md-2">
                            <label class="small text-muted d-block mb-1">สถานะการให้คะแนน</label>
                            @if($hasEvaluated)
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle-fill me-1"></i>ให้คะแนนแล้ว
                                </span>
                                <br><small class="text-muted">{{ number_format($myEvaluation->total_score, 2) }} คะแนน</small>
                            @else
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-exclamation-triangle me-1"></i>ยังไม่ให้คะแนน
                                </span>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="col-md-2 text-end">
                            @if($hasEvaluated)
                                <a href="{{ route('lecturer.evaluations.form', $project->project_id) }}" 
                                   class="btn btn-sm btn-outline-primary mb-1 w-100">
                                    <i class="bi bi-pencil me-1"></i>แก้ไขคะแนน
                                </a>
                            @else
                                <a href="{{ route('lecturer.evaluations.form', $project->project_id) }}" 
                                   class="btn btn-sm btn-primary w-100">
                                    <i class="bi bi-clipboard-check me-1"></i>ให้คะแนน
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="modern-card">
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>ไม่พบโครงงานที่ต้องประเมิน</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

</div>

@push('scripts')
<script>
function filterEvalCards() {
    const q = document.getElementById('evalSearch').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.eval-card-wrap');
    let visible = 0;
    cards.forEach(function(card) {
        const show = !q || card.innerText.toLowerCase().includes(q);
        card.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const badge = document.querySelector('.badge.bg-primary.rounded-pill');
    if (badge) badge.textContent = visible + ' โครงงาน';
}
document.getElementById('evalSearch').addEventListener('input', filterEvalCards);
</script>
@endpush
@endsection

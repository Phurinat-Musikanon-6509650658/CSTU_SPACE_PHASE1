@extends('layouts.app')

@section('title', 'การให้คะแนนโครงงาน | CSTU SPACE')

@push('styles')
<style>
.eval-row { transition: background .12s; }
.eval-row:hover { background: #fafbff; }
.eval-row:last-child { border-bottom: none !important; }
.eval-row .accent-strip { width: 5px; flex-shrink: 0; background: #e3e6f0; }
.eval-row.complete .accent-strip { background: linear-gradient(180deg,#1cc88a,#13855c); }
.eval-row.partial  .accent-strip { background: linear-gradient(180deg,#f6c23e,#d4a017); }
.eval-row.empty    .accent-strip { background: #e3e6f0; }
.eval-progress-bar { height: 6px; border-radius: 6px; background:#e9ecef; overflow:hidden; }
.eval-progress-bar .fill { height:100%; border-radius:6px; transition:width .4s ease; background:#e9ecef; }
.eval-progress-bar .fill.complete { background: linear-gradient(90deg,#1cc88a,#38ef7d); }
.eval-progress-bar .fill.partial  { background: linear-gradient(90deg,#f6c23e,#ffd461); }
.info-label { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af; }
.stat-card { border:none; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,.07); }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Header --}}
    <div class="mb-4">
        <a href="{{ route('coordinator.dashboard') }}" class="btn btn-link text-decoration-none ps-0 text-muted">
            <i class="bi bi-chevron-left me-1"></i>กลับ Dashboard
        </a>
        <h1 class="h2 fw-bold mb-0 mt-1">
            <i class="bi bi-clipboard-check me-2 text-primary"></i>การให้คะแนนโครงงาน
        </h1>
        <p class="text-muted small mb-0">ติดตามสถานะการให้คะแนนจากอาจารย์ที่ปรึกษาและคณะกรรมการ</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats --}}
    @php
        $total         = $projects->count();
        $completeCount = $projects->filter(function($p) {
            $exp  = collect([$p->advisor_code,$p->committee1_code,$p->committee2_code,$p->committee3_code])->filter()->count();
            $done = $p->evaluations->pluck('evaluator_role')->unique()->count();
            return $exp > 0 && $done >= $exp;
        })->count();
        $partialCount  = $projects->filter(function($p) {
            $exp  = collect([$p->advisor_code,$p->committee1_code,$p->committee2_code,$p->committee3_code])->filter()->count();
            $done = $p->evaluations->pluck('evaluator_role')->unique()->count();
            return $done > 0 && $done < $exp;
        })->count();
        $emptyCount    = $total - $completeCount - $partialCount;
    @endphp
    <div class="row g-3 mb-4">
        @foreach([
            ['โครงงานทั้งหมด',   $total,         'primary',   'bi-folder2'],
            ['ครบทุกคนแล้ว',     $completeCount, 'success',   'bi-check-circle-fill'],
            ['ให้คะแนนบางส่วน', $partialCount,  'warning',   'bi-hourglass-split'],
            ['ยังไม่มีคะแนน',    $emptyCount,    'secondary', 'bi-clock'],
        ] as [$label, $val, $color, $icon])
        <div class="col-6 col-md-3">
            <div class="stat-card card">
                <div class="card-body py-3 d-flex align-items-center gap-3">
                    <div style="width:44px;height:44px;border-radius:10px;background:var(--bs-{{ $color }}-bg-subtle);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi {{ $icon }} text-{{ $color }}" style="font-size:1.2rem;"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0 text-{{ $color }}">{{ $val }}</div>
                        <div class="small text-muted">{{ $label }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('coordinator.evaluations.index') }}" id="filterForm">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small mb-1">
                            <i class="bi bi-search me-1 text-muted"></i>ค้นหา
                        </label>
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="ชื่อโครงงาน หรือ รหัสโครงงาน"
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small mb-1">ปีการศึกษา</label>
                        <select name="year" class="form-select form-select-sm auto-submit">
                            <option value="">ทั้งหมด</option>
                            @foreach($years as $yr)
                                <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small mb-1">เทอม</label>
                        <select name="semester" class="form-select form-select-sm auto-submit">
                            <option value="">ทั้งหมด</option>
                            <option value="1" {{ request('semester') == '1' ? 'selected' : '' }}>เทอม 1</option>
                            <option value="2" {{ request('semester') == '2' ? 'selected' : '' }}>เทอม 2</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small mb-1">สถานะ</label>
                        <select name="eval_status" class="form-select form-select-sm auto-submit">
                            <option value="">ทั้งหมด</option>
                            <option value="complete" {{ request('eval_status') == 'complete' ? 'selected' : '' }}>ครบแล้ว</option>
                            <option value="partial"  {{ request('eval_status') == 'partial'  ? 'selected' : '' }}>บางส่วน</option>
                            <option value="none"     {{ request('eval_status') == 'none'     ? 'selected' : '' }}>ยังไม่มีคะแนน</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill">
                            <i class="bi bi-search me-1"></i>ค้นหา
                        </button>
                        <a href="{{ route('coordinator.evaluations.index') }}" class="btn btn-outline-secondary btn-sm px-3" title="ล้างตัวกรอง">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- List --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2">
            <span class="fw-semibold small"><i class="bi bi-table me-2 text-primary"></i>รายการโครงงาน</span>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:.72rem;">{{ $total }} โครงงาน</span>
        </div>
        <div class="card-body p-0">
            @forelse($projects as $project)
            @php
                $exp        = collect([$project->advisor_code,$project->committee1_code,$project->committee2_code,$project->committee3_code])->filter()->count();
                $done       = $project->evaluations->pluck('evaluator_role')->unique()->count();
                $isComplete = $exp > 0 && $done >= $exp;
                $pct        = $exp > 0 ? min(100, round(($done / $exp) * 100)) : 0;
                $rowStatus  = $isComplete ? 'complete' : ($done > 0 ? 'partial' : 'empty');
            @endphp
            <div class="eval-row border-bottom d-flex align-items-stretch {{ $rowStatus }}">
                <div class="accent-strip"></div>
                <div class="flex-fill px-4 py-3">
                    <div class="row align-items-center g-2">

                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <code class="text-primary fw-bold" style="font-size:.82rem;">{{ $project->project_code }}</code>
                                @if($isComplete)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:.67rem;"><i class="bi bi-check-circle-fill me-1"></i>ครบแล้ว</span>
                                @elseif($done > 0)
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size:.67rem;"><i class="bi bi-hourglass-split me-1"></i>บางส่วน</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size:.67rem;"><i class="bi bi-clock me-1"></i>ยังไม่มีคะแนน</span>
                                @endif
                            </div>
                            <div class="fw-semibold small text-dark">{{ $project->project_name ?? 'ยังไม่ระบุชื่อ' }}</div>
                            <div class="text-muted mt-1" style="font-size:.77rem;">
                                <i class="bi bi-people me-1"></i>
                                @foreach($project->group->members as $member)
                                    {{ $member->student->firstname_std ?? '' }} {{ $member->student->lastname_std ?? '' }}@if(!$loop->last), @endif
                                @endforeach
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-label mb-1">คณะกรรมการ</div>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach([
                                    ['ที่ปรึกษา', $project->advisor_code,    'bg-primary-subtle text-primary border-primary-subtle'],
                                    ['ก.1',       $project->committee1_code, 'bg-success-subtle text-success border-success-subtle'],
                                    ['ก.2',       $project->committee2_code, 'bg-success-subtle text-success border-success-subtle'],
                                    ['ก.3',       $project->committee3_code, 'bg-success-subtle text-success border-success-subtle'],
                                ] as [$lbl, $code, $cls])
                                    @if($code)
                                        <span class="badge {{ $cls }} border" style="font-size:.68rem;">{{ $code }}</span>
                                    @endif
                                @endforeach
                                @if(!$project->advisor_code && !$project->committee1_code)
                                    <span class="text-muted small">ยังไม่กำหนด</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="d-flex justify-content-between align-items-baseline mb-1">
                                <span class="info-label">ความคืบหน้า</span>
                                <span class="fw-bold small {{ $isComplete ? 'text-success' : ($done > 0 ? 'text-warning' : 'text-secondary') }}">
                                    {{ $done }} / {{ $exp }}
                                </span>
                            </div>
                            <div class="eval-progress-bar">
                                <div class="fill {{ $rowStatus }}" style="width:{{ $pct }}%"></div>
                            </div>
                            <div class="text-muted mt-1" style="font-size:.72rem;">
                                {{ $exp === 0 ? 'ยังไม่กำหนดคณะกรรมการ' : $pct . '% เสร็จสิ้น' }}
                            </div>
                        </div>

                        <div class="col-md-2 text-end">
                            <a href="{{ route('coordinator.evaluations.scores', $project->project_id) }}"
                               class="btn btn-sm {{ $isComplete ? 'btn-outline-success' : 'btn-outline-primary' }}">
                                <i class="bi bi-clipboard-data me-1"></i>ดูคะแนน
                            </a>
                        </div>

                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <i class="bi bi-clipboard-x display-4 text-muted d-block mb-2" style="opacity:.3;"></i>
                <p class="text-muted small mb-0">ไม่พบข้อมูลโครงงาน</p>
            </div>
            @endforelse
        </div>
        @if($total > 0)
        <div class="card-footer bg-white border-top text-muted small">
            แสดงทั้งหมด <strong>{{ $total }}</strong> โครงงาน
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.auto-submit').forEach(el => {
    el.addEventListener('change', () => document.getElementById('filterForm').submit());
});
</script>
@endpush

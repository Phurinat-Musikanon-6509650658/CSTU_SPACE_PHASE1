@extends('layouts.app')

@section('title', 'การให้คะแนนโครงงาน | CSTU SPACE')

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
    .stat-card.primary   { border-left-color: #667eea; }
    .stat-card.success   { border-left-color: #48bb78; }
    .stat-card.warning   { border-left-color: #f6ad55; }
    .stat-card.secondary { border-left-color: #a0aec0; }
    .stat-card-icon { position: absolute; top: 50%; right: 1.5rem; transform: translateY(-50%); font-size: 4rem; opacity: 0.1; }
    .stat-card-title { font-size: 0.875rem; color: #718096; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem; }
    .stat-card-value { font-size: 2.5rem; font-weight: 700; color: #2d3748; margin-bottom: 0; }
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
    .empty-state { padding: 3rem; text-align: center; color: #718096; }
    .empty-state i { font-size: 4rem; margin-bottom: 1rem; opacity: 0.3; }
</style>
@endpush

@section('content')
<div class="container">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-clipboard-check me-2"></i>การให้คะแนนโครงงาน
                </h2>
                <p class="mb-0 opacity-75">ติดตามสถานะการให้คะแนนจากอาจารย์ที่ปรึกษาและคณะกรรมการ</p>
            </div>
            <a href="{{ route('coordinator.dashboard') }}" class="btn modern-btn btn-light">
                <i class="bi bi-arrow-left"></i>
                <span>กลับ Dashboard</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── Export Card ───────────────────────────────────────────────────── --}}
    <a href="{{ route('coordinator.evaluations.export-preview') }}"
       class="text-decoration-none">
        <div class="card border-0 shadow-sm mb-4" style="cursor:pointer;transition:box-shadow .2s;" onmouseover="this.style.boxShadow='0 4px 20px rgba(28,200,138,.25)'" onmouseout="this.style.boxShadow=''">
            <div class="card-body d-flex align-items-center gap-3 py-3"
                 style="background:linear-gradient(135deg,#1cc88a15,#13855c08);border-radius:12px;border:1px solid #bbf7d0;">
                <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#1cc88a,#13855c);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-file-earmark-spreadsheet text-white" style="font-size:1.4rem;"></i>
                </div>
                <div class="flex-fill">
                    <div class="fw-bold text-success">สรุปคะแนนและส่งออกคะแนน</div>
                    <div class="small text-muted">ดูตัวอย่างแบบ real-time · Export .xlsx 2 sheet (แยกตามอาจารย์ / สรุปรวมเฉลี่ย)</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:.72rem;">
                        <i class="bi bi-activity me-1"></i>Real-time
                    </span>
                    <i class="bi bi-chevron-right text-success"></i>
                </div>
            </div>
        </div>
    </a>

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
        $emptyCount = $total - $completeCount - $partialCount;
    @endphp
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-card-icon"><i class="bi bi-folder2"></i></div>
            <div class="stat-card-title">โครงงานทั้งหมด</div>
            <div class="stat-card-value">{{ $total }}</div>
        </div>
        <div class="stat-card success">
            <div class="stat-card-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stat-card-title">ครบทุกคนแล้ว</div>
            <div class="stat-card-value">{{ $completeCount }}</div>
        </div>
        <div class="stat-card warning">
            <div class="stat-card-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-card-title">ให้คะแนนบางส่วน</div>
            <div class="stat-card-value">{{ $partialCount }}</div>
        </div>
        <div class="stat-card secondary">
            <div class="stat-card-icon"><i class="bi bi-clock"></i></div>
            <div class="stat-card-title">ยังไม่มีคะแนน</div>
            <div class="stat-card-value">{{ $emptyCount }}</div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="modern-card mb-3">
        <div class="modern-card-header">
            <h4><i class="bi bi-funnel"></i>ตัวกรอง</h4>
        </div>
        <div class="p-3">
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
    <div class="modern-card">
        <div class="modern-card-header">
            <h4>
                <i class="bi bi-table"></i>รายการโครงงาน
                <span class="badge bg-primary ms-auto" style="font-size:.75rem;">{{ $total }} โครงงาน</span>
            </h4>
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
            <div class="empty-state">
                <i class="bi bi-clipboard-x"></i>
                <p>ไม่พบข้อมูลโครงงาน</p>
            </div>
            @endforelse
        </div>
        @if($total > 0)
        <div class="p-3 border-top text-muted small">
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

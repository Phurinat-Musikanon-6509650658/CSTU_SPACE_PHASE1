@extends('layouts.app')

@section('title', 'การให้คะแนนโครงงาน | CSTU SPACE')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- Header -->
    <div class="mb-4">
        <a href="{{ route('coordinator.dashboard') }}" class="btn btn-link text-decoration-none ps-0 text-muted">
            <i class="bi bi-chevron-left me-1"></i>กลับ Dashboard
        </a>
        <div class="mt-1">
            <h1 class="h3 fw-bold mb-1">
                <i class="bi bi-clipboard-check me-2 text-primary"></i>การให้คะแนนโครงงาน
            </h1>
            <p class="text-muted mb-0 small">ติดตามสถานะการให้คะแนนจากอาจารย์ที่ปรึกษาและคณะกรรมการ</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Summary Stats -->
    @php
        $allItems = $projects->getCollection();
        $totalShown = $allItems->count();
        $completeCount = $allItems->filter(function($p) {
            $exp = collect([$p->advisor_code, $p->committee1_code, $p->committee2_code, $p->committee3_code])->filter()->count();
            $done = $p->evaluations->pluck('evaluator_role')->unique()->count();
            return $exp > 0 && $done >= $exp;
        })->count();
        $partialCount = $allItems->filter(function($p) {
            $exp = collect([$p->advisor_code, $p->committee1_code, $p->committee2_code, $p->committee3_code])->filter()->count();
            $done = $p->evaluations->pluck('evaluator_role')->unique()->count();
            return $done > 0 && $done < $exp;
        })->count();
        $emptyCount = $totalShown - $completeCount - $partialCount;
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3 text-center">
                    <div class="h3 fw-bold text-primary mb-0">{{ $projects->total() }}</div>
                    <div class="small text-muted">โครงงานทั้งหมด</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3 text-center">
                    <div class="h3 fw-bold text-success mb-0">{{ $completeCount }}</div>
                    <div class="small text-muted">ครบทุกคน</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3 text-center">
                    <div class="h3 fw-bold text-warning mb-0">{{ $partialCount }}</div>
                    <div class="small text-muted">ให้คะแนนบางส่วน</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3 text-center">
                    <div class="h3 fw-bold text-secondary mb-0">{{ $emptyCount }}</div>
                    <div class="small text-muted">ยังไม่มีคะแนน</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('coordinator.evaluations.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small mb-1">ภาคเรียน</label>
                        <select name="semester" class="form-select form-select-sm">
                            <option value="">ทั้งหมด</option>
                            <option value="1" {{ request('semester') == '1' ? 'selected' : '' }}>ภาคต้น (1)</option>
                            <option value="2" {{ request('semester') == '2' ? 'selected' : '' }}>ภาคปลาย (2)</option>
                        </select>
                    </div>
                    <div class="col-auto d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>ค้นหา
                        </button>
                        <a href="{{ route('coordinator.evaluations.index') }}" class="btn btn-outline-secondary btn-sm" title="ล้าง">
                            <i class="bi bi-x"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Project List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <span class="fw-semibold small"><i class="bi bi-table me-2 text-primary"></i>รายการโครงงาน</span>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:.72rem;">{{ $projects->total() }} โครงงาน</span>
        </div>
        <div class="card-body p-0">
            @forelse($projects as $project)
            @php
                $expectedCount = collect([
                    $project->advisor_code,
                    $project->committee1_code,
                    $project->committee2_code,
                    $project->committee3_code,
                ])->filter()->count();

                $evaluationsCount = $project->evaluations->pluck('evaluator_role')->unique()->count();
                $isComplete  = $expectedCount > 0 && $evaluationsCount >= $expectedCount;
                $percentage  = $expectedCount > 0 ? min(100, round(($evaluationsCount / $expectedCount) * 100)) : 0;
                $rowStatus   = $isComplete ? 'complete' : ($evaluationsCount > 0 ? 'partial' : 'empty');
            @endphp
            <div class="eval-row border-bottom d-flex align-items-stretch {{ $rowStatus }}">
                <div class="accent-strip"></div>
                <div class="flex-fill px-4 py-3">
                    <div class="row align-items-center g-3">

                        <!-- Project info -->
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <code class="text-primary fw-bold" style="font-size:.82rem;">{{ $project->project_code }}</code>
                                @if($isComplete)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:.67rem;">
                                        <i class="bi bi-check-circle-fill me-1"></i>ครบแล้ว
                                    </span>
                                @elseif($evaluationsCount > 0)
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size:.67rem;">
                                        <i class="bi bi-hourglass-split me-1"></i>รอเพิ่มเติม
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size:.67rem;">
                                        <i class="bi bi-clock me-1"></i>ยังไม่มีคะแนน
                                    </span>
                                @endif
                            </div>
                            <div class="fw-semibold text-dark small">{{ $project->project_name ?? 'ยังไม่ระบุชื่อ' }}</div>
                            <div class="text-muted mt-1" style="font-size:.77rem;">
                                <i class="bi bi-people me-1"></i>
                                @foreach($project->group->members as $member)
                                    {{ $member->student->firstname_std ?? '' }} {{ $member->student->lastname_std ?? '' }}@if(!$loop->last), @endif
                                @endforeach
                            </div>
                        </div>

                        <!-- Committee badges -->
                        <div class="col-md-3">
                            <div class="info-label mb-1">คณะกรรมการ</div>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach([
                                    ['ที่ปรึกษา', $project->advisor_code,    'bg-primary-subtle text-primary border-primary-subtle'],
                                    ['ก.1',       $project->committee1_code, 'bg-success-subtle text-success border-success-subtle'],
                                    ['ก.2',       $project->committee2_code, 'bg-success-subtle text-success border-success-subtle'],
                                    ['ก.3',       $project->committee3_code, 'bg-success-subtle text-success border-success-subtle'],
                                ] as [$label, $code, $cls])
                                    @if($code)
                                        <span class="badge {{ $cls }} border" style="font-size:.68rem;">{{ $code }}</span>
                                    @endif
                                @endforeach
                                @if(!$project->advisor_code && !$project->committee1_code)
                                    <span class="text-muted small">ยังไม่กำหนด</span>
                                @endif
                            </div>
                        </div>

                        <!-- Progress -->
                        <div class="col-md-3">
                            <div class="d-flex justify-content-between align-items-baseline mb-1">
                                <span class="info-label">การให้คะแนน</span>
                                <span class="fw-bold small {{ $isComplete ? 'text-success' : ($evaluationsCount > 0 ? 'text-warning' : 'text-secondary') }}">
                                    {{ $evaluationsCount }} / {{ $expectedCount }}
                                </span>
                            </div>
                            <div class="eval-progress-bar">
                                <div class="fill {{ $rowStatus }}" style="width:{{ $percentage }}%"></div>
                            </div>
                            <div class="text-muted mt-1" style="font-size:.72rem;">
                                @if($expectedCount === 0)
                                    ยังไม่กำหนดคณะกรรมการ
                                @else
                                    {{ $percentage }}% เสร็จสิ้น
                                @endif
                            </div>
                        </div>

                        <!-- Button -->
                        <div class="col-md-2 text-end">
                            <a href="{{ route('coordinator.evaluations.scores', $project->project_id) }}"
                               class="btn btn-sm {{ $isComplete ? 'btn-outline-success' : 'btn-primary' }}">
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
    </div>

    @if($projects->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-3">
        <small class="text-muted">แสดง {{ $projects->firstItem() }}–{{ $projects->lastItem() }} จาก {{ $projects->total() }} โครงงาน</small>
        {{ $projects->links() }}
    </div>
    @endif

</div>
@endsection

@push('styles')
<style>
.eval-row { transition: background .12s; }
.eval-row:hover { background: #fafbff; }
.eval-row:last-child { border-bottom: none !important; }
.eval-row .accent-strip {
    width: 5px; flex-shrink: 0;
    background: #e3e6f0;
}
.eval-row.complete .accent-strip { background: linear-gradient(180deg, #1cc88a, #13855c); }
.eval-row.partial  .accent-strip { background: linear-gradient(180deg, #f6c23e, #d4a017); }
.eval-row.empty    .accent-strip { background: #e3e6f0; }
.eval-progress-bar {
    height: 7px; border-radius: 6px;
    background: #e9ecef; overflow: hidden;
}
.eval-progress-bar .fill {
    height: 100%; border-radius: 6px;
    background: #e9ecef; transition: width .5s ease;
}
.eval-progress-bar .fill.complete { background: linear-gradient(90deg, #1cc88a, #38ef7d); }
.eval-progress-bar .fill.partial  { background: linear-gradient(90deg, #f6c23e, #ffd461); }
.info-label {
    font-size: .7rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em;
    color: #9ca3af;
}
</style>
@endpush

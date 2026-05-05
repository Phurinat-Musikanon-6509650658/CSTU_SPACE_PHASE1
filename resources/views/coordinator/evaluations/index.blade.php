@extends('layouts.app')

@section('title', 'ประเมินและให้คะแนนโครงงาน')

@push('styles')
<style>
    body { background-color: #f8f9fa; }

    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 1.25rem;
    }
    .card-header {
        font-weight: 600;
        border-radius: 12px 12px 0 0 !important;
    }

    /* Project row card */
    .project-card {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        background: white;
        transition: box-shadow 0.2s, transform 0.2s;
        margin-bottom: 1rem;
        overflow: hidden;
    }
    .project-card:hover {
        box-shadow: 0 6px 24px rgba(78, 115, 223, 0.15);
        transform: translateY(-2px);
    }
    .project-card .accent-bar {
        width: 5px;
        min-height: 100%;
        background: linear-gradient(180deg, #4e73df 0%, #224abe 100%);
        flex-shrink: 0;
    }
    .project-card .accent-bar.complete {
        background: linear-gradient(180deg, #1cc88a 0%, #13855c 100%);
    }

    .project-code {
        font-family: 'Courier New', monospace;
        background: rgba(78, 115, 223, 0.1);
        color: #4e73df;
        padding: 0.2rem 0.6rem;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.9rem;
    }
    .project-title {
        font-weight: 700;
        color: #2d3748;
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }
    .info-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        color: #9aa0b2;
        margin-bottom: 0.3rem;
    }

    /* Eval progress */
    .eval-fraction {
        font-size: 1.4rem;
        font-weight: 800;
        color: #4e73df;
        line-height: 1;
    }
    .progress-bar-eval {
        height: 8px;
        border-radius: 8px;
        background-color: #e9ecef;
        overflow: hidden;
        margin-top: 4px;
    }
    .progress-bar-eval .fill {
        height: 100%;
        border-radius: 8px;
        background: linear-gradient(90deg, #1cc88a, #38ef7d);
        transition: width 0.5s ease;
    }

    /* Score & grade badges */
    .score-pill {
        display: inline-block;
        min-width: 70px;
        text-align: center;
        padding: 0.45rem 0.9rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 1.05rem;
        color: white;
    }

    /* Confirmation chips */
    .confirm-chip {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.65rem;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    .confirm-chip.done    { background: #d1fae5; color: #065f46; }
    .confirm-chip.pending { background: #f3f4f6; color: #9ca3af; }

    /* Action buttons */
    .btn-action {
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.45rem 0.9rem;
        transition: all 0.2s;
    }
    .btn-action:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

    /* Empty state */
    .empty-state { text-align: center; padding: 4rem 1rem; color: #9aa0b2; }
    .empty-state i { font-size: 3.5rem; opacity: 0.3; display: block; margin-bottom: 1rem; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- Page Header -->
    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center py-3">
            <div>
                <h1 class="h3 fw-bold mb-1 text-primary">
                    <i class="bi bi-clipboard-check me-2"></i>ประเมินและให้คะแนนโครงงาน
                </h1>
                <p class="text-muted mb-0 small">
                    <i class="bi bi-info-circle me-1"></i>ดูคะแนนและสถานะการประเมินของโครงงานทั้งหมด
                </p>
            </div>
            <a href="{{ route('coordinator.dashboard') }}" class="btn btn-outline-primary btn-action">
                <i class="bi bi-arrow-left me-1"></i>กลับ Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Project List -->
    @forelse($projects as $project)
        @php
            $evaluationsCount = $project->evaluations->unique('evaluator_code')->count();
            $expectedCount    = (int)($project->advisor_code)    + (int)($project->committee1_code)
                              + (int)($project->committee2_code) + (int)($project->committee3_code);
            $isComplete  = $expectedCount > 0 && $evaluationsCount >= $expectedCount;
            $percentage  = $expectedCount > 0 ? ($evaluationsCount / $expectedCount) * 100 : 0;
            $grade       = $project->grade;
            $allConfirmed = $grade && $grade->all_confirmed;

            $gradeColors = ['A'=>'success','B+'=>'info','B'=>'info','C+'=>'warning','C'=>'warning','D+'=>'danger','D'=>'danger','F'=>'danger'];
            $gc = isset($grade->grade) ? ($gradeColors[$grade->grade] ?? 'secondary') : 'secondary';
        @endphp

        <div class="project-card d-flex">
            <div class="accent-bar {{ $isComplete ? 'complete' : '' }}"></div>

            <div class="flex-grow-1 p-3">
                <div class="row align-items-center g-3">

                    <!-- Project info -->
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="project-code">{{ $project->project_code }}</span>
                            @if($allConfirmed)
                                <span class="badge bg-success" style="font-size:0.7rem">
                                    <i class="bi bi-patch-check-fill me-1"></i>ยืนยันแล้ว
                                </span>
                            @endif
                        </div>
                        <div class="project-title">{{ $project->project_name ?? 'ยังไม่ระบุชื่อ' }}</div>
                        <div class="text-muted small">
                            <i class="bi bi-people-fill me-1"></i>
                            @foreach($project->group->members as $member)
                                {{ $member->student->firstname_std ?? '' }} {{ $member->student->lastname_std ?? '' }}@if(!$loop->last), @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Evaluations progress -->
                    <div class="col-md-2">
                        <div class="info-label"><i class="bi bi-person-check me-1"></i>การให้คะแนน</div>
                        <div class="d-flex align-items-baseline gap-1">
                            <span class="eval-fraction">{{ $evaluationsCount }}</span>
                            <span class="text-muted fw-600 fs-6">/ {{ $expectedCount }}</span>
                            @if($isComplete)
                                <i class="bi bi-check-circle-fill text-success ms-1"></i>
                            @endif
                        </div>
                        <div class="progress-bar-eval">
                            <div class="fill" style="width: {{ $percentage }}%"></div>
                        </div>
                        <small class="text-muted">{{ number_format($percentage, 0) }}% เสร็จสิ้น</small>
                    </div>

                    <!-- Score -->
                    <div class="col-md-2 text-center">
                        <div class="info-label"><i class="bi bi-bar-chart-fill me-1"></i>คะแนนรวม</div>
                        @if($grade && $grade->final_score !== null)
                            <div class="score-pill bg-primary">
                                {{ number_format($grade->final_score, 1) }}
                            </div>
                            <div class="text-muted small mt-1">/ 100</div>
                        @else
                            <div class="score-pill bg-secondary">—</div>
                        @endif
                    </div>

                    <!-- Grade -->
                    <div class="col-md-1 text-center">
                        <div class="info-label"><i class="bi bi-award-fill me-1"></i>เกรด</div>
                        @if($grade && $grade->grade)
                            <div class="score-pill bg-{{ $gc }}">{{ $grade->grade }}</div>
                        @else
                            <div class="score-pill bg-secondary">—</div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="col-md-3 d-flex flex-column gap-2 align-items-end">
                        <a href="{{ route('coordinator.evaluations.scores', $project->project_id) }}"
                           class="btn btn-sm btn-primary btn-action w-100">
                            <i class="bi bi-clipboard-data me-1"></i>ดูคะแนน
                        </a>
                        @if($grade)
                            <a href="{{ route('coordinator.evaluations.grades', $project->project_id) }}"
                               class="btn btn-sm btn-success btn-action w-100">
                                <i class="bi bi-award me-1"></i>ดูเกรด
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Confirmation status -->
                @if($grade)
                    <div class="border-top mt-3 pt-2 d-flex align-items-center gap-2 flex-wrap">
                        <span class="text-muted small me-1">
                            <i class="bi bi-patch-check me-1"></i>ยืนยันเกรด:
                        </span>
                        @if($project->advisor_code)
                            <span class="confirm-chip {{ $grade->advisor_confirmed ? 'done' : 'pending' }}">
                                <i class="bi bi-{{ $grade->advisor_confirmed ? 'check-circle-fill' : 'circle' }}"></i>
                                ที่ปรึกษา
                            </span>
                        @endif
                        @foreach(['committee1'=>'กรรมการ 1','committee2'=>'กรรมการ 2','committee3'=>'กรรมการ 3'] as $cr => $label)
                            @if($project->{$cr.'_code'})
                                <span class="confirm-chip {{ $grade->{$cr.'_confirmed'} ? 'done' : 'pending' }}">
                                    <i class="bi bi-{{ $grade->{$cr.'_confirmed'} ? 'check-circle-fill' : 'circle' }}"></i>
                                    {{ $label }}
                                </span>
                            @endif
                        @endforeach
                        @if($allConfirmed)
                            <span class="badge bg-success ms-auto">
                                <i class="bi bi-check-all me-1"></i>ยืนยันครบแล้ว
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        </div>

    @empty
        <div class="empty-state">
            <i class="bi bi-clipboard-x"></i>
            <h5 class="text-muted">ไม่พบข้อมูลโครงงาน</h5>
            <p class="text-muted small">ยังไม่มีโครงงานในระบบ</p>
        </div>
    @endforelse

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-3">
        {{ $projects->links() }}
    </div>

</div>
@endsection

@extends('layouts.app')

@section('title', 'คะแนนประเมินโครงงาน')

@push('styles')
<style>
    body {
        background-color: #f8f9fa;
    }
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
    }
    .card-header {
        font-weight: 600;
        border-radius: 12px 12px 0 0 !important;
    }
    .score-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .score-display {
        font-size: 2rem;
        font-weight: 700;
    }
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #6c757d;
    }
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="mb-4">
        <a href="{{ route('coordinator.evaluations.index') }}" class="btn btn-outline-primary mb-3">
            <i class="bi bi-arrow-left me-2"></i>กลับรายการ
        </a>
        <h1 class="h2 fw-bold">
            <i class="bi bi-clipboard-data me-2 text-primary"></i>คะแนนประเมินโครงงาน
        </h1>
    </div>

    <!-- Project Info -->
    <div class="card mb-4">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
            <h5 class="mb-0 text-white">ข้อมูลโครงงาน</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2"><strong>รหัสโครงงาน:</strong> <code class="text-primary fs-5">{{ $project->project_code }}</code></p>
                    <p class="mb-2"><strong>ชื่อโครงงาน:</strong> {{ $project->project_name ?? 'ยังไม่ระบุ' }}</p>
                    <p class="mb-0"><strong>สมาชิก:</strong> 
                        @foreach($project->group->members as $member)
                            {{ $member->student->firstname_std ?? '' }} {{ $member->student->lastname_std ?? '' }}@if(!$loop->last), @endif
                        @endforeach
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><strong>อาจารย์ที่ปรึกษา:</strong> 
                        @if($project->advisor)
                            <span class="badge bg-primary">{{ $project->advisor_code }} - {{ $project->advisor->firstname_user }} {{ $project->advisor->lastname_user }}</span>
                        @else
                            <span class="text-muted">ยังไม่กำหนด</span>
                        @endif
                    </p>
                    <p class="mb-2"><strong>คณะกรรมการ:</strong></p>
                    <div class="d-flex flex-wrap gap-1">
                        @if($project->committee1)
                            <span class="badge bg-success">{{ $project->committee1_code }} - {{ $project->committee1->firstname_user }}</span>
                        @endif
                        @if($project->committee2)
                            <span class="badge bg-success">{{ $project->committee2_code }} - {{ $project->committee2->firstname_user }}</span>
                        @endif
                        @if($project->committee3)
                            <span class="badge bg-success">{{ $project->committee3_code }} - {{ $project->committee3->firstname_user }}</span>
                        @endif
                        @if(!$project->committee1 && !$project->committee2 && !$project->committee3)
                            <span class="text-muted">ยังไม่กำหนด</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Evaluations -->
    <h5 class="mb-3 fw-bold">
        <i class="bi bi-people-fill me-2 text-success"></i>คะแนนจากอาจารย์และคณะกรรมการ
    </h5>

    @if($project->evaluations->count() > 0)
        <div class="row">
            @foreach($project->evaluations as $evaluation)
                <div class="col-md-6 col-lg-4">
                    <div class="score-card">
                        <!-- Evaluator Info -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="fw-bold mb-1">
                                    @if($evaluation->evaluator)
                                        {{ $evaluation->evaluator->firstname_user }} {{ $evaluation->evaluator->lastname_user }}
                                    @else
                                        {{ $evaluation->evaluator_code }}
                                    @endif
                                </h6>
                                @php
                                    $roleLabels = [
                                        'advisor' => 'อาจารย์ที่ปรึกษา',
                                        'committee1' => 'กรรมการคนที่ 1',
                                        'committee2' => 'กรรมการคนที่ 2',
                                        'committee3' => 'กรรมการคนที่ 3'
                                    ];
                                    $roleColors = [
                                        'advisor' => 'primary',
                                        'committee1' => 'success',
                                        'committee2' => 'success',
                                        'committee3' => 'success'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $roleColors[$evaluation->evaluator_role] ?? 'secondary' }}">
                                    {{ $roleLabels[$evaluation->evaluator_role] ?? $evaluation->evaluator_role }}
                                </span>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">รวม</small>
                                <div class="score-display text-primary">
                                    {{ number_format($evaluation->total_score, 2) }}
                                </div>
                            </div>
                        </div>

                        <!-- Score Breakdown -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted d-block">รูปเล่ม</small>
                                    <strong class="text-info">{{ number_format($evaluation->document_score, 2) }}</strong>
                                    <small class="text-muted">/30</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted d-block">พรีเซนต์</small>
                                    <strong class="text-warning">{{ number_format($evaluation->presentation_score, 2) }}</strong>
                                    <small class="text-muted">/70</small>
                                </div>
                            </div>
                        </div>

                        <!-- Comments -->
                        @if($evaluation->comments)
                            <div class="border-top pt-2">
                                <small class="text-muted d-block mb-1">ความเห็น:</small>
                                <p class="mb-0 small">{{ $evaluation->comments }}</p>
                            </div>
                        @endif

                        <!-- Submitted At -->
                        <div class="text-end mt-2">
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>
                                {{ $evaluation->submitted_at ? $evaluation->submitted_at->format('d/m/Y H:i') : 'ไม่ระบุเวลา' }}
                            </small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Average Score Summary -->
        @php
            $avgScore = $project->evaluations->avg('total_score');
            $avgDoc = $project->evaluations->avg('document_score');
            $avgPres = $project->evaluations->avg('presentation_score');
        @endphp
        
        <div class="card mt-4">
            <div class="card-header text-white" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
                <h5 class="mb-0 text-white">
                    <i class="bi bi-calculator me-2"></i>คะแนนเฉลี่ย
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h6 class="text-muted mb-2">รูปเล่ม</h6>
                        <div class="score-display text-info">{{ number_format($avgDoc, 2) }}</div>
                        <small class="text-muted">/30</small>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-2">พรีเซนต์</h6>
                        <div class="score-display text-warning">{{ number_format($avgPres, 2) }}</div>
                        <small class="text-muted">/70</small>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-2">รวม</h6>
                        <div class="score-display text-success">{{ number_format($avgScore, 2) }}</div>
                        <small class="text-muted">/100</small>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-2">เกรดที่คาดว่าจะได้</h6>
                        @php
                            $expectedGrade = \App\Models\ProjectGrade::calculateGrade($avgScore);
                            $gradeColors = [
                                'A' => 'success', 'B+' => 'info', 'B' => 'info',
                                'C+' => 'warning', 'C' => 'warning',
                                'D+' => 'danger', 'D' => 'danger', 'F' => 'danger'
                            ];
                            $color = $gradeColors[$expectedGrade] ?? 'secondary';
                        @endphp
                        <div class="score-display text-{{ $color }}">{{ $expectedGrade }}</div>
                        <small class="text-muted">คาดการณ์</small>
                    </div>
                </div>
            </div>
        </div>

    @else
        <div class="empty-state">
            <i class="bi bi-clipboard-x"></i>
            <h5 class="text-muted">ยังไม่มีการให้คะแนน</h5>
            <p class="text-muted">รอให้อาจารย์และคณะกรรมการให้คะแนนโครงงาน</p>
        </div>
    @endif

    <!-- Expected Evaluators -->
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-bold">
                <i class="bi bi-person-lines-fill me-2"></i>รายชื่อผู้ประเมิน
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                @if($project->advisor_code)
                    @php
                        $hasEval = $project->evaluations->where('evaluator_role', 'advisor')->where('evaluator_code', $project->advisor_code)->first();
                    @endphp
                    <div class="col-md-6 mb-2">
                        <div class="d-flex justify-content-between align-items-center p-2 rounded {{ $hasEval ? 'bg-success bg-opacity-10' : 'bg-light' }}">
                            <div>
                                <span class="badge bg-primary me-2">Advisor</span>
                                <strong>{{ $project->advisor_code }}</strong>
                                @if($project->advisor)
                                    - {{ $project->advisor->firstname_user }} {{ $project->advisor->lastname_user }}
                                @endif
                            </div>
                            @if($hasEval)
                                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            @else
                                <i class="bi bi-clock text-warning fs-5"></i>
                            @endif
                        </div>
                    </div>
                @endif

                @foreach(['committee1', 'committee2', 'committee3'] as $role)
                    @if($project->{$role.'_code'})
                        @php
                            $hasEval = $project->evaluations->where('evaluator_role', $role)->where('evaluator_code', $project->{$role.'_code'})->first();
                        @endphp
                        <div class="col-md-6 mb-2">
                            <div class="d-flex justify-content-between align-items-center p-2 rounded {{ $hasEval ? 'bg-success bg-opacity-10' : 'bg-light' }}">
                                <div>
                                    <span class="badge bg-success me-2">{{ ucfirst($role) }}</span>
                                    <strong>{{ $project->{$role.'_code'} }}</strong>
                                    @if($project->{$role})
                                        - {{ $project->{$role}->firstname_user }} {{ $project->{$role}->lastname_user }}
                                    @endif
                                </div>
                                @if($hasEval)
                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                @else
                                    <i class="bi bi-clock text-warning fs-5"></i>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

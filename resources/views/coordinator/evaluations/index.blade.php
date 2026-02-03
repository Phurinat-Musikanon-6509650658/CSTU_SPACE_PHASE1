@extends('layouts.app')

@section('title', 'ประเมินและให้คะแนนโครงงาน')

@push('styles')
<style>
    body {
        background-color: #f8f9fa;
    }
    .project-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: all 0.2s;
    }
    .project-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }
    .score-badge {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
    }
    .progress-evaluations {
        height: 8px;
        border-radius: 10px;
        background-color: #e9ecef;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1 fw-bold">
                <i class="bi bi-clipboard-check me-2 text-success"></i>ประเมินและให้คะแนนโครงงาน
            </h1>
            <p class="text-muted mb-0">ดูคะแนนและเกรดจากอาจารย์และคณะกรรมการ</p>
        </div>
        <a href="{{ route('coordinator.dashboard') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>กลับ Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Projects List -->
    <div class="row">
        @forelse($projects as $project)
            @php
                $evaluationsCount = $project->evaluations->count();
                $expectedCount = 0;
                if ($project->advisor_code) $expectedCount++;
                if ($project->committee1_code) $expectedCount++;
                if ($project->committee2_code) $expectedCount++;
                if ($project->committee3_code) $expectedCount++;
                
                $grade = $project->grade;
                $allConfirmed = $grade && $grade->all_confirmed;
            @endphp
            
            <div class="col-12">
                <div class="project-card">
                    <div class="row align-items-center">
                        <!-- Project Info -->
                        <div class="col-md-3">
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

                        <!-- Evaluations Progress -->
                        <div class="col-md-3">
                            <label class="small text-muted d-block mb-1">การให้คะแนน</label>
                            <div class="d-flex align-items-center mb-1">
                                <strong class="me-2">{{ $evaluationsCount }}/{{ $expectedCount }}</strong>
                                <small class="text-muted">คน</small>
                            </div>
                            <div class="progress progress-evaluations">
                                @php
                                    $percentage = $expectedCount > 0 ? ($evaluationsCount / $expectedCount) * 100 : 0;
                                @endphp
                                <div class="progress-bar bg-success" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>

                        <!-- Final Score & Grade -->
                        <div class="col-md-2">
                            <label class="small text-muted d-block mb-1">คะแนนรวม</label>
                            @if($grade && $grade->final_score !== null)
                                <div class="score-badge bg-primary text-white">
                                    {{ number_format($grade->final_score, 2) }}
                                </div>
                            @else
                                <div class="score-badge bg-secondary text-white">
                                    -
                                </div>
                            @endif
                        </div>

                        <div class="col-md-2">
                            <label class="small text-muted d-block mb-1">เกรด</label>
                            @if($grade && $grade->grade)
                                @php
                                    $gradeColors = [
                                        'A' => 'success', 'B+' => 'info', 'B' => 'info',
                                        'C+' => 'warning', 'C' => 'warning',
                                        'D+' => 'danger', 'D' => 'danger', 'F' => 'danger'
                                    ];
                                    $color = $gradeColors[$grade->grade] ?? 'secondary';
                                @endphp
                                <div class="score-badge bg-{{ $color }} text-white">
                                    {{ $grade->grade }}
                                </div>
                            @else
                                <div class="score-badge bg-secondary text-white">
                                    -
                                </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="col-md-2 text-end">
                            <a href="{{ route('coordinator.evaluations.scores', $project->project_id) }}" 
                               class="btn btn-sm btn-outline-primary mb-1 w-100">
                                <i class="bi bi-clipboard-data me-1"></i>คะแนน
                            </a>
                            @if($grade)
                                <a href="{{ route('coordinator.evaluations.grades', $project->project_id) }}" 
                                   class="btn btn-sm btn-outline-success w-100">
                                    <i class="bi bi-award me-1"></i>เกรด
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Confirmation Status -->
                    @if($grade)
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted d-block mb-2">สถานะการยืนยันเกรด:</small>
                            <div class="d-flex gap-2 flex-wrap">
                                @if($project->advisor_code)
                                    <span class="badge {{ $grade->advisor_confirmed ? 'bg-success' : 'bg-secondary' }}">
                                        Advisor: {{ $grade->advisor_confirmed ? '✓' : '○' }}
                                    </span>
                                @endif
                                @if($project->committee1_code)
                                    <span class="badge {{ $grade->committee1_confirmed ? 'bg-success' : 'bg-secondary' }}">
                                        Comm1: {{ $grade->committee1_confirmed ? '✓' : '○' }}
                                    </span>
                                @endif
                                @if($project->committee2_code)
                                    <span class="badge {{ $grade->committee2_confirmed ? 'bg-success' : 'bg-secondary' }}">
                                        Comm2: {{ $grade->committee2_confirmed ? '✓' : '○' }}
                                    </span>
                                @endif
                                @if($project->committee3_code)
                                    <span class="badge {{ $grade->committee3_confirmed ? 'bg-success' : 'bg-secondary' }}">
                                        Comm3: {{ $grade->committee3_confirmed ? '✓' : '○' }}
                                    </span>
                                @endif
                                
                                @if($allConfirmed)
                                    <span class="badge bg-success ms-2">
                                        <i class="bi bi-check-circle-fill me-1"></i>ยืนยันครบแล้ว
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>ไม่พบข้อมูลโครงงาน
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $projects->links() }}
    </div>
</div>
@endsection

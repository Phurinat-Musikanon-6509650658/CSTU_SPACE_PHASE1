@extends('layouts.app')

@section('title', 'ประเมินโครงงาน')

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
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1 fw-bold">
                <i class="bi bi-clipboard-check me-2 text-primary"></i>ประเมินและให้คะแนนโครงงาน
            </h1>
            <p class="text-muted mb-0">โครงงานที่คุณเป็นอาจารย์ที่ปรึกษาหรือคณะกรรมการ</p>
        </div>
        <a href="{{ route('menu') }}" class="btn btn-outline-primary">
            <i class="bi bi-house me-2"></i>กลับหน้าหลัก
        </a>
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

    <!-- Projects List -->
    <div class="row">
        @forelse($projects as $project)
            @php
                $myRole = null;
                if ($project->advisor_code === Auth::user()->user_code) $myRole = 'advisor';
                elseif ($project->committee1_code === Auth::user()->user_code) $myRole = 'committee1';
                elseif ($project->committee2_code === Auth::user()->user_code) $myRole = 'committee2';
                elseif ($project->committee3_code === Auth::user()->user_code) $myRole = 'committee3';

                $myEvaluation = $project->evaluations->first();
                $hasEvaluated = $myEvaluation !== null;

                $roleLabels = [
                    'advisor' => 'อาจารย์ที่ปรึกษา',
                    'committee1' => 'กรรมการคนที่ 1',
                    'committee2' => 'กรรมการคนที่ 2',
                    'committee3' => 'กรรมการคนที่ 3'
                ];
            @endphp
            
            <div class="col-12">
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
                            @php
                                $roleColors = [
                                    'advisor' => 'primary',
                                    'committee1' => 'success',
                                    'committee2' => 'success',
                                    'committee3' => 'success'
                                ];
                            @endphp
                            <span class="badge bg-{{ $roleColors[$myRole] ?? 'secondary' }}">
                                {{ $roleLabels[$myRole] ?? '-' }}
                            </span>
                        </div>

                        <!-- Exam DateTime -->
                        <div class="col-md-2">
                            <label class="small text-muted d-block mb-1">วันเวลาสอบ</label>
                            @if($project->exam_datetime)
                                <strong class="text-danger">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    {{ $project->exam_datetime->format('d/m/Y H:i') }}
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
                                @if($project->grade)
                                    <a href="{{ route('lecturer.evaluations.grade', $project->project_id) }}" 
                                       class="btn btn-sm btn-outline-success w-100">
                                        <i class="bi bi-award me-1"></i>ดูเกรด
                                    </a>
                                @endif
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
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>ไม่พบโครงงานที่ต้องประเมิน
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

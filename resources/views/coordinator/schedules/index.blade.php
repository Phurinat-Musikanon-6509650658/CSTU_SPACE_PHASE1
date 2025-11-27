@extends('layouts.app')

@section('title', 'จัดตารางสอบและคณะกรรมการ')

@push('styles')
<style>
    body {
        background-color: #f8f9fa;
    }
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .project-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .project-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }
    .badge-exam {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1 fw-bold">
                <i class="bi bi-calendar-check me-2 text-primary"></i>จัดตารางสอบและคณะกรรมการ
            </h1>
            <p class="text-muted mb-0">กำหนดวันเวลาสอบและมอบหมายคณะกรรมการสอบโครงงาน</p>
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

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('coordinator.schedules.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">ภาคเรียน</label>
                    <select name="semester" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <option value="1" {{ request('semester') == '1' ? 'selected' : '' }}>1</option>
                        <option value="2" {{ request('semester') == '2' ? 'selected' : '' }}>2</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">ปีการศึกษา</label>
                    <select name="year" class="form-select">
                        <option value="">ทั้งหมด</option>
                        @for($y = 2568; $y >= 2560; $y--)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">สถานะตารางสอบ</label>
                    <select name="has_exam" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <option value="1" {{ request('has_exam') == '1' ? 'selected' : '' }}>กำหนดแล้ว</option>
                        <option value="0" {{ request('has_exam') == '0' ? 'selected' : '' }}>ยังไม่กำหนด</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-2"></i>กรองข้อมูล
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Projects List -->
    <div class="row">
        @forelse($projects as $project)
            <div class="col-12">
                <div class="project-card">
                    <div class="row align-items-center">
                        <!-- Project Info -->
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-2">
                                <div class="badge bg-primary me-2" style="font-size: 1rem;">{{ sprintf('%02d', $project->group_id) }}</div>
                                <code class="text-primary fw-bold">{{ $project->project_code }}</code>
                            </div>
                            <h6 class="fw-bold mb-1">{{ $project->project_name ?? 'ยังไม่ระบุชื่อโครงงาน' }}</h6>
                            <small class="text-muted">
                                @foreach($project->group->members as $index => $member)
                                    {{ $member->student->firstname_std ?? '' }} {{ $member->student->lastname_std ?? '' }}
                                    @if(!$loop->last), @endif
                                @endforeach
                            </small>
                        </div>

                        <!-- Exam DateTime -->
                        <div class="col-md-3">
                            <label class="small text-muted d-block mb-1">วันเวลาสอบ</label>
                            @if($project->exam_datetime)
                                <div class="badge badge-exam bg-success">
                                    <i class="bi bi-calendar-check me-1"></i>
                                    {{ $project->exam_datetime->format('d/m/Y H:i น.') }}
                                </div>
                            @else
                                <div class="badge badge-exam bg-warning text-dark">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    ยังไม่กำหนด
                                </div>
                            @endif
                        </div>

                        <!-- Committee -->
                        <div class="col-md-3">
                            <label class="small text-muted d-block mb-1">คณะกรรมการ</label>
                            <div class="d-flex flex-wrap gap-1">
                                @if($project->advisor_code)
                                    <span class="badge bg-primary" title="Advisor">{{ $project->advisor_code }}</span>
                                @endif
                                @if($project->committee1_code)
                                    <span class="badge bg-success" title="Committee 1">{{ $project->committee1_code }}</span>
                                @endif
                                @if($project->committee2_code)
                                    <span class="badge bg-success" title="Committee 2">{{ $project->committee2_code }}</span>
                                @endif
                                @if($project->committee3_code)
                                    <span class="badge bg-success" title="Committee 3">{{ $project->committee3_code }}</span>
                                @endif
                                @if(!$project->advisor_code && !$project->committee1_code)
                                    <span class="text-muted small">ยังไม่มอบหมาย</span>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="col-md-2 text-end">
                            <a href="{{ route('coordinator.schedules.edit', $project->project_id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil me-1"></i>แก้ไข
                            </a>
                        </div>
                    </div>
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

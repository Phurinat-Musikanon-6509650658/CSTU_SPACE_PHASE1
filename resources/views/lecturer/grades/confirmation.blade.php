@extends('layouts.app')

@section('title', 'ยืนยันเกรด - Lecturer')

@push('styles')
<style>
    .page-header {
        background: white;
        border-radius: var(--border-radius);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-light);
    }

    .tabs-container {
        background: white;
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--shadow-light);
        margin-bottom: 2rem;
    }

    .nav-tabs {
        border-bottom: 2px solid #e9ecef;
        padding: 0 2rem;
    }

    .nav-tabs .nav-link {
        border: none;
        color: #718096;
        font-weight: 600;
        padding: 1.25rem 2rem;
        transition: var(--transition);
    }

    .nav-tabs .nav-link.active {
        color: #667eea;
        border-bottom: 3px solid #667eea;
        background: transparent;
    }

    .nav-tabs .nav-link:hover {
        color: #667eea;
    }

    .grade-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: var(--transition);
        border-left: 5px solid;
    }

    .grade-card.pending {
        border-left-color: #f6ad55;
    }

    .grade-card.confirmed {
        border-left-color: #48bb78;
    }

    .grade-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }

    .project-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 1.25rem;
    }

    .project-title {
        font-weight: 700;
        color: #2c3e50;
        font-size: 1.15rem;
        margin-bottom: 0.5rem;
    }

    .grade-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1.5rem;
        background: linear-gradient(135deg, #ffd700, #ffed4e);
        color: #2c3e50;
        box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
    }

    .score-display {
        font-size: 1rem;
        color: #718096;
        margin-left: 0.5rem;
    }

    .members-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .member-tag {
        background: #f8f9fa;
        padding: 0.4rem 1rem;
        border-radius: 50px;
        font-size: 0.9rem;
        color: #2c3e50;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .score-details {
        background: #f8f9fa;
        padding: 1.25rem;
        border-radius: 12px;
        margin-bottom: 1rem;
    }

    .score-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid #e9ecef;
    }

    .score-row:last-child {
        border-bottom: none;
        font-weight: 700;
        font-size: 1.1rem;
        color: #2c3e50;
    }

    .score-label {
        color: #718096;
    }

    .score-value {
        font-weight: 600;
        color: #2c3e50;
    }

    .confirm-btn {
        background: linear-gradient(135deg, #48bb78, #38a169);
        color: white;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 50px;
        font-weight: 600;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .confirm-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(72, 187, 120, 0.3);
        color: white;
    }

    .confirm-btn:disabled {
        background: #cbd5e0;
        cursor: not-allowed;
    }

    .confirmed-badge {
        background: linear-gradient(135deg, #48bb78, #38a169);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #718096;
    }

    .empty-state i {
        font-size: 5rem;
        opacity: 0.2;
        margin-bottom: 1.5rem;
    }

    .role-badge {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 0.4rem 0.9rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-award-fill me-2"></i>
                    ยืนยันเกรด
                </h2>
                <p class="mb-0 text-muted">ตรวจสอบและยืนยันเกรดโครงงานที่คุณมีส่วนร่วมในการประเมิน</p>
            </div>
            <a href="{{ route('lecturer.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                กลับ Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tabs -->
    <div class="tabs-container">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                    <i class="bi bi-hourglass-split me-2"></i>
                    รอยืนยัน
                    @if($projects->count() > 0)
                        <span class="badge bg-warning text-dark ms-2">{{ $projects->count() }}</span>
                    @endif
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#confirmed" type="button" role="tab">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    ยืนยันแล้ว
                </button>
            </li>
        </ul>

        <div class="tab-content p-4">
            <!-- Tab: รอยืนยัน -->
            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                @if($projects->count() > 0)
                    @foreach($projects as $project)
                        @php
                            $role = null;
                            if ($project->advisor_code === Auth::user()->user_code) $role = 'advisor';
                            elseif ($project->committee1_code === Auth::user()->user_code) $role = 'committee1';
                            elseif ($project->committee2_code === Auth::user()->user_code) $role = 'committee2';
                            elseif ($project->committee3_code === Auth::user()->user_code) $role = 'committee3';

                            $myEvaluation = $project->evaluations->first();
                        @endphp

                        <div class="grade-card pending">
                            <div class="project-header">
                                <div>
                                    <div class="project-title">
                                        <i class="bi bi-folder-fill me-2"></i>
                                        {{ $project->project_name }}
                                    </div>
                                    <span class="role-badge">
                                        @if($role === 'advisor')
                                            <i class="bi bi-person-fill-check"></i> อาจารย์ที่ปรึกษา
                                        @else
                                            <i class="bi bi-people-fill"></i> กรรมการ
                                        @endif
                                    </span>
                                </div>
                                <div class="grade-badge">
                                    {{ $project->grade->grade }}
                                    <span class="score-display">{{ number_format($project->grade->final_score, 2) }}</span>
                                </div>
                            </div>

                            <div class="members-row">
                                <i class="bi bi-people-fill text-muted me-2"></i>
                                @foreach($project->group->members as $member)
                                    <span class="member-tag">
                                        <i class="bi bi-person-fill"></i>
                                        {{ $member->student->firstname_std ?? 'N/A' }} {{ $member->student->lastname_std ?? '' }}
                                    </span>
                                @endforeach
                            </div>

                            @if($myEvaluation)
                                <div class="score-details">
                                    <div class="score-row">
                                        <span class="score-label">คะแนนเอกสาร (30):</span>
                                        <span class="score-value">{{ number_format($myEvaluation->document_score, 2) }}</span>
                                    </div>
                                    <div class="score-row">
                                        <span class="score-label">คะแนนนำเสนอ (70):</span>
                                        <span class="score-value">{{ number_format($myEvaluation->presentation_score, 2) }}</span>
                                    </div>
                                    <div class="score-row">
                                        <span class="score-label">คะแนนของคุณ (100):</span>
                                        <span class="score-value">{{ number_format($myEvaluation->total_score, 2) }}</span>
                                    </div>
                                </div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    คะแนนเฉลี่ยจากการประเมินทั้งหมด
                                </small>
                                <form action="{{ route('lecturer.evaluations.confirm', $project->project_id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="confirm-btn">
                                        <i class="bi bi-check-circle-fill"></i>
                                        ยืนยันเกรด
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach

                    <div class="mt-4">
                        {{ $projects->links() }}
                    </div>
                @else
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <h5>ไม่มีเกรดที่รอยืนยัน</h5>
                        <p class="text-muted">เกรดทั้งหมดได้รับการยืนยันเรียบร้อยแล้ว</p>
                    </div>
                @endif
            </div>

            <!-- Tab: ยืนยันแล้ว -->
            <div class="tab-pane fade" id="confirmed" role="tabpanel">
                @if($confirmedProjects->count() > 0)
                    @foreach($confirmedProjects as $project)
                        @php
                            $role = null;
                            if ($project->advisor_code === Auth::user()->user_code) $role = 'advisor';
                            elseif ($project->committee1_code === Auth::user()->user_code) $role = 'committee1';
                            elseif ($project->committee2_code === Auth::user()->user_code) $role = 'committee2';
                            elseif ($project->committee3_code === Auth::user()->user_code) $role = 'committee3';

                            $confirmedAt = null;
                            if ($role && $project->grade) {
                                $confirmedAt = $project->grade->{$role.'_confirmed_at'};
                            }
                        @endphp

                        <div class="grade-card confirmed">
                            <div class="project-header">
                                <div>
                                    <div class="project-title">
                                        <i class="bi bi-folder-fill me-2"></i>
                                        {{ $project->project_name }}
                                    </div>
                                    <span class="role-badge">
                                        @if($role === 'advisor')
                                            <i class="bi bi-person-fill-check"></i> อาจารย์ที่ปรึกษา
                                        @else
                                            <i class="bi bi-people-fill"></i> กรรมการ
                                        @endif
                                    </span>
                                </div>
                                <div class="grade-badge">
                                    {{ $project->grade->grade }}
                                    <span class="score-display">{{ number_format($project->grade->final_score, 2) }}</span>
                                </div>
                            </div>

                            <div class="members-row">
                                <i class="bi bi-people-fill text-muted me-2"></i>
                                @foreach($project->group->members as $member)
                                    <span class="member-tag">
                                        <i class="bi bi-person-fill"></i>
                                        {{ $member->student->firstname_std ?? 'N/A' }} {{ $member->student->lastname_std ?? '' }}
                                    </span>
                                @endforeach
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    ยืนยันเมื่อ: {{ $confirmedAt ? $confirmedAt->locale('th')->translatedFormat('j M Y H:i') : 'N/A' }} น.
                                </small>
                                <span class="confirmed-badge">
                                    <i class="bi bi-check-circle-fill"></i>
                                    ยืนยันแล้ว
                                </span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <h5>ยังไม่มีเกรดที่ยืนยันแล้ว</h5>
                        <p class="text-muted">รายการเกรดที่คุณยืนยันจะแสดงที่นี่</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'โครงงานของฉัน')

@push('styles')
<style>
    .page-header {
        background: white;
        border-radius: var(--border-radius);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-light);
    }

    .page-header h2 {
        color: #2c3e50;
        font-weight: 700;
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .project-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-light);
        margin-bottom: 1.5rem;
        overflow: hidden;
        transition: var(--transition);
        border-left: 5px solid;
    }

    .project-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-medium);
    }

    .project-card.approved {
        border-left-color: #48bb78;
    }

    .project-card.in-progress {
        border-left-color: #4299e1;
    }

    .project-card.submitted {
        border-left-color: #f6ad55;
    }

    .project-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f0f0f0;
    }

    .project-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }

    .project-code {
        font-size: 0.9rem;
        color: #718096;
        font-family: 'Courier New', monospace;
        background: #f8f9fa;
        padding: 0.25rem 0.75rem;
        border-radius: 6px;
        display: inline-block;
    }

    .project-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        padding: 1.25rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        margin-bottom: 1.25rem;
    }

    .project-meta-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.95rem;
        color: #4a5568;
    }

    .project-meta-item i {
        color: #667eea;
        font-size: 1.25rem;
        width: 24px;
        text-align: center;
    }

    .members-section {
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
    }

    .members-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: #718096;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .member-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.9rem;
        font-weight: 500;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
    }

    .actions-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        padding-top: 1.25rem;
        border-top: 2px solid #f0f0f0;
    }

    .action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        transition: var(--transition);
        border: none;
        text-decoration: none;
        box-shadow: var(--shadow-light);
    }

    .action-btn:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-medium);
        text-decoration: none;
    }

    .action-btn i {
        font-size: 1.25rem;
    }

    .action-btn.btn-view {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .action-btn.btn-download {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        color: white;
    }

    .action-btn.btn-evaluate {
        background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);
        color: white;
    }

    .action-btn.btn-disabled {
        background: #e9ecef;
        color: #adb5bd;
        cursor: not-allowed;
        opacity: 0.6;
    }

    .action-btn.btn-disabled:hover {
        transform: none;
        box-shadow: var(--shadow-light);
    }

    .file-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        background: linear-gradient(135deg, #d4fc79 0%, #96e6a1 100%);
        border-radius: 8px;
        margin-top: 1rem;
        font-weight: 600;
        color: #2f855a;
    }

    .exam-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%);
        border-radius: 8px;
        margin-top: 0.5rem;
        font-weight: 600;
        color: #744210;
    }

    .badge-modern {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .badge-approved {
        background: linear-gradient(45deg, #48bb78, #38a169);
        color: white;
    }

    .badge-in-progress {
        background: linear-gradient(45deg, #4299e1, #3182ce);
        color: white;
    }

    .badge-submitted {
        background: linear-gradient(45deg, #f6ad55, #ed8936);
        color: white;
    }

    .badge-late {
        background: linear-gradient(45deg, #f56565, #e53e3e);
        color: white;
    }

    .empty-state {
        background: white;
        border-radius: var(--border-radius);
        padding: 4rem 2rem;
        text-align: center;
        box-shadow: var(--shadow-light);
    }

    .empty-state i {
        font-size: 5rem;
        color: #cbd5e0;
        margin-bottom: 1.5rem;
    }

    .modern-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius);
        font-weight: 600;
        transition: var(--transition);
        border: none;
    }

    .modern-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-medium);
    }

    .modern-btn.btn-primary {
        background: var(--gradient-primary);
        color: white;
    }

    .modern-btn.btn-light {
        background: #f8f9fa;
        color: #2c3e50;
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-mini {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.25rem;
        box-shadow: var(--shadow-light);
        text-align: center;
    }

    .stat-mini-value {
        font-size: 2rem;
        font-weight: 700;
        color: #2d3748;
    }

    .stat-mini-label {
        font-size: 0.85rem;
        color: #718096;
        font-weight: 600;
        text-transform: uppercase;
    }
</style>
@endpush

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-folder-fill me-2"></i>
                    โครงงานของฉัน
                </h2>
                <p class="mb-0 opacity-75">รายการโครงงานที่คุณเป็นอาจารย์ที่ปรึกษา</p>
            </div>
            <a href="{{ route('lecturer.dashboard') }}" class="btn modern-btn btn-light">
                <i class="bi bi-arrow-left"></i>
                <span>กลับ Dashboard</span>
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-row">
        <div class="stat-mini">
            <div class="stat-mini-value" style="color: #48bb78;">{{ $projects->where('status_project', 'approved')->count() }}</div>
            <div class="stat-mini-label">อนุมัติแล้ว</div>
        </div>
        <div class="stat-mini">
            <div class="stat-mini-value" style="color: #4299e1;">{{ $projects->where('status_project', 'in_progress')->count() }}</div>
            <div class="stat-mini-label">กำลังดำเนินการ</div>
        </div>
        <div class="stat-mini">
            <div class="stat-mini-value" style="color: #f6ad55;">{{ $projects->where('status_project', 'submitted')->count() }}</div>
            <div class="stat-mini-label">ส่งเล่มแล้ว</div>
        </div>
        <div class="stat-mini">
            <div class="stat-mini-value" style="color: #2d3748;">{{ $projects->count() }}</div>
            <div class="stat-mini-label">ทั้งหมด</div>
        </div>
    </div>

    <!-- Projects List -->
    @if($projects->isEmpty())
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <h5>ยังไม่มีโครงงาน</h5>
            <p>คุณยังไม่ได้เป็นอาจารย์ที่ปรึกษาโครงงานใดๆ</p>
        </div>
    @else
        @foreach($projects as $project)
            <div class="project-card 
                @if($project->status_project === 'approved') approved
                @elseif($project->status_project === 'submitted') submitted
                @else in-progress
                @endif
            ">
                <div class="card-body p-4">
                    <!-- Project Header -->
                    <div class="project-header">
                        <div>
                            <div class="project-title">{{ $project->project_name }}</div>
                            <div class="project-code">
                                <i class="bi bi-tag-fill me-1"></i>{{ $project->project_code }}
                            </div>
                        </div>
                        <span class="badge-modern 
                            @if($project->status_project === 'approved') badge-approved
                            @elseif($project->status_project === 'submitted') badge-submitted
                            @elseif($project->status_project === 'late_submission') badge-late
                            @else badge-in-progress
                            @endif
                        ">
                            @if($project->status_project === 'approved') 
                                <i class="bi bi-check-circle"></i>อนุมัติแล้ว
                            @elseif($project->status_project === 'in_progress') 
                                <i class="bi bi-gear-fill"></i>กำลังดำเนินการ
                            @elseif($project->status_project === 'submitted') 
                                <i class="bi bi-check-circle-fill"></i>ส่งเล่มแล้ว
                            @elseif($project->status_project === 'late_submission') 
                                <i class="bi bi-exclamation-triangle"></i>ส่งเล่มล่าช้า
                            @else 
                                <i class="bi bi-question-circle"></i>{{ $project->status_project }}
                            @endif
                        </span>
                    </div>

                    <!-- Project Meta -->
                    <div class="project-meta">
                        <div class="project-meta-item">
                            <i class="bi bi-diagram-3-fill"></i>
                            <span><strong>กลุ่มที่:</strong> {{ $project->group_id }}</span>
                        </div>
                        <div class="project-meta-item">
                            <i class="bi bi-book-fill"></i>
                            <span><strong>รหัสวิชา:</strong> {{ $project->group->subject_code }}</span>
                        </div>
                        <div class="project-meta-item">
                            <i class="bi bi-people-fill"></i>
                            <span><strong>สมาชิก:</strong> {{ $project->group->members->count() }} คน</span>
                        </div>
                        <div class="project-meta-item">
                            <i class="bi bi-calendar-fill"></i>
                            <span><strong>สร้างเมื่อ:</strong> {{ $project->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>

                    <!-- Members Section -->
                    <div class="members-section">
                        <div class="members-title">
                            <i class="bi bi-people-fill"></i>
                            สมาชิกกลุ่ม
                        </div>
                        <div>
                            @foreach($project->group->members as $index => $member)
                                <span class="member-badge">
                                    <i class="bi {{ $index === 0 ? 'bi-star-fill' : 'bi-person-fill' }}"></i>
                                    {{ $member->student->full_name }}
                                    @if($index === 0)
                                        <span style="opacity: 0.8;">(หัวหน้า)</span>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <!-- Status Indicators -->
                    @if($project->submission_file)
                        <div class="file-status">
                            <i class="bi bi-file-earmark-pdf-fill" style="font-size: 1.25rem;"></i>
                            <span>มีไฟล์รายงานแล้ว - ส่งเมื่อ {{ $project->submitted_at ? $project->submitted_at->format('d/m/Y H:i') : 'N/A' }}</span>
                        </div>
                    @endif

                    @if($project->exam_datetime)
                        <div class="exam-status">
                            <i class="bi bi-calendar-event-fill" style="font-size: 1.25rem;"></i>
                            <span>กำหนดสอบ: {{ $project->exam_datetime->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="actions-section">
                        @if($project->group->latestProposal)
                            <a href="{{ route('lecturer.proposals.show', $project->group->latestProposal->proposal_id) }}" 
                               class="action-btn btn-view">
                                <i class="bi bi-file-earmark-text-fill"></i>
                                <span>ดูข้อเสนอโครงงาน</span>
                            </a>
                        @else
                            <div class="action-btn btn-disabled">
                                <i class="bi bi-file-earmark-text"></i>
                                <span>ไม่มีข้อเสนอ</span>
                            </div>
                        @endif

                        @if($project->submission_file)
                            <a href="{{ route('lecturer.submission.download', $project->project_id) }}" 
                               class="action-btn btn-download">
                                <i class="bi bi-download"></i>
                                <span>ดาวน์โหลดรายงาน</span>
                            </a>
                        @else
                            <div class="action-btn btn-disabled">
                                <i class="bi bi-inbox"></i>
                                <span>ยังไม่ส่งรายงาน</span>
                            </div>
                        @endif

                        @if($project->exam_datetime)
                            <a href="{{ route('lecturer.evaluations.form', $project->project_id) }}" 
                               class="action-btn btn-evaluate">
                                <i class="bi bi-clipboard-check-fill"></i>
                                <span>ประเมินโครงงาน</span>
                            </a>
                        @else
                            <div class="action-btn btn-disabled">
                                <i class="bi bi-calendar-x"></i>
                                <span>ยังไม่มีตารางสอบ</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection

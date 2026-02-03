@extends('layouts.app')

@section('title', 'รายละเอียดข้อเสนอ')

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

    .modern-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-light);
        margin-bottom: 1.5rem;
        overflow: hidden;
        transition: var(--transition);
    }

    .modern-card:hover {
        box-shadow: var(--shadow-medium);
    }

    .card-header-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .card-header-success {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        color: white;
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .card-header-warning {
        background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);
        color: white;
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .card-header-info {
        background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
        color: white;
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        padding: 1.5rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .info-label {
        font-size: 0.85rem;
        color: #718096;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 1.1rem;
        color: #2c3e50;
        font-weight: 600;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1rem;
    }

    .status-pending {
        background: linear-gradient(45deg, #f6ad55, #ed8936);
        color: white;
    }

    .status-approved {
        background: linear-gradient(45deg, #48bb78, #38a169);
        color: white;
    }

    .status-rejected {
        background: linear-gradient(45deg, #f56565, #e53e3e);
        color: white;
    }

    .status-in-progress {
        background: linear-gradient(45deg, #4299e1, #3182ce);
        color: white;
    }

    .member-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 12px;
        margin-bottom: 0.75rem;
        transition: var(--transition);
    }

    .member-card:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }

    .member-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        flex-shrink: 0;
    }

    .leader-avatar {
        background: linear-gradient(135deg, #f6ad55, #ed8936);
    }

    .member-avatar-regular {
        background: linear-gradient(135deg, #4299e1, #3182ce);
    }

    .file-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 2px dashed #cbd5e0;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        transition: var(--transition);
    }

    .file-card:hover {
        border-color: #667eea;
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
    }

    .file-icon {
        font-size: 3rem;
        color: #e53e3e;
        margin-bottom: 1rem;
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

    .modern-btn.btn-success {
        background: linear-gradient(135deg, #48bb78, #38a169);
        color: white;
    }

    .modern-btn.btn-danger {
        background: linear-gradient(135deg, #f56565, #e53e3e);
        color: white;
    }

    .modern-btn.btn-light {
        background: #f8f9fa;
        color: #2c3e50;
    }

    .timeline-item {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .timeline-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: white;
        font-size: 1.2rem;
    }

    .timeline-content {
        flex: 1;
        background: #f8f9fa;
        padding: 1rem 1.25rem;
        border-radius: 12px;
    }
</style>
@endpush

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-file-earmark-text-fill me-2"></i>
                    รายละเอียดข้อเสนอโครงงาน
                </h2>
                <p class="mb-0 opacity-75">ดูข้อมูลและพิจารณาข้อเสนอหัวข้อโครงงาน</p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <span class="status-badge 
                    @if($proposal->status === 'pending') status-pending
                    @elseif($proposal->status === 'approved') status-approved
                    @else status-rejected
                    @endif
                ">
                    @if($proposal->status === 'pending') 
                        <i class="bi bi-clock"></i>รอพิจารณา
                    @elseif($proposal->status === 'approved') 
                        <i class="bi bi-check-circle"></i>อนุมัติแล้ว
                    @else 
                        <i class="bi bi-x-circle"></i>ปฏิเสธแล้ว
                    @endif
                </span>
                <a href="{{ route('lecturer.proposals.index') }}" class="btn modern-btn btn-light">
                    <i class="bi bi-arrow-left"></i>
                    <span>ย้อนกลับ</span>
                </a>
            </div>
        </div>
    </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Proposal Details Card -->
            <div class="modern-card">
                <div class="card-header-gradient">
                    <i class="bi bi-lightbulb-fill me-2"></i>ข้อมูลโครงงาน
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <label class="info-label">หัวข้อโครงงานที่เสนอ</label>
                        <h4 class="info-value mb-0">{{ $proposal->proposed_title }}</h4>
                    </div>

                    @if($proposal->description)
                        <div class="mb-4">
                            <label class="info-label">รายละเอียดโครงงาน</label>
                            <p class="mb-0" style="white-space: pre-line; line-height: 1.8;">{{ $proposal->description }}</p>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>ไม่มีรายละเอียดเพิ่มเติม
                        </div>
                    @endif
                </div>
            </div>

            <!-- Group Status Card -->
            <div class="modern-card">
                <div class="card-header-info">
                    <i class="bi bi-diagram-3-fill me-2"></i>สถานะกลุ่ม
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">สถานะกลุ่ม</span>
                        <span class="info-value">
                            @if($proposal->group->status_group === 'created')
                                <span class="badge bg-success">สร้างแล้ว</span>
                            @elseif($proposal->group->status_group === 'not_created')
                                <span class="badge bg-secondary">ยังไม่สร้าง</span>
                            @elseif($proposal->group->status_group === 'member_added')
                                <span class="badge bg-info">เพิ่มสมาชิก</span>
                            @elseif($proposal->group->status_group === 'member_left')
                                <span class="badge bg-warning">สมาชิกออก</span>
                            @elseif($proposal->group->status_group === 'disbanded')
                                <span class="badge bg-danger">ยุบกลุ่ม</span>
                            @else
                                <span class="badge bg-secondary">{{ $proposal->group->status_group }}</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">กลุ่มที่</span>
                        <span class="info-value">{{ $proposal->group->group_id }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">รหัสวิชา</span>
                        <span class="info-value">{{ $proposal->group->subject_code }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">ปีการศึกษา / ภาคเรียน</span>
                        <span class="info-value">{{ $proposal->group->year }} / {{ $proposal->group->semester }}</span>
                    </div>
                </div>
            </div>

            <!-- Project Status Card -->
            @if($proposal->group->project)
            <div class="modern-card">
                <div class="card-header-success">
                    <i class="bi bi-folder-fill me-2"></i>สถานะโครงงาน
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">รหัสโครงงาน</span>
                        <span class="info-value">{{ $proposal->group->project->project_code }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">สถานะโครงงาน</span>
                        <span class="info-value">
                            @if($proposal->group->project->status_project === 'approved')
                                <span class="badge bg-success">อนุมัติแล้ว</span>
                            @elseif($proposal->group->project->status_project === 'pending')
                                <span class="badge bg-warning">รอพิจารณา</span>
                            @elseif($proposal->group->project->status_project === 'rejected')
                                <span class="badge bg-danger">ปฏิเสธ</span>
                            @elseif($proposal->group->project->status_project === 'in_progress')
                                <span class="badge bg-info">กำลังดำเนินการ</span>
                            @elseif($proposal->group->project->status_project === 'submitted')
                                <span class="badge bg-success">ส่งเล่มแล้ว</span>
                            @elseif($proposal->group->project->status_project === 'late_submission')
                                <span class="badge bg-warning">ส่งเล่มล่าช้า</span>
                            @else
                                <span class="badge bg-secondary">{{ $proposal->group->project->status_project }}</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">อาจารย์ที่ปรึกษา</span>
                        <span class="info-value">
                            @if($proposal->group->project->advisor)
                                {{ $proposal->group->project->advisor->full_name }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">วันที่สร้างโครงงาน</span>
                        <span class="info-value">{{ $proposal->group->project->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
            @endif

            <!-- PDF Report Card -->
            @if($proposal->group->project && $proposal->group->project->submission_file)
            <div class="modern-card">
                <div class="card-header-warning">
                    <i class="bi bi-file-earmark-pdf-fill me-2"></i>ไฟล์รายงานโครงงาน
                </div>
                <div class="card-body p-4">
                    <div class="file-card">
                        <i class="bi bi-file-earmark-pdf-fill file-icon"></i>
                        <h5 class="mb-2">{{ $proposal->group->project->submission_original_name ?? 'รายงานโครงงาน.pdf' }}</h5>
                        <p class="text-muted mb-3">
                            <i class="bi bi-calendar me-1"></i>
                            ส่งเมื่อ: {{ $proposal->group->project->submitted_at ? $proposal->group->project->submitted_at->format('d/m/Y H:i') : 'N/A' }}
                        </p>
                        <p class="text-muted mb-3">
                            <i class="bi bi-person me-1"></i>
                            ส่งโดย: {{ $proposal->group->project->submitted_by ?? 'N/A' }}
                        </p>
                        <a href="{{ route('lecturer.submission.download', $proposal->group->project->project_id) }}" 
                           class="btn modern-btn btn-primary">
                            <i class="bi bi-download"></i>ดาวน์โหลดไฟล์ PDF
                        </a>
                    </div>
                </div>
            </div>
            @else
            <div class="modern-card">
                <div class="card-header-warning">
                    <i class="bi bi-file-earmark-pdf-fill me-2"></i>ไฟล์รายงานโครงงาน
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info mb-0 text-center">
                        <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                        <h5 class="mt-3">ยังไม่มีการส่งรายงาน</h5>
                        <p class="mb-0 text-muted">นักศึกษายังไม่ได้ส่งไฟล์รายงานโครงงาน</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Rejection Reason (if rejected) -->
            @if($proposal->status === 'rejected' && $proposal->rejection_reason)
            <div class="modern-card">
                <div class="card-header-danger" style="background: linear-gradient(135deg, #f56565, #e53e3e);">
                    <i class="bi bi-x-circle-fill me-2"></i>เหตุผลที่ปฏิเสธ
                </div>
                <div class="card-body p-4">
                    <p class="mb-0" style="white-space: pre-line; line-height: 1.8;">{{ $proposal->rejection_reason }}</p>
                </div>
            </div>
            @endif

            <!-- Timeline -->
            <div class="modern-card">
                <div class="card-header-gradient">
                    <i class="bi bi-clock-history me-2"></i>ไทม์ไลน์
                </div>
                <div class="card-body p-4">
                    <div class="timeline-item">
                        <div class="timeline-icon" style="background: linear-gradient(135deg, #4299e1, #3182ce);">
                            <i class="bi bi-plus-circle"></i>
                        </div>
                        <div class="timeline-content">
                            <strong>เสนอหัวข้อโครงงาน</strong>
                            <p class="mb-0 text-muted small">{{ $proposal->proposed_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    @if($proposal->responded_at)
                    <div class="timeline-item">
                        <div class="timeline-icon" style="background: linear-gradient(135deg, 
                            @if($proposal->status === 'approved') #48bb78, #38a169 @else #f56565, #e53e3e @endif);">
                            <i class="bi @if($proposal->status === 'approved') bi-check-circle @else bi-x-circle @endif"></i>
                        </div>
                        <div class="timeline-content">
                            <strong>{{ $proposal->status === 'approved' ? 'อนุมัติข้อเสนอ' : 'ปฏิเสธข้อเสนอ' }}</strong>
                            <p class="mb-0 text-muted small">{{ $proposal->responded_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @endif

                    @if($proposal->group->project && $proposal->group->project->submitted_at)
                    <div class="timeline-item">
                        <div class="timeline-icon" style="background: linear-gradient(135deg, #f6ad55, #ed8936);">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </div>
                        <div class="timeline-content">
                            <strong>ส่งรายงานโครงงาน</strong>
                            <p class="mb-0 text-muted small">{{ $proposal->group->project->submitted_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Group Members Card -->
            <div class="modern-card">
                <div class="card-header-gradient">
                    <i class="bi bi-people-fill me-2"></i>สมาชิกกลุ่ม ({{ $proposal->group->members->count() }} คน)
                </div>
                <div class="card-body p-3">
                    @foreach($proposal->group->members as $index => $member)
                        <div class="member-card">
                            <div class="member-avatar {{ $index === 0 ? 'leader-avatar' : 'member-avatar-regular' }}">
                                <i class="bi {{ $index === 0 ? 'bi-star-fill' : 'bi-person-fill' }}"></i>
                            </div>
                            <div>
                                <strong class="d-block">{{ $member->student->full_name }}</strong>
                                <small class="text-muted">{{ $member->student->username_std }}</small>
                                @if($index === 0)
                                    <div class="mt-1">
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-star-fill"></i> หัวหน้ากลุ่ม
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Action Buttons Card -->
            <div class="modern-card">
                <div class="card-header-warning">
                    <i class="bi bi-gear-fill me-2"></i>การดำเนินการ
                </div>
                <div class="card-body p-3">
                    <div class="d-grid gap-2">
                        @if($proposal->status === 'pending')
                            <button type="button" 
                                    class="btn modern-btn btn-success" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#approveModal">
                                <i class="bi bi-check-circle"></i>อนุมัติข้อเสนอ
                            </button>
                            <button type="button" 
                                    class="btn modern-btn btn-danger" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#rejectModal">
                                <i class="bi bi-x-circle"></i>ปฏิเสธข้อเสนอ
                            </button>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                ข้อเสนอนี้ได้รับการตอบกลับแล้ว
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #48bb78, #38a169); color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    ยืนยันการอนุมัติ
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>คุณต้องการอนุมัติข้อเสนอ <strong>"{{ $proposal->proposed_title }}"</strong> หรือไม่?</p>
                <div class="alert alert-info mb-0">
                    <small>
                        <i class="bi bi-info-circle me-1"></i>
                        เมื่ออนุมัติแล้ว นักศึกษาจะได้รับแจ้งเตือน และข้อเสนอจะถูกนำไปสู่ขั้นตอนถัดไป
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    ยกเลิก
                </button>
                <form action="{{ route('lecturer.proposals.approve', $proposal->proposal_id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn modern-btn btn-success">
                        <i class="bi bi-check-circle"></i>ยืนยันการอนุมัติ
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #f56565, #e53e3e); color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-x-circle-fill me-2"></i>
                    ปฏิเสธข้อเสนอ
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('lecturer.proposals.reject', $proposal->proposal_id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>กรุณาระบุเหตุผลที่ปฏิเสธข้อเสนอ <strong>"{{ $proposal->proposed_title }}"</strong></p>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">
                            เหตุผล <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" 
                                  id="rejection_reason" 
                                  name="rejection_reason" 
                                  rows="4" 
                                  placeholder="เช่น หัวข้อซ้ำกับโครงงานอื่น, ขอบเขตกว้างเกินไป, ควรปรับแนวคิด..."
                                  required></textarea>
                    </div>
                    <div class="alert alert-warning mb-0">
                        <small>
                            <i class="bi bi-info-circle me-1"></i>
                            นักศึกษาจะเห็นเหตุผลนี้และได้รับแจ้งเตือนทันที
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        ยกเลิก
                    </button>
                    <button type="submit" class="btn modern-btn btn-danger">
                        <i class="bi bi-x-circle"></i>ยืนยันการปฏิเสธ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

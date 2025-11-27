@extends('layouts.app')

@section('title', 'ข้อเสนอโครงงาน')

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

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--shadow-light);
        transition: var(--transition);
        border-left: 4px solid;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-medium);
    }

    .stat-card.warning {
        border-left-color: #f6ad55;
    }

    .stat-card.success {
        border-left-color: #48bb78;
    }

    .stat-card.info {
        border-left-color: #4299e1;
    }

    .stat-card.danger {
        border-left-color: #f56565;
    }

    .stat-card-icon {
        position: absolute;
        top: 50%;
        right: 1rem;
        transform: translateY(-50%);
        font-size: 3rem;
        opacity: 0.1;
    }

    .stat-card-title {
        font-size: 0.875rem;
        color: #718096;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .stat-card-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0;
    }

    .proposal-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-light);
        margin-bottom: 1.5rem;
        overflow: hidden;
        transition: var(--transition);
        border-left: 4px solid transparent;
    }

    .proposal-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-medium);
    }

    .proposal-card.pending {
        border-left-color: #f6ad55;
    }

    .proposal-card.approved {
        border-left-color: #48bb78;
    }

    .proposal-card.rejected {
        border-left-color: #f56565;
    }

    .proposal-card.in-progress {
        border-left-color: #4299e1;
    }

    .proposal-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.75rem;
    }

    .proposal-description {
        color: #718096;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 1rem;
    }

    .proposal-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 0.75rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        margin-top: 1rem;
    }

    .proposal-meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: #4a5568;
    }

    .proposal-meta-item i {
        color: #667eea;
        font-size: 1rem;
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

    .badge-pending {
        background: linear-gradient(45deg, #f6ad55, #ed8936);
        color: white;
    }

    .badge-approved {
        background: linear-gradient(45deg, #48bb78, #38a169);
        color: white;
    }

    .badge-rejected {
        background: linear-gradient(45deg, #f56565, #e53e3e);
        color: white;
    }

    .badge-in-progress {
        background: linear-gradient(45deg, #4299e1, #3182ce);
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

    .empty-state h5 {
        color: #4a5568;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: #718096;
        margin-bottom: 0;
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

    .btn-action-group {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .btn-action-group .btn {
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .proposal-card .row {
            flex-direction: column;
        }
        
        .btn-action-group {
            flex-direction: row;
            margin-top: 1rem;
        }
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
                    <i class="bi bi-file-earmark-text-fill me-2"></i>
                    ข้อเสนอโครงงาน
                </h2>
                <p class="mb-0 opacity-75">รายการข้อเสนอหัวข้อโครงงานที่นักศึกษาส่งมาให้พิจารณา</p>
            </div>
            <a href="{{ route('lecturer.dashboard') }}" class="btn modern-btn btn-light">
                <i class="bi bi-arrow-left"></i>
                <span>กลับ Dashboard</span>
            </a>
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

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card warning">
            <i class="bi bi-clock-fill stat-card-icon"></i>
            <div class="stat-card-title">รอพิจารณา</div>
            <div class="stat-card-value">{{ $proposals->where('status', 'pending')->count() }}</div>
        </div>

        <div class="stat-card success">
            <i class="bi bi-check-circle-fill stat-card-icon"></i>
            <div class="stat-card-title">อนุมัติแล้ว</div>
            <div class="stat-card-value">{{ $proposals->where('status', 'approved')->count() }}</div>
        </div>

        <div class="stat-card info">
            <i class="bi bi-gear-fill stat-card-icon"></i>
            <div class="stat-card-title">กำลังดำเนินงาน</div>
            <div class="stat-card-value">{{ $proposals->whereIn('status', ['in_progress', 'late_submission'])->count() }}</div>
        </div>

        <div class="stat-card danger">
            <i class="bi bi-x-circle-fill stat-card-icon"></i>
            <div class="stat-card-title">ปฏิเสธแล้ว</div>
            <div class="stat-card-value">{{ $proposals->where('status', 'rejected')->count() }}</div>
        </div>
    </div>

    <!-- Proposals List -->
    @if($proposals->isEmpty())
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <h5>ยังไม่มีข้อเสนอโครงงาน</h5>
            <p>เมื่อนักศึกษาส่งข้อเสนอมา จะแสดงที่นี่</p>
        </div>
    @else
        @foreach($proposals as $proposal)
            <div class="proposal-card 
                @if($proposal->status === 'pending') pending
                @elseif($proposal->status === 'approved') approved
                @elseif($proposal->status === 'rejected') rejected
                @else in-progress
                @endif
            ">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="proposal-title">{{ $proposal->proposed_title }}</div>
                                <span class="badge-modern 
                                    @if($proposal->status === 'pending') badge-pending
                                    @elseif($proposal->status === 'approved') badge-approved
                                    @elseif($proposal->status === 'rejected') badge-rejected
                                    @else badge-in-progress
                                    @endif
                                ">
                                    @if($proposal->status === 'pending') 
                                        <i class="bi bi-clock"></i>รอพิจารณา
                                    @elseif($proposal->status === 'approved') 
                                        <i class="bi bi-check-circle"></i>อนุมัติแล้ว
                                    @elseif($proposal->status === 'in_progress') 
                                        <i class="bi bi-gear-fill"></i>กำลังดำเนินงาน
                                    @elseif($proposal->status === 'late_submission') 
                                        <i class="bi bi-exclamation-triangle"></i>ส่งเล่มล่าช้า
                                    @elseif($proposal->status === 'submitted') 
                                        <i class="bi bi-check-circle-fill"></i>ส่งเล่มแล้ว
                                    @else 
                                        <i class="bi bi-x-circle"></i>ปฏิเสธแล้ว
                                    @endif
                                </span>
                            </div>

                            @if($proposal->description)
                                <div class="proposal-description">
                                    {{ Str::limit($proposal->description, 200) }}
                                </div>
                            @endif

                            <div class="proposal-meta">
                                <div class="proposal-meta-item">
                                    <i class="bi bi-diagram-3-fill"></i>
                                    <span><strong>กลุ่มที่:</strong> {{ $proposal->group->group_id }}</span>
                                </div>
                                <div class="proposal-meta-item">
                                    <i class="bi bi-book-fill"></i>
                                    <span><strong>รหัสวิชา:</strong> {{ $proposal->group->subject_code }}</span>
                                </div>
                                <div class="proposal-meta-item">
                                    <i class="bi bi-people-fill"></i>
                                    <span><strong>สมาชิก:</strong> {{ $proposal->group->members->count() }} คน</span>
                                </div>
                                <div class="proposal-meta-item">
                                    <i class="bi bi-clock-history"></i>
                                    <span>เสนอเมื่อ {{ $proposal->proposed_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 d-flex align-items-center justify-content-end">
                            <div class="btn-action-group w-100">
                                <a href="{{ route('lecturer.proposals.show', $proposal->proposal_id) }}" 
                                   class="btn modern-btn btn-primary">
                                    <i class="bi bi-eye"></i>ดูรายละเอียด
                                </a>
                                
                                @if($proposal->status === 'pending')
                                    <button type="button" 
                                            class="btn modern-btn btn-success" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#approveModal{{ $proposal->proposal_id }}">
                                        <i class="bi bi-check-circle"></i>อนุมัติ
                                    </button>
                                    <button type="button" 
                                            class="btn modern-btn btn-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#rejectModal{{ $proposal->proposal_id }}">
                                        <i class="bi bi-x-circle"></i>ปฏิเสธ
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>

            <!-- Approve Modal -->
            <div class="modal fade" id="approveModal{{ $proposal->proposal_id }}" tabindex="-1">
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
            <div class="modal fade" id="rejectModal{{ $proposal->proposal_id }}" tabindex="-1">
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
                                    <label for="rejection_reason{{ $proposal->proposal_id }}" class="form-label">
                                        เหตุผล <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" 
                                              id="rejection_reason{{ $proposal->proposal_id }}" 
                                              name="rejection_reason" 
                                              rows="4" 
                                              placeholder="เช่น หัวข้อซ้ำกับโครงงานอื่น, ขอบเขตกว้างเกินไป..."
                                              required></textarea>
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
        @endforeach
    @endif
</div>
@endsection

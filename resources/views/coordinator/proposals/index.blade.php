@extends('layouts.app')

@section('title', 'ข้อเสนอโครงงานทั้งหมด | CSTU SPACE')

@push('styles')
<style>
    .page-header { background: white; border-radius: var(--border-radius); padding: 2rem; margin-bottom: 2rem; box-shadow: var(--shadow-light); }
    .page-header h2 { color: #2c3e50; font-weight: 700; font-size: 2rem; margin-bottom: 0.5rem; }
    .modern-card { background: white; border-radius: var(--border-radius); box-shadow: var(--shadow-light); margin-bottom: 2rem; overflow: hidden; }
    .modern-card-header { padding: 1.5rem; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-bottom: 2px solid #dee2e6; }
    .modern-card-header h4 { margin: 0; color: #2c3e50; font-weight: 600; display: flex; align-items: center; gap: 0.75rem; }
    .modern-btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; border-radius: var(--border-radius); font-weight: 600; transition: var(--transition); border: none; }
    .modern-btn.btn-light { background: #f8f9fa; color: #2c3e50; }
    .modern-btn:hover { transform: translateY(-2px); box-shadow: var(--shadow-medium); }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
    .stat-card { background: white; border-radius: var(--border-radius); padding: 1.5rem; box-shadow: var(--shadow-light); transition: var(--transition); border-left: 4px solid; position: relative; overflow: hidden; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-medium); }
    .stat-card.primary { border-left-color: #667eea; }
    .stat-card.warning { border-left-color: #f6ad55; }
    .stat-card.success { border-left-color: #48bb78; }
    .stat-card.info    { border-left-color: #4299e1; }
    .stat-card.danger  { border-left-color: #fc5c7d; }
    .stat-card-icon { position: absolute; top: 50%; right: 1.5rem; transform: translateY(-50%); font-size: 4rem; opacity: 0.1; }
    .stat-card-title { font-size: 0.875rem; color: #718096; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem; }
    .stat-card-value { font-size: 2.5rem; font-weight: 700; color: #2d3748; margin-bottom: 0; }
    .empty-state { padding: 3rem; text-align: center; color: #718096; }
    .empty-state i { font-size: 4rem; margin-bottom: 1rem; opacity: 0.3; }
    .proposal-card { background: white; border-radius: var(--border-radius); padding: 1.5rem; margin-bottom: 1rem; box-shadow: var(--shadow-light); transition: var(--transition); border-left: 4px solid #dee2e6; }
    .proposal-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-medium); }
    .proposal-card.pending  { border-left-color: #f6ad55; }
    .proposal-card.approved { border-left-color: #48bb78; }
    .proposal-card.rejected { border-left-color: #fc5c7d; }
</style>
@endpush

@section('content')
<div class="container">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-file-earmark-text-fill me-2"></i>ข้อเสนอโครงงานทั้งหมด
                </h2>
                <p class="mb-0 opacity-75">รายการข้อเสนอหัวข้อโครงงานทั้งหมดในระบบ</p>
            </div>
            <a href="{{ route('coordinator.dashboard') }}" class="btn modern-btn btn-light">
                <i class="bi bi-arrow-left"></i>
                <span>กลับ Dashboard</span>
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-card-icon"><i class="bi bi-file-earmark-text"></i></div>
            <div class="stat-card-title">ทั้งหมด</div>
            <div class="stat-card-value">{{ $proposals->count() }}</div>
        </div>
        <div class="stat-card warning">
            <div class="stat-card-icon"><i class="bi bi-clock"></i></div>
            <div class="stat-card-title">รอพิจารณา</div>
            <div class="stat-card-value">{{ $proposals->where('status', 'pending')->count() }}</div>
        </div>
        <div class="stat-card success">
            <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
            <div class="stat-card-title">อนุมัติแล้ว</div>
            <div class="stat-card-value">{{ $proposals->where('status', 'approved')->count() }}</div>
        </div>
        <div class="stat-card info">
            <div class="stat-card-icon"><i class="bi bi-gear"></i></div>
            <div class="stat-card-title">กำลังทำ</div>
            <div class="stat-card-value">{{ $proposals->whereIn('status', ['in_progress', 'late_submission'])->count() }}</div>
        </div>
        <div class="stat-card success">
            <div class="stat-card-icon"><i class="bi bi-file-earmark-check"></i></div>
            <div class="stat-card-title">ส่งเล่มแล้ว</div>
            <div class="stat-card-value">{{ $proposals->where('status', 'submitted')->count() }}</div>
        </div>
        <div class="stat-card danger">
            <div class="stat-card-icon"><i class="bi bi-x-circle"></i></div>
            <div class="stat-card-title">ปฏิเสธ</div>
            <div class="stat-card-value">{{ $proposals->where('status', 'rejected')->count() }}</div>
        </div>
    </div>

    {{-- Proposals List --}}
    @if($proposals->isEmpty())
        <div class="modern-card">
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>ยังไม่มีข้อเสนอโครงงาน</p>
                <small>เมื่อนักศึกษาส่งข้อเสนอมา จะแสดงที่นี่</small>
            </div>
        </div>
    @else
        @foreach($proposals as $proposal)
            @php
                $statusBadge = [
                    'pending'  => ['bg-warning text-dark',  'รอพิจารณา'],
                    'approved' => ['bg-success text-white', 'อนุมัติแล้ว'],
                    'rejected' => ['bg-danger text-white',  'ปฏิเสธแล้ว'],
                ][$proposal->status] ?? ['bg-secondary text-white', $proposal->status];
            @endphp
            <div class="proposal-card {{ $proposal->status }}">
                <div class="row">
                    <div class="col-md-8">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="fw-semibold mb-0">{{ $proposal->proposed_title }}</h5>
                            <span class="badge {{ $statusBadge[0] }} ms-2">{{ $statusBadge[1] }}</span>
                        </div>

                        @if($proposal->description)
                            <p class="text-muted mb-2">{{ Str::limit($proposal->description, 150) }}</p>
                        @endif

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p class="mb-1 small"><i class="bi bi-diagram-3 me-1 text-muted"></i><strong>กลุ่มที่:</strong> {{ $proposal->group->group_id }}</p>
                                <p class="mb-1 small"><i class="bi bi-book me-1 text-muted"></i><strong>รหัสวิชา:</strong> {{ $proposal->group->subject_code }}</p>
                                <p class="mb-1 small">
                                    <i class="bi bi-person-badge me-1 text-muted"></i><strong>อาจารย์ที่ปรึกษา:</strong>
                                    @if($proposal->lecturer)
                                        {{ $proposal->lecturer->firstname_user }} {{ $proposal->lecturer->lastname_user }}
                                    @else
                                        <span class="text-muted">ยังไม่ระบุ</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1 small"><i class="bi bi-people me-1 text-muted"></i><strong>สมาชิก:</strong> {{ $proposal->group->members->count() }} คน</p>
                                <p class="mb-1 small">
                                    <i class="bi bi-person me-1 text-muted"></i><strong>เสนอโดย:</strong>
                                    @if($proposal->student)
                                        {{ $proposal->student->firstname_std }} {{ $proposal->student->lastname_std }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </p>
                                <p class="mb-0 small text-muted"><i class="bi bi-clock me-1"></i>เสนอเมื่อ {{ $proposal->proposed_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 d-flex align-items-center justify-content-end">
                        <a href="{{ route('lecturer.proposals.show', $proposal->proposal_id) }}"
                           class="btn btn-primary">
                            <i class="bi bi-eye me-1"></i>ดูรายละเอียด
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

</div>
@endsection

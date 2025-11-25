@extends('layouts.app')

@section('title', 'ข้อเสนอโครงงาน')

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="page-header mb-4">
                <h1>
                    <i class="bi bi-file-earmark-text-fill me-2"></i>ข้อเสนอโครงงาน
                </h1>
                <p>รายการข้อเสนอหัวข้อโครงงานที่นักศึกษาส่งมาให้พิจารณา</p>
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
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center p-3" style="border-left: 4px solid var(--color-yellow);">
                        <h3 class="mb-0" style="color: var(--color-yellow);">
                            {{ $proposals->where('status', 'pending')->count() }}
                        </h3>
                        <small class="text-muted">รอพิจารณา</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center p-3" style="border-left: 4px solid var(--color-green);">
                        <h3 class="mb-0" style="color: var(--color-green);">
                            {{ $proposals->where('status', 'approved')->count() }}
                        </h3>
                        <small class="text-muted">อนุมัติแล้ว</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center p-3" style="border-left: 4px solid var(--color-blue);">
                        <h3 class="mb-0" style="color: var(--color-blue);">
                            {{ $proposals->whereIn('status', ['in_progress', 'late_submission'])->count() }}
                        </h3>
                        <small class="text-muted">กำลังดำเนินงาน</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center p-3" style="border-left: 4px solid var(--color-red);">
                        <h3 class="mb-0" style="color: var(--color-red);">
                            {{ $proposals->where('status', 'rejected')->count() }}
                        </h3>
                        <small class="text-muted">ปฏิเสธแล้ว</small>
                    </div>
                </div>
            </div>

            <!-- Proposals List -->
            @if($proposals->isEmpty())
                <div class="card p-5 text-center">
                    <i class="bi bi-inbox text-muted" style="font-size: 5rem;"></i>
                    <h5 class="mt-3 text-muted">ยังไม่มีข้อเสนอโครงงาน</h5>
                    <p class="text-muted mb-0">เมื่อนักศึกษาส่งข้อเสนอมา จะแสดงที่นี่</p>
                </div>
            @else
                @foreach($proposals as $proposal)
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="mb-0">{{ $proposal->proposed_title }}</h5>
                                        <span class="badge 
                                            @if($proposal->status === 'pending') bg-warning text-dark
                                            @elseif($proposal->status === 'approved') bg-success
                                            @elseif($proposal->status === 'in_progress') bg-info
                                            @elseif($proposal->status === 'late_submission') bg-warning
                                            @elseif($proposal->status === 'submitted') bg-success
                                            @else bg-danger
                                            @endif
                                        ">
                                            @if($proposal->status === 'pending') 
                                                <i class="bi bi-clock me-1"></i>รอพิจารณา
                                            @elseif($proposal->status === 'approved') 
                                                <i class="bi bi-check-circle me-1"></i>อนุมัติแล้ว
                                            @elseif($proposal->status === 'in_progress') 
                                                <i class="bi bi-gear-fill me-1"></i>กำลังดำเนินงาน
                                            @elseif($proposal->status === 'late_submission') 
                                                <i class="bi bi-exclamation-triangle me-1"></i>ส่งเล่มล่าช้า
                                            @elseif($proposal->status === 'submitted') 
                                                <i class="bi bi-check-circle-fill me-1"></i>ส่งเล่มแล้ว
                                            @else 
                                                <i class="bi bi-x-circle me-1"></i>ปฏิเสธแล้ว
                                            @endif
                                        </span>
                                    </div>

                                    @if($proposal->description)
                                        <p class="text-muted mb-2">{{ Str::limit($proposal->description, 150) }}</p>
                                    @endif

                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <p class="mb-1 small">
                                                <i class="bi bi-diagram-3 me-1"></i>
                                                <strong>กลุ่มที่:</strong> {{ $proposal->group->group_id }}
                                            </p>
                                            <p class="mb-1 small">
                                                <i class="bi bi-book me-1"></i>
                                                <strong>รหัสวิชา:</strong> {{ $proposal->group->subject_code }}
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1 small">
                                                <i class="bi bi-people me-1"></i>
                                                <strong>สมาชิก:</strong> {{ $proposal->group->members->count() }} คน
                                            </p>
                                            <p class="mb-0 small">
                                                <i class="bi bi-clock me-1"></i>
                                                เสนอเมื่อ {{ $proposal->proposed_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 d-flex align-items-center justify-content-end">
                                    <div class="d-grid gap-2 w-100">
                                        <a href="{{ route('lecturer.proposals.show', $proposal->proposal_id) }}" 
                                           class="btn btn-primary">
                                            <i class="bi bi-eye me-1"></i>ดูรายละเอียด
                                        </a>
                                        
                                        @if($proposal->status === 'pending')
                                            <button type="button" 
                                                    class="btn btn-success" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#approveModal{{ $proposal->proposal_id }}">
                                                <i class="bi bi-check-circle me-1"></i>อนุมัติ
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#rejectModal{{ $proposal->proposal_id }}">
                                                <i class="bi bi-x-circle me-1"></i>ปฏิเสธ
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Approve Modal -->
                    <div class="modal fade" id="approveModal{{ $proposal->proposal_id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        ยืนยันการอนุมัติ
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check-circle me-1"></i>ยืนยันการอนุมัติ
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
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                        ปฏิเสธข้อเสนอ
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bi bi-x-circle me-1"></i>ยืนยันการปฏิเสธ
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection

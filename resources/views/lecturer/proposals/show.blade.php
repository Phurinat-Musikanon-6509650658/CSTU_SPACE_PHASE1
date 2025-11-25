@extends('layouts.app')

@section('title', 'รายละเอียดข้อเสนอ')

@section('content')
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Page Header -->
            <div class="page-header mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1>
                            <i class="bi bi-file-earmark-text-fill me-2"></i>รายละเอียดข้อเสนอโครงงาน
                        </h1>
                        <p class="mb-0">ดูข้อมูลและพิจารณาข้อเสนอหัวข้อโครงงาน</p>
                    </div>
                    <div>
                        <span class="badge 
                            @if($proposal->status === 'pending') bg-warning
                            @elseif($proposal->status === 'approved') bg-success
                            @else bg-danger
                            @endif
                        " style="font-size: 1.2rem; padding: 0.75rem 1.5rem;">
                            @if($proposal->status === 'pending') รอพิจารณา
                            @elseif($proposal->status === 'approved') อนุมัติแล้ว
                            @else ปฏิเสธแล้ว
                            @endif
                        </span>
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

            <!-- Proposal Details -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card p-4">
                        <h5 class="mb-4" style="color: var(--color-blue);">
                            <i class="bi bi-lightbulb-fill me-2"></i>ข้อมูลโครงงาน
                        </h5>
                        
                        <div class="mb-4">
                            <label class="form-label text-muted small">หัวข้อโครงงานที่เสนอ</label>
                            <h4>{{ $proposal->proposed_title }}</h4>
                        </div>

                        @if($proposal->description)
                            <div class="mb-4">
                                <label class="form-label text-muted small">รายละเอียดโครงงาน</label>
                                <p style="white-space: pre-line;">{{ $proposal->description }}</p>
                            </div>
                        @else
                            <div class="alert alert-info mb-4">
                                <i class="bi bi-info-circle me-2"></i>
                                ไม่มีรายละเอียดเพิ่มเติม
                            </div>
                        @endif

                        @if($proposal->status === 'rejected' && $proposal->rejection_reason)
                            <div class="alert alert-danger">
                                <h6 class="alert-heading">
                                    <i class="bi bi-x-circle-fill me-2"></i>เหตุผลที่ปฏิเสธ
                                </h6>
                                <p class="mb-0" style="white-space: pre-line;">{{ $proposal->rejection_reason }}</p>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-6">
                                <div class="mb-0">
                                    <label class="form-label text-muted small">วันที่เสนอ</label>
                                    <p>{{ $proposal->proposed_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            @if($proposal->responded_at)
                                <div class="col-6">
                                    <div class="mb-0">
                                        <label class="form-label text-muted small">วันที่ตอบกลับ</label>
                                        <p>{{ $proposal->responded_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Group Info Card -->
                    <div class="card p-4 mb-3" style="background: var(--gradient-primary); color: white;">
                        <h6 class="mb-3">
                            <i class="bi bi-diagram-3-fill me-2"></i>ข้อมูลกลุ่ม
                        </h6>
                        
                        <p class="mb-2">
                            <strong>กลุ่มที่:</strong> {{ $proposal->group->group_id }}
                        </p>
                        <p class="mb-2">
                            <strong>รหัสวิชา:</strong> {{ $proposal->group->subject_code }}
                        </p>
                        <p class="mb-2">
                            <strong>ปีการศึกษา:</strong> {{ $proposal->group->year }}
                        </p>
                        <p class="mb-3 pb-3 border-bottom border-light">
                            <strong>ภาคการศึกษา:</strong> {{ $proposal->group->semester }}
                        </p>
                        
                        <h6 class="mb-3">
                            <i class="bi bi-people-fill me-2"></i>สมาชิกกลุ่ม
                        </h6>
                        
                        @foreach($proposal->group->members as $index => $member)
                            <div class="mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-white d-flex align-items-center justify-content-center me-2" 
                                         style="width: 40px; height: 40px; color: var(--color-blue);">
                                        <i class="bi {{ $index === 0 ? 'bi-star-fill' : 'bi-person-fill' }}"></i>
                                    </div>
                                    <div>
                                        <strong class="d-block">{{ $member->student->full_name }}</strong>
                                        <small>{{ $member->student->username_std }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        
                        <div class="alert alert-light text-dark mt-3 mb-0">
                            <small>
                                <i class="bi bi-star-fill me-1"></i>
                                หัวหน้ากลุ่ม: {{ $proposal->student->full_name ?? 'N/A' }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card p-4">
                        <h5 class="mb-4" style="color: var(--color-yellow);">
                            <i class="bi bi-gear-fill me-2"></i>การดำเนินการ
                        </h5>
                        
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('lecturer.proposals.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>กลับไปรายการ
                            </a>
                            
                            @if($proposal->status === 'pending')
                                <button type="button" 
                                        class="btn btn-success" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#approveModal">
                                    <i class="bi bi-check-circle me-1"></i>อนุมัติข้อเสนอ
                                </button>
                                <button type="button" 
                                        class="btn btn-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#rejectModal">
                                    <i class="bi bi-x-circle me-1"></i>ปฏิเสธข้อเสนอ
                                </button>
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
    <div class="modal fade" id="rejectModal" tabindex="-1">
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
                                นักศึกษาจะเห็นเหตุผลนี้และสามารถแก้ไขข้อเสนอส่งใหม่ได้
                            </small>
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
@endsection

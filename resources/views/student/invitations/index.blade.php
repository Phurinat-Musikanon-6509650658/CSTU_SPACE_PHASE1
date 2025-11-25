@extends('layouts.student')

@section('title', 'คำเชิญเข้าร่วมกลุ่ม')

@section('content')
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header">
                <h1>
                    <i class="bi bi-envelope-fill me-2"></i>คำเชิญเข้าร่วมกลุ่ม
                </h1>
                <p>รายการคำเชิญทั้งหมดของคุณ</p>
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

    <!-- Invitations List -->
    @if($invitations->count() > 0)
        <div class="row">
            @foreach($invitations as $invitation)
            <div class="col-lg-6 mb-4">
                <div class="card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">
                                @if($invitation->group->project)
                                    {{ $invitation->group->project->project_name }}
                                @else
                                    กลุ่มที่ {{ $invitation->group->group_id }} - {{ $invitation->group->subject_code }}
                                @endif
                            </h5>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>{{ $invitation->created_at->diffForHumans() }}
                            </small>
                        </div>
                        <span class="badge 
                            @if($invitation->status === 'pending') bg-warning
                            @elseif($invitation->status === 'accepted') bg-success
                            @else bg-danger
                            @endif
                        ">
                            @if($invitation->status === 'pending') รอตอบรับ
                            @elseif($invitation->status === 'accepted') ตอบรับแล้ว
                            @else ปฏิเสธแล้ว
                            @endif
                        </span>
                    </div>
                    
                    <div class="mb-3">
                        <p class="mb-2">
                            <strong><i class="bi bi-person-fill me-2"></i>ผู้เชิญ:</strong> 
                            {{ $invitation->inviter->full_name }}
                        </p>
                        <p class="mb-2">
                            <strong><i class="bi bi-code-square me-2"></i>รหัสโครงงาน:</strong> 
                            {{ $invitation->group->project_code }}
                        </p>
                        <p class="mb-2">
                            <strong><i class="bi bi-book me-2"></i>วิชา:</strong> 
                            {{ $invitation->group->subject_code }}
                        </p>
                        <p class="mb-2">
                            <strong><i class="bi bi-calendar3 me-2"></i>ปีการศึกษา:</strong> 
                            {{ $invitation->group->year }}/{{ $invitation->group->semester }}
                        </p>
                        
                        @if($invitation->message)
                        <div class="bg-light p-3 rounded mt-3">
                            <small class="text-muted d-block mb-1">ข้อความ:</small>
                            <p class="mb-0">{{ $invitation->message }}</p>
                        </div>
                        @endif
                        
                        @if($invitation->group->description)
                        <div class="mt-3">
                            <small class="text-muted d-block mb-1">คำอธิบายโครงงาน:</small>
                            <p class="small text-secondary mb-0">{{ Str::limit($invitation->group->description, 100) }}</p>
                        </div>
                        @endif
                    </div>
                    
                    @if($invitation->responded_at)
                        <div class="text-muted small">
                            <i class="bi bi-info-circle me-1"></i>
                            ตอบรับเมื่อ: {{ $invitation->responded_at->format('d/m/Y H:i') }}
                        </div>
                    @endif
                    
                    @if($invitation->isPending())
                        <div class="d-flex gap-2 mt-3">
                            <form action="{{ route('invitations.accept', $invitation) }}" method="POST" class="flex-fill">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100" 
                                        onclick="return confirm('ยืนยันการเข้าร่วมกลุ่มนี้?')">
                                    <i class="bi bi-check-circle me-1"></i>ตอบรับ
                                </button>
                            </form>
                            <form action="{{ route('invitations.decline', $invitation) }}" method="POST" class="flex-fill">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100"
                                        onclick="return confirm('ยืนยันการปฏิเสธคำเชิญนี้?')">
                                    <i class="bi bi-x-circle me-1"></i>ปฏิเสธ
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($invitations->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $invitations->links() }}
            </div>
        @endif
    @else
        <!-- No Invitations -->
        <div class="card p-5 text-center">
            <i class="bi bi-inbox fa-4x text-muted mb-3"></i>
            <h4 class="text-muted mb-3">ไม่มีคำเชิญ</h4>
            <p class="text-muted mb-4">คุณยังไม่มีคำเชิญเข้าร่วมกลุ่มใดๆ</p>
            <a href="{{ route('student.menu') }}" class="btn btn-primary">
                <i class="bi bi-house-fill me-1"></i>กลับหน้าหลัก
            </a>
        </div>
    @endif
@endsection

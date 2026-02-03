@extends('layouts.student')

@section('title', 'กลุ่มที่ ' . $group->group_id)

@section('content')
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Page Header -->
            <div class="page-header mb-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h1>
                            <i class="bi bi-diagram-3-fill me-2"></i>
                            @if($group->project)
                                {{ $group->project->project_name }}
                            @else
                                กลุ่มที่ {{ $group->group_id }} - {{ $group->subject_code }}
                            @endif
                        </h1>
                        <p>รายละเอียดกลุ่มโครงงาน และสมาชิก</p>
                    </div>
                    <div>
                        <span class="badge bg-primary" style="font-size: 1.2rem; padding: 0.75rem 1.5rem;">
                            <i class="bi bi-hash me-1"></i>กลุ่มที่ {{ $group->group_id }}
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

            <!-- Group Information -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card p-4">
                        <h5 class="mb-4" style="color: var(--color-blue);">
                            <i class="bi bi-info-circle-fill me-2"></i>ข้อมูลกลุ่ม
                        </h5>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted small">รหัสวิชา</label>
                            <p class="fw-bold">{{ $group->subject_code }}</p>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small">ปีการศึกษา</label>
                                    <p class="fw-bold">{{ $group->year }}</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small">ภาคการศึกษา</label>
                                    <p class="fw-bold">ภาค{{ $group->semester }}</p>
                                </div>
                            </div>
                        </div>

                        @if($group->project)
                            <div class="mb-3">
                                <label class="form-label text-muted small">ชื่อโครงงาน</label>
                                <p class="fw-bold">{{ $group->project->project_name }}</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted small">รหัสโครงงาน</label>
                                <p class="fw-bold">{{ $group->project->project_code }}</p>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>รอการอนุมัติ:</strong> กรุณารออาจารย์ที่ปรึกษาอนุมัติโครงงานของคุณ
                            </div>
                        @endif

                        <div class="mb-0">
                            <label class="form-label text-muted small">สถานะ</label>
                            <p>
                                @if($group->status_group === 'pending')
                                    <span class="badge bg-warning">
                                        <i class="bi bi-clock me-1"></i>รอการอนุมัติ
                                    </span>
                                @elseif($group->status_group === 'approved')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>อนุมัติแล้ว
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-info-circle me-1"></i>{{ $group->status_group }}
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-4" style="background: var(--gradient-primary); color: white;">
                        <h5 class="mb-4">
                            <i class="bi bi-people-fill me-2"></i>สมาชิก
                        </h5>
                        
                        <p class="mb-3">
                            <strong>จำนวนสมาชิก:</strong> {{ $group->members->count() }}/2 คน
                        </p>

                        @foreach($group->members as $member)
                            <div class="mb-3 pb-3 border-bottom border-light">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-white d-flex align-items-center justify-content-center me-3" 
                                         style="width: 50px; height: 50px; color: var(--color-blue);">
                                        <i class="bi bi-person-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <strong class="d-block">{{ $member->student->full_name }}</strong>
                                        <small>{{ $member->student->username_std }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @if($group->members->count() < 2)
                            <div class="alert alert-warning text-dark mt-3 mb-0">
                                <small>
                                    <i class="bi bi-info-circle me-1"></i>
                                    กลุ่มยังสามารถรับสมาชิกได้อีก {{ 2 - $group->members->count() }} คน
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Invitations Section -->
            @if($group->invitations->count() > 0)
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card p-4" style="background: var(--gradient-accent); color: white;">
                            <h5 class="mb-4">
                                <i class="bi bi-envelope-fill me-2"></i>คำเชิญที่ส่งออก
                            </h5>
                            
                            <div class="row">
                                @foreach($group->invitations as $invitation)
                                    <div class="col-md-6 mb-3">
                                        <div class="card text-dark">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="card-title mb-0">{{ $invitation->invitee->full_name }}</h6>
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
                                                <p class="card-text small text-muted mb-2">
                                                    {{ $invitation->invitee->username_std }}
                                                </p>
                                                <small class="text-muted">
                                                    <i class="bi bi-clock me-1"></i>
                                                    ส่งเมื่อ {{ $invitation->created_at->diffForHumans() }}
                                                </small>
                                                
                                                @if($invitation->isPending())
                                                    <form action="{{ route('invitations.cancel', $invitation) }}" method="POST" class="mt-2">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger w-100"
                                                                onclick="return confirm('ยืนยันการยกเลิกคำเชิญนี้?')">
                                                            <i class="bi bi-x-circle me-1"></i>ยกเลิกคำเชิญ
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Project Proposal Section -->
            @if($group->status_group === 'created' || $group->status_group === 'approved')
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card p-4" style="border-left: 4px solid var(--color-green);">
                            <h5 class="mb-4" style="color: var(--color-green);">
                                <i class="bi bi-lightbulb-fill me-2"></i>การเสนอหัวข้อโครงงาน
                            </h5>
                            
                            @if($group->latestProposal)
                                @php $proposal = $group->latestProposal; @endphp
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label class="form-label text-muted small">หัวข้อที่เสนอ</label>
                                            <p class="fw-bold">{{ $proposal->proposed_title }}</p>
                                        </div>
                                        
                                        @if($proposal->description)
                                            <div class="mb-3">
                                                <label class="form-label text-muted small">รายละเอียด</label>
                                                <p>{{ $proposal->description }}</p>
                                            </div>
                                        @endif
                                        
                                        <div class="mb-3">
                                            <label class="form-label text-muted small">เสนอให้</label>
                                            <p>
                                                @if($proposal->lecturer)
                                                    {{ $proposal->lecturer->firstname_user }} {{ $proposal->lecturer->lastname_user }}
                                                @else
                                                    N/A
                                                @endif
                                            </p>
                                        </div>
                                        
                                        <div class="mb-0">
                                            <label class="form-label text-muted small">สถานะข้อเสนอ</label>
                                            <p>
                                                @if($proposal->status === 'pending')
                                                    <span class="badge bg-warning text-dark" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                                        <i class="bi bi-clock me-1"></i>รอการพิจารณา
                                                    </span>
                                                @elseif($proposal->status === 'approved')
                                                    <span class="badge bg-success" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                                        <i class="bi bi-check-circle me-1"></i>อนุมัติแล้ว
                                                    </span>
                                                    <br><small class="text-success mt-2 d-block">
                                                        <i class="bi bi-info-circle me-1"></i>
                                                        อาจารย์ได้อนุมัติหัวข้อโครงงานของคุณแล้ว
                                                    </small>
                                                @elseif($proposal->status === 'in_progress')
                                                    <span class="badge bg-info" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                                        <i class="bi bi-gear-fill me-1"></i>กำลังดำเนินโครงงาน
                                                    </span>
                                                @elseif($proposal->status === 'late_submission')
                                                    <span class="badge bg-warning" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                                        <i class="bi bi-exclamation-triangle me-1"></i>ส่งเล่มล่าช้า
                                                    </span>
                                                @elseif($proposal->status === 'submitted')
                                                    <span class="badge bg-success" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                                        <i class="bi bi-check-circle-fill me-1"></i>ส่งเล่มแล้ว
                                                    </span>
                                                @elseif($proposal->status === 'rejected')
                                                    <span class="badge bg-danger" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                                        <i class="bi bi-x-circle me-1"></i>ปฏิเสธแล้ว
                                                    </span>
                                                    @if($proposal->rejection_reason)
                                                        <br><small class="text-danger mt-2 d-block">
                                                            <i class="bi bi-exclamation-circle me-1"></i>
                                                            เหตุผล: {{ $proposal->rejection_reason }}
                                                        </small>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                                        {{ $proposal->status }}
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                        
                                        @if($proposal->status === 'rejected' && $proposal->rejection_reason)
                                            <div class="alert alert-danger mt-3 mb-0">
                                                <strong><i class="bi bi-info-circle me-2"></i>เหตุผลที่ปฏิเสธ:</strong>
                                                <p class="mb-0 mt-2">{{ $proposal->rejection_reason }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <i class="bi bi-file-earmark-text text-muted" style="font-size: 5rem;"></i>
                                            <p class="text-muted small mt-2">
                                                เสนอเมื่อ {{ $proposal->proposed_at->format('d/m/Y H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                @php
                                    $firstMember = $group->members()->orderBy('groupmem_id', 'asc')->first();
                                    $isGroupLeader = $firstMember && $firstMember->username_std === auth('student')->user()->username_std;
                                @endphp
                                
                                <div class="alert alert-info mb-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    กลุ่มนี้ยังไม่ได้เสนอหัวข้อโครงงาน
                                    @if($isGroupLeader)
                                        กด "เสนอหัวข้อ" เพื่อเริ่มเสนอชื่อโครงงานต่ออาจารย์
                                    @else
                                        ให้หัวหน้ากลุ่มเป็นผู้เสนอ
                                    @endif
                                </div>
                                
                                @if($isGroupLeader)
                                    <a href="{{ route('proposals.create', $group->group_id) }}" class="btn btn-success">
                                        <i class="bi bi-lightbulb-fill me-2"></i>เสนอหัวข้อโครงงาน
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card p-4">
                        <h5 class="mb-4" style="color: var(--color-yellow);">
                            <i class="bi bi-gear-fill me-2"></i>การจัดการ
                        </h5>
                        
                        <div class="d-flex gap-2 flex-wrap">
                            @if($group->members->count() < 2)
                                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#inviteMemberModal">
                                    <i class="bi bi-person-plus-fill me-2"></i>เชิญสมาชิกเพิ่ม
                                </button>
                            @endif
                            
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#leaveGroupModal">
                                <i class="bi bi-box-arrow-left me-2"></i>ออกจากกลุ่ม
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invite Member Modal -->
    <div class="modal fade" id="inviteMemberModal" tabindex="-1" aria-labelledby="inviteMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="inviteMemberModalLabel">
                        <i class="bi bi-person-plus-fill me-2"></i>เชิญสมาชิกเข้ากลุ่ม
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('invitations.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="group_id" value="{{ $group->id }}">
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="invite_username" class="form-label">เลือกนักศึกษา</label>
                            <select class="form-select" id="invite_username" name="invite_username" required>
                                <option value="">เลือกนักศึกษาที่ต้องการเชิญ...</option>
                            </select>
                            <div class="form-text">เลือกนักศึกษาที่ยังไม่มีกลุ่ม</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">ข้อความ (ไม่บังคับ)</label>
                            <textarea class="form-control" id="message" name="message" rows="3" 
                                      placeholder="เขียนข้อความถึงคนที่คุณจะเชิญ..."></textarea>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>ยกเลิก
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send-fill me-1"></i>ส่งคำเชิญ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Leave Group Modal -->
    <div class="modal fade" id="leaveGroupModal" tabindex="-1" aria-labelledby="leaveGroupModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="leaveGroupModalLabel">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                        ยืนยันการออกจากกลุ่ม
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-people text-muted" style="font-size: 4rem;"></i>
                    <h6 class="mt-3 mb-3">คุณมั่นใจแล้วใช่ไหมว่าจะออกจากกลุ่ม?</h6>
                    <p class="text-muted mb-0">
                        การดำเนินการนี้ไม่สามารถยกเลิกได้<br>
                        หากกลุ่มไม่มีสมาชิกเหลือ กลุ่มจะถูกลบอัตโนมัติ
                    </p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>ยกเลิก
                    </button>
                    <form action="{{ route('groups.leave') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-box-arrow-left me-1"></i>ออกจากกลุ่ม
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('#invite_username').select2({
            dropdownParent: $('#inviteMemberModal'),
            placeholder: 'พิมพ์ชื่อหรือรหัสนักศึกษา...',
            allowClear: true,
            ajax: {
                url: '{{ route("groups.search-students") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term || ''
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.map(function(student) {
                            return {
                                id: student.username_std,
                                text: student.firstname_std + ' ' + student.lastname_std + ' (' + student.username_std + ')'
                            };
                        })
                    };
                },
                cache: true
            }
        });
    });
</script>
@endpush

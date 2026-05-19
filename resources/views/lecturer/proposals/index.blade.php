@extends('layouts.app')

@section('title', 'ข้อเสนอโครงงาน')

@push('styles')
<style>
    :root { --theme: #f6ad55; --theme-dark: #ed8936; --theme-rgb: 246,173,85; }
    .page-banner {
        background: linear-gradient(135deg, var(--theme) 0%, var(--theme-dark) 100%);
        border-radius: 12px;
        padding: 1.1rem 1.75rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 6px 20px rgba(var(--theme-rgb),.35);
    }
    .filter-card {
        background: #fff;
        border-radius: 10px;
        padding: .9rem 1.25rem;
        margin-bottom: 1rem;
        box-shadow: 0 1px 8px rgba(0,0,0,.06);
        border: 1px solid #f0e8d0;
        border-left: 4px solid var(--theme);
    }
    .filter-card .form-select-sm {
        border-color: #e8dcc8;
        font-size: .83rem;
    }
    .filter-card .form-select-sm:focus {
        border-color: var(--theme);
        box-shadow: 0 0 0 .2rem rgba(var(--theme-rgb),.2);
    }
    .table-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,.07);
        border: 1px solid #ede8e0;
    }
    .table-compact thead th {
        background: linear-gradient(135deg, #fff8ed 0%, #fef3dc 100%);
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #92600a;
        padding: .7rem .75rem;
        border-bottom: 2px solid #f6d98a;
        white-space: nowrap;
    }
    .table-compact tbody td {
        padding: .65rem .75rem;
        vertical-align: middle;
        font-size: .875rem;
        border-bottom: 1px solid #faf5ec;
    }
    .table-compact tbody tr:last-child td { border-bottom: none; }
    .table-compact tbody tr:hover { background: linear-gradient(90deg,#fff8ed,#fffcf5); }
    .stat-pill {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .3rem .9rem;
        border-radius: 50px;
        font-size: .8rem;
        font-weight: 600;
        border: 1.5px solid;
    }
    .stat-pill-total   { background:#f8f9fa; color:#6c757d; border-color:#dee2e6; }
    .stat-pill-pending { background:#fff8e1; color:#d97706; border-color:#f6ad55; }
    .stat-pill-ok      { background:#f0fdf4; color:#059669; border-color:#48bb78; }
    .stat-pill-prog    { background:#eff6ff; color:#2563eb; border-color:#4299e1; }
    .stat-pill-reject  { background:#fff1f2; color:#dc3545; border-color:#f56565; }
    .project-code {
        font-family: 'Courier New', monospace;
        background: linear-gradient(135deg,#fff8ed,#fdeabc);
        color: #92600a;
        padding: .2rem .55rem;
        border-radius: 5px;
        font-size: .8rem;
        font-weight: 600;
        white-space: nowrap;
        border: 1px solid #f6d98a;
    }
    .empty-row td {
        padding: 3rem !important;
        text-align: center;
        color: #bbb;
    }
    .btn-icon {
        padding: .28rem .55rem;
        font-size: .78rem;
        border-radius: 6px;
        line-height: 1;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-3">

    {{-- Banner --}}
    <div class="page-banner d-flex align-items-center justify-content-between">
        <div>
            <h5 class="fw-bold mb-0 text-white"><i class="bi bi-file-earmark-text-fill me-2"></i>ข้อเสนอโครงงาน</h5>
            <small style="color:rgba(255,255,255,.85);">รายการข้อเสนอหัวข้อโครงงานที่นักศึกษาส่งมา</small>
        </div>
        <a href="{{ route('lecturer.dashboard') }}" class="btn btn-sm"
           style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.5);">
            <i class="bi bi-arrow-left me-1"></i>กลับ
        </a>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 mb-2" role="alert">
            <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 mb-2" role="alert">
            <i class="bi bi-exclamation-circle me-1"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats pills --}}
    @php
        $total    = $proposals->count();
        $pending  = $proposals->where('status','pending')->count();
        $approved = $proposals->where('status','approved')->count();
        $inprog   = $proposals->whereIn('status',['in_progress','submitted','late_submission'])->count();
        $rejected = $proposals->where('status','rejected')->count();
    @endphp
    <div class="d-flex flex-wrap gap-2 mb-2">
        <span class="stat-pill stat-pill-total"><i class="bi bi-list-ul"></i>ทั้งหมด {{ $total }}</span>
        <span class="stat-pill stat-pill-pending"><i class="bi bi-clock-fill"></i>รอพิจารณา {{ $pending }}</span>
        <span class="stat-pill stat-pill-ok"><i class="bi bi-check-circle-fill"></i>อนุมัติ {{ $approved }}</span>
        <span class="stat-pill stat-pill-prog"><i class="bi bi-gear-fill"></i>ดำเนินการ {{ $inprog }}</span>
        <span class="stat-pill stat-pill-reject"><i class="bi bi-x-circle-fill"></i>ปฏิเสธ {{ $rejected }}</span>
    </div>

    {{-- Filter --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('lecturer.proposals.index') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label mb-1 small fw-semibold text-muted">ปีการศึกษา</label>
                <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">— ทั้งหมด —</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1 small fw-semibold text-muted">ภาคเรียน</label>
                <select name="semester" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">— ทั้งหมด —</option>
                    @foreach($semesters as $sem)
                        <option value="{{ $sem }}" {{ request('semester') == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1 small fw-semibold text-muted">รายวิชา</label>
                <select name="subject" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">— ทั้งหมด —</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s }}" {{ request('subject') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1 small fw-semibold text-muted">สถานะ</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">— ทั้งหมด —</option>
                    <option value="pending"     {{ request('status') == 'pending'     ? 'selected' : '' }}>รอพิจารณา</option>
                    <option value="approved"    {{ request('status') == 'approved'    ? 'selected' : '' }}>อนุมัติแล้ว</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>กำลังดำเนินการ</option>
                    <option value="rejected"    {{ request('status') == 'rejected'    ? 'selected' : '' }}>ปฏิเสธ</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                @if(request()->hasAny(['year','semester','subject','status']))
                    <a href="{{ route('lecturer.proposals.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-x-circle me-1"></i>ล้างตัวกรอง
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-compact mb-0">
                <thead>
                    <tr>
                        <th style="width:3%">#</th>
                        <th style="width:30%">ชื่อหัวข้อ</th>
                        <th style="width:8%">วิชา</th>
                        <th style="width:8%">ปี/เทอม</th>
                        <th style="width:18%">สมาชิก</th>
                        <th style="width:10%">เสนอเมื่อ</th>
                        <th style="width:10%">สถานะ</th>
                        <th style="width:13%" class="text-center">การดำเนิน</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proposals as $i => $proposal)
                        <tr>
                            <td class="text-muted small">{{ $i + 1 }}</td>
                            <td>
                                <div class="fw-semibold lh-sm">{{ $proposal->proposed_title }}</div>
                                @if($proposal->description)
                                    <small class="text-muted d-block mt-1">{{ Str::limit($proposal->description, 60) }}</small>
                                @endif
                            </td>
                            <td>
                                <code class="small">{{ $proposal->group->subject_code ?? '-' }}</code>
                            </td>
                            <td class="small text-muted">
                                {{ $proposal->group->year ?? '-' }}/{{ $proposal->group->semester ?? '-' }}
                            </td>
                            <td>
                                @foreach($proposal->group->members as $member)
                                    <div class="small lh-sm">
                                        <i class="bi bi-person-fill text-muted me-1" style="font-size:.7rem;"></i>{{ $member->student->firstname_std ?? '' }} {{ $member->student->lastname_std ?? '' }}
                                    </div>
                                @endforeach
                            </td>
                            <td class="small text-muted" style="white-space:nowrap;">
                                {{ $proposal->proposed_at->locale('th')->diffForHumans() }}
                            </td>
                            <td>
                                @php
                                    $badgeMap = [
                                        'pending'         => ['bg-warning text-dark', 'รอพิจารณา'],
                                        'approved'        => ['bg-success text-white', 'อนุมัติแล้ว'],
                                        'rejected'        => ['bg-danger text-white', 'ปฏิเสธ'],
                                        'in_progress'     => ['bg-primary text-white', 'ดำเนินการ'],
                                        'submitted'       => ['bg-info text-dark', 'ส่งเล่มแล้ว'],
                                        'late_submission' => ['bg-danger text-white', 'ส่งล่าช้า'],
                                    ];
                                    [$bc, $bl] = $badgeMap[$proposal->status] ?? ['bg-secondary', $proposal->status];
                                @endphp
                                <span class="badge {{ $bc }} small">{{ $bl }}</span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1 flex-wrap">
                                    <a href="{{ route('lecturer.proposals.show', $proposal->proposal_id) }}"
                                       class="btn btn-icon btn-outline-primary" title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($proposal->status === 'pending')
                                        <button class="btn btn-icon btn-success" title="อนุมัติ"
                                                data-bs-toggle="modal"
                                                data-bs-target="#approveModal{{ $proposal->proposal_id }}">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button class="btn btn-icon btn-danger" title="ปฏิเสธ"
                                                data-bs-toggle="modal"
                                                data-bs-target="#rejectModal{{ $proposal->proposal_id }}">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="8">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                ไม่มีข้อเสนอที่ตรงกับเงื่อนไข
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Approve / Reject Modals --}}
@foreach($proposals as $proposal)
    @if($proposal->status === 'pending')
        <div class="modal fade" id="approveModal{{ $proposal->proposal_id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header py-2" style="background:linear-gradient(135deg,#48bb78,#38a169);color:white;">
                        <h6 class="modal-title mb-0"><i class="bi bi-check-circle-fill me-1"></i>ยืนยันอนุมัติ</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body small">
                        อนุมัติ <strong>"{{ Str::limit($proposal->proposed_title,60) }}"</strong>?
                    </div>
                    <div class="modal-footer py-2 gap-1">
                        <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <form action="{{ route('lecturer.proposals.approve', $proposal->proposal_id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="bi bi-check-lg me-1"></i>อนุมัติ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="rejectModal{{ $proposal->proposal_id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header py-2" style="background:linear-gradient(135deg,#f56565,#e53e3e);color:white;">
                        <h6 class="modal-title mb-0"><i class="bi bi-x-circle-fill me-1"></i>ปฏิเสธข้อเสนอ</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('lecturer.proposals.reject', $proposal->proposal_id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p class="small mb-2">ระบุเหตุผลที่ปฏิเสธ <strong>"{{ Str::limit($proposal->proposed_title,60) }}"</strong></p>
                            <textarea class="form-control form-control-sm" name="rejection_reason" rows="3"
                                      placeholder="เช่น หัวข้อซ้ำ, ขอบเขตกว้างเกินไป..." required></textarea>
                        </div>
                        <div class="modal-footer py-2 gap-1">
                            <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="bi bi-x-lg me-1"></i>ยืนยันปฏิเสธ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach

@endsection

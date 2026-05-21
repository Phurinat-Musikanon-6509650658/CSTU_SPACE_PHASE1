@extends('layouts.app')

@section('title', 'ข้อเสนอโครงงาน')

@push('styles')
<style>
    .page-header { background: white; border-radius: var(--border-radius); padding: 2rem; margin-bottom: 2rem; box-shadow: var(--shadow-light); }
    .page-header h2 { color: #2c3e50; font-weight: 700; font-size: 2rem; margin-bottom: 0.5rem; }
    .modern-card { background: white; border-radius: var(--border-radius); box-shadow: var(--shadow-light); margin-bottom: 2rem; overflow: hidden; }
    .modern-card-header { padding: 1.5rem; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-bottom: 2px solid #dee2e6; }
    .modern-card-header h4 { margin: 0; color: #2c3e50; font-weight: 600; display: flex; align-items: center; gap: 0.75rem; }
    .table-modern { margin-bottom: 0; }
    .table-modern thead th { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); font-weight: 600; color: #2c3e50; border: none; padding: 1rem 0.75rem; white-space: nowrap; }
    .table-modern tbody tr { transition: var(--transition); }
    .table-modern tbody tr:hover { background-color: rgba(102, 126, 234, 0.05); }
    .table-modern td { vertical-align: middle; padding: .75rem 0.75rem; border-bottom: 1px solid #f0f0f0; }
    .modern-btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; border-radius: var(--border-radius); font-weight: 600; transition: var(--transition); border: none; }
    .modern-btn.btn-light { background: #f8f9fa; color: #2c3e50; }
    .modern-btn:hover { transform: translateY(-2px); box-shadow: var(--shadow-medium); }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
    .stat-card { background: white; border-radius: var(--border-radius); padding: 1.5rem; box-shadow: var(--shadow-light); transition: var(--transition); border-left: 4px solid; position: relative; overflow: hidden; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-medium); }
    .stat-card.primary { border-left-color: #667eea; }
    .stat-card.warning { border-left-color: #f6ad55; }
    .stat-card.success { border-left-color: #48bb78; }
    .stat-card.info    { border-left-color: #4299e1; }
    .stat-card.danger  { border-left-color: #f56565; }
    .stat-card-icon { position: absolute; top: 50%; right: 1.5rem; transform: translateY(-50%); font-size: 4rem; opacity: 0.1; }
    .stat-card-title { font-size: 0.875rem; color: #718096; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem; }
    .stat-card-value { font-size: 2.5rem; font-weight: 700; color: #2d3748; margin-bottom: 0; }
    .empty-state { padding: 3rem; text-align: center; color: #718096; }
    .empty-state i { font-size: 4rem; margin-bottom: 1rem; opacity: 0.3; }
    .btn-icon { padding: .28rem .55rem; font-size: .78rem; border-radius: 6px; line-height: 1; }
</style>
@endpush

@section('content')
<div class="container">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-file-earmark-text-fill me-2"></i>ข้อเสนอโครงงาน
                </h2>
                <p class="mb-0 opacity-75">รายการข้อเสนอหัวข้อโครงงานที่นักศึกษาส่งมา</p>
            </div>
            <a href="{{ route('lecturer.dashboard') }}" class="btn modern-btn btn-light">
                <i class="bi bi-arrow-left"></i>
                <span>กลับ Dashboard</span>
            </a>
        </div>
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

    {{-- Stats --}}
    @php
        $total    = $proposals->count();
        $pending  = $proposals->where('status','pending')->count();
        $approved = $proposals->where('status','approved')->count();
        $inprog   = $proposals->whereIn('status',['in_progress','submitted','late_submission'])->count();
        $rejected = $proposals->where('status','rejected')->count();
    @endphp
    <div class="stats-grid">
        <div class="stat-card primary">
            <i class="bi bi-list-ul stat-card-icon"></i>
            <div class="stat-card-title">ทั้งหมด</div>
            <div class="stat-card-value">{{ $total }}</div>
        </div>
        <div class="stat-card warning">
            <i class="bi bi-clock-fill stat-card-icon"></i>
            <div class="stat-card-title">รอพิจารณา</div>
            <div class="stat-card-value">{{ $pending }}</div>
        </div>
        <div class="stat-card success">
            <i class="bi bi-check-circle-fill stat-card-icon"></i>
            <div class="stat-card-title">อนุมัติแล้ว</div>
            <div class="stat-card-value">{{ $approved }}</div>
        </div>
        <div class="stat-card info">
            <i class="bi bi-gear-fill stat-card-icon"></i>
            <div class="stat-card-title">กำลังดำเนินการ</div>
            <div class="stat-card-value">{{ $inprog }}</div>
        </div>
        <div class="stat-card danger">
            <i class="bi bi-x-circle-fill stat-card-icon"></i>
            <div class="stat-card-title">ปฏิเสธ</div>
            <div class="stat-card-value">{{ $rejected }}</div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="modern-card mb-3">
        <div class="modern-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="bi bi-funnel-fill"></i>ตัวกรอง</h4>
                <span class="badge bg-primary rounded-pill">{{ $proposals->count() }} รายการ</span>
            </div>
        </div>
        <div class="p-3">
            {{-- Text search (client-side) --}}
            <div class="mb-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="propSearch" class="form-control"
                           placeholder="ค้นหา ชื่อหัวข้อ / ชื่อนักศึกษา...">
                    <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('propSearch').value='';filterPropTable()">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
            <form method="GET" action="{{ route('lecturer.proposals.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-sm-6 col-md-2">
                        <label class="form-label">ปีการศึกษา</label>
                        <select name="year" class="form-select form-select-sm">
                            <option value="">ทั้งหมด</option>
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <label class="form-label">ภาคเรียน</label>
                        <select name="semester" class="form-select form-select-sm">
                            <option value="">ทั้งหมด</option>
                            @foreach($semesters as $sem)
                                <option value="{{ $sem }}" {{ request('semester') == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <label class="form-label">รายวิชา</label>
                        <select name="subject" class="form-select form-select-sm">
                            <option value="">ทั้งหมด</option>
                            @foreach($subjects as $s)
                                <option value="{{ $s }}" {{ request('subject') == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <label class="form-label">สถานะ</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">ทั้งหมด</option>
                            <option value="pending"     {{ request('status') == 'pending'     ? 'selected' : '' }}>รอพิจารณา</option>
                            <option value="approved"    {{ request('status') == 'approved'    ? 'selected' : '' }}>อนุมัติแล้ว</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>กำลังดำเนินการ</option>
                            <option value="rejected"    {{ request('status') == 'rejected'    ? 'selected' : '' }}>ปฏิเสธ</option>
                        </select>
                    </div>
                    <div class="col-auto d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="bi bi-search me-1"></i>ค้นหา
                        </button>
                        <a href="{{ route('lecturer.proposals.index') }}" class="btn btn-outline-secondary btn-sm" title="ล้างตัวกรอง">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h4><i class="bi bi-table"></i>รายการข้อเสนอโครงงาน</h4>
        </div>
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th style="width:3%">ลำดับ</th>
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
                        <tr class="prop-row">
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
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="bi bi-inbox d-block"></i>
                                    <p class="mb-0">ไม่มีข้อเสนอที่ตรงกับเงื่อนไข</p>
                                </div>
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

@push('scripts')
<script>
function filterPropTable() {
    const q = document.getElementById('propSearch').value.toLowerCase().trim();
    const rows = document.querySelectorAll('.prop-row');
    let visible = 0;
    rows.forEach(function(row) {
        const show = !q || row.innerText.toLowerCase().includes(q);
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const badge = document.querySelector('.badge.bg-primary.rounded-pill');
    if (badge) badge.textContent = visible + ' รายการ';
}
document.getElementById('propSearch').addEventListener('input', filterPropTable);
</script>
@endpush
@endsection

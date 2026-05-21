@extends('layouts.app')

@section('title', 'จัดการตารางสอบ | CSTU SPACE')

@push('styles')
<style>
    body { background-color: #f8f9fa; }
    .page-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,.08);
    }
    .badge-today    { background: #ffc107; color: #212529; }
    .badge-upcoming { background: #198754; color: #fff; }
    .badge-past     { background: #6c757d; color: #fff; }
    .table th { font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; }
    .table td { vertical-align: middle; }
    .time-block .date  { font-weight: 600; font-size: .9rem; }
    .time-block .time  { font-size: .8rem; color: #6c757d; }
    .project-title     { font-weight: 600; font-size: .9rem; }
    .project-code      { font-size: .75rem; color: #6c757d; }
    .committee-chip {
        display: inline-block;
        background: #e7f0ff;
        color: #0d47a1;
        border-radius: 4px;
        font-size: .72rem;
        padding: 1px 6px;
        margin: 1px;
        white-space: nowrap;
    }

    /* Toast */
    #toast-container { position: fixed; top: 1.25rem; right: 1.25rem; z-index: 9999; min-width: 280px; }
    .toast { border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,.15); }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-2">

    {{-- Toast container --}}
    <div id="toast-container"></div>

    {{-- Page Header --}}
    <div style="background:white;border-radius:var(--border-radius);padding:2rem;margin-bottom:2rem;box-shadow:var(--shadow-light);">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 style="color:#2c3e50;font-weight:700;font-size:2rem;margin-bottom:.5rem;">
                    <i class="bi bi-calendar-event-fill me-2"></i>จัดการตารางสอบ
                </h2>
                <p class="mb-0 opacity-75">กำหนดและติดตามตารางสอบโครงงาน</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('coordinator.exam-schedules.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>เพิ่มตารางสอบ
                </a>
                <a href="{{ route('coordinator.exam-schedules.calendar') }}" class="btn btn-outline-success">
                    <i class="bi bi-calendar3 me-1"></i>ปฏิทิน
                </a>
                <a href="{{ route('coordinator.dashboard') }}" class="btn" style="background:#f8f9fa;color:#2c3e50;font-weight:600;display:inline-flex;align-items:center;gap:.5rem;border-radius:var(--border-radius);">
                    <i class="bi bi-arrow-left"></i><span>กลับ Dashboard</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @foreach(['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $type)
        @if(session($key))
            <div class="alert alert-{{ $type }} alert-dismissible fade show" role="alert">
                <i class="bi bi-{{ $type === 'success' ? 'check-circle' : ($type === 'warning' ? 'exclamation-circle' : 'x-circle') }} me-2"></i>
                {{ session($key) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach

    {{-- Table Card --}}
    <div class="card page-card">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-table me-2 text-primary"></i>ตารางสอบทั้งหมด
                <span class="badge bg-primary rounded-pill ms-2">{{ $examSchedules->count() }}</span>
            </h5>
            <input type="text" class="form-control form-control-sm w-auto" id="tableSearch" placeholder="ค้นหาโครงงาน..." style="min-width:200px;">
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="examTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">โครงงาน</th>
                            <th>วัน-เวลาเริ่ม</th>
                            <th>วัน-เวลาสิ้นสุด</th>
                            <th>ระยะเวลา</th>
                            <th>สถานที่</th>
                            <th>คณะกรรมการ</th>
                            <th>สถานะ</th>
                            <th class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($examSchedules as $schedule)
                            @php
                                $isToday    = $schedule->ex_start_time->isToday();
                                $isPast     = $schedule->ex_start_time->isPast() && !$isToday;
                                $isUpcoming = $schedule->ex_start_time->isFuture();
                                $duration   = $schedule->ex_start_time->diff($schedule->ex_end_time);
                                $durText    = ($duration->h + $duration->days * 24) . 'ชม.' . ($duration->i ? ' ' . $duration->i . 'น.' : '');
                            @endphp
                            <tr data-search="{{ strtolower($schedule->project->project_code . ' ' . $schedule->project->project_name) }}">
                                <td class="ps-4">
                                    <div class="project-title">{{ $schedule->project->project_name ?? 'N/A' }}</div>
                                    <div class="project-code"><i class="bi bi-hash me-1"></i>{{ $schedule->project->project_code ?? '-' }}</div>
                                </td>
                                <td>
                                    <div class="time-block">
                                        <div class="date">{{ thaiDate($schedule->ex_start_time) }}</div>
                                        <div class="time"><i class="bi bi-clock me-1"></i>{{ $schedule->ex_start_time->format('H:i') }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="time-block">
                                        <div class="date">{{ thaiDate($schedule->ex_end_time) }}</div>
                                        <div class="time"><i class="bi bi-clock me-1"></i>{{ $schedule->ex_end_time->format('H:i') }}</div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $durText }}</span></td>
                                <td>
                                    @if($schedule->location)
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $schedule->location }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $members = array_filter([
                                            $schedule->project->advisor,
                                            $schedule->project->committee1,
                                            $schedule->project->committee2,
                                            $schedule->project->committee3,
                                        ]);
                                    @endphp
                                    @forelse($members as $m)
                                        <span class="committee-chip">{{ $m->user_code }}</span>
                                    @empty
                                        <span class="text-muted small">—</span>
                                    @endforelse
                                </td>
                                <td>
                                    @if($isToday)
                                        <span class="badge badge-today"><i class="bi bi-star-fill me-1"></i>วันนี้</span>
                                    @elseif($isPast)
                                        <span class="badge badge-past">สอบแล้ว</span>
                                    @else
                                        <span class="badge badge-upcoming">กำลังจะถึง</span>
                                    @endif
                                </td>
                                <td class="text-center" style="white-space:nowrap;">
                                    <a href="{{ route('coordinator.exam-schedules.edit', $schedule->ex_id) }}"
                                       class="btn btn-sm btn-outline-primary me-1">
                                        <i class="bi bi-pencil"></i> แก้ไข
                                    </a>
                                    <button class="btn btn-sm btn-outline-secondary me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#committeeModal{{ $schedule->project->project_id }}">
                                        <i class="bi bi-people"></i> คณะ
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-btn"
                                            data-id="{{ $schedule->ex_id }}"
                                            data-name="{{ $schedule->project->project_name ?? $schedule->project->project_code }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            {{-- Committee Modal --}}
                            <div class="modal fade" id="committeeModal{{ $schedule->project->project_id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header" style="background:linear-gradient(135deg,#4e73df,#224abe);color:white;">
                                            <h5 class="modal-title">
                                                <i class="bi bi-people-fill me-2"></i>คณะกรรมการ — {{ $schedule->project->project_code }}
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('coordinator.schedules.update', $schedule->project->project_id) }}" method="POST">
                                            @csrf @method('PUT')
                                            <div class="modal-body">
                                                @foreach([
                                                    ['name' => 'advisor_code',    'label' => 'อาจารย์ที่ปรึกษา',   'icon' => 'bi-star-fill text-warning',   'rel' => 'advisor'],
                                                    ['name' => 'committee1_code', 'label' => 'กรรมการที่ 1',        'icon' => 'bi-person-fill text-primary', 'rel' => 'committee1'],
                                                    ['name' => 'committee2_code', 'label' => 'กรรมการที่ 2',        'icon' => 'bi-person-fill text-info',    'rel' => 'committee2'],
                                                    ['name' => 'committee3_code', 'label' => 'กรรมการที่ 3',        'icon' => 'bi-person-fill text-success', 'rel' => 'committee3'],
                                                ] as $row)
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">
                                                            <i class="bi {{ $row['icon'] }} me-1"></i>{{ $row['label'] }}
                                                        </label>
                                                        <select name="{{ $row['name'] }}" class="form-select">
                                                            <option value="">— ไม่กำหนด —</option>
                                                            @foreach($lecturers as $l)
                                                                <option value="{{ $l->user_code }}"
                                                                    {{ $schedule->project->{$row['rel']}?->user_code === $l->user_code ? 'selected' : '' }}>
                                                                    {{ $l->user_code }} — {{ $l->firstname_user }} {{ $l->lastname_user }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>บันทึก</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="bi bi-calendar-x display-4 text-muted d-block mb-2"></i>
                                    <p class="text-muted">ยังไม่มีตารางสอบ</p>
                                    <a href="{{ route('coordinator.exam-schedules.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-circle me-1"></i>เพิ่มตารางสอบแรก
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirm Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle me-2"></i>ยืนยันการลบ</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1 small">ต้องการลบตารางสอบของ:</p>
                <p class="fw-bold" id="deleteProjectName"></p>
                <p class="text-muted small mb-0">การลบจะไม่สามารถกู้คืนได้</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-danger btn-sm" id="confirmDeleteBtn">
                    <i class="bi bi-trash me-1"></i>ลบ
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Table search
document.getElementById('tableSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#examTable tbody tr[data-search]').forEach(row => {
        row.style.display = row.dataset.search.includes(q) ? '' : 'none';
    });
});

// Delete
let deleteId = null;
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        deleteId = this.dataset.id;
        document.getElementById('deleteProjectName').textContent = this.dataset.name;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    });
});

document.getElementById('confirmDeleteBtn').addEventListener('click', async function() {
    if (!deleteId) return;
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>กำลังลบ...';
    try {
        const res = await fetch(`/coordinator/exam-schedules/${deleteId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const data = await res.json();
        bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
        showToast(data.success ? 'success' : 'danger', data.message ?? 'เกิดข้อผิดพลาด');
        if (data.success) setTimeout(() => location.reload(), 1200);
    } catch {
        showToast('danger', 'เกิดข้อผิดพลาดในการเชื่อมต่อ');
    }
});

function showToast(type, message) {
    const id = 'toast-' + Date.now();
    const icons = { success: 'check-circle-fill', danger: 'x-circle-fill', warning: 'exclamation-circle-fill' };
    const el = document.createElement('div');
    el.id = id;
    el.className = `toast align-items-center text-bg-${type} border-0 mb-2`;
    el.setAttribute('role', 'alert');
    el.innerHTML = `
        <div class="d-flex">
            <div class="toast-body"><i class="bi bi-${icons[type] ?? 'info-circle'} me-2"></i>${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>`;
    document.getElementById('toast-container').appendChild(el);
    const t = new bootstrap.Toast(el, { delay: 3500 });
    t.show();
    el.addEventListener('hidden.bs.toast', () => el.remove());
}
</script>
@endpush

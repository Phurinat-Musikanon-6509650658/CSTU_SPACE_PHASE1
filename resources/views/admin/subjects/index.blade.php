@extends('layouts.app')

@section('title', 'จัดการรายวิชา | CSTU SPACE')

@push('styles')
<style>
    .term-section {
        margin-bottom: 2.5rem;
    }
    .term-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: .75rem 1.25rem;
        border-radius: 12px 12px 0 0;
        font-weight: 700;
        font-size: 1.05rem;
        color: white;
    }
    .term-header.active   { background: linear-gradient(135deg,#2E75B6,#1F4E79); }
    .term-header.closed   { background: linear-gradient(135deg,#6c757d,#495057); }
    .term-body {
        background: white;
        border-radius: 0 0 12px 12px;
        padding: 1.25rem;
        box-shadow: 0 2px 10px rgba(0,0,0,.07);
    }
    .subject-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
        transition: transform .2s, box-shadow .2s;
        height: 100%;
    }
    .subject-card:hover { transform: translateY(-3px); box-shadow: 0 6px 16px rgba(0,0,0,.12); }
    .subject-card-head {
        padding: 1rem 1.25rem .75rem;
        border-radius: 10px 10px 0 0;
        color: white;
    }
    .subject-card-head.enabled  { background: linear-gradient(135deg,#4e73df,#224abe); }
    .subject-card-head.disabled { background: linear-gradient(135deg,#9ca3af,#6b7280); }
    .subject-card-body { padding: 1rem 1.25rem; }
    .period-pill {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        font-size: .72rem;
        padding: .2rem .55rem;
        border-radius: 20px;
        font-weight: 500;
        border: 1px solid;
    }
    .pill-open   { background:#e8f5e9; color:#1b5e20; border-color:#a5d6a7; }
    .pill-soon   { background:#fff8e1; color:#e65100; border-color:#ffcc80; }
    .pill-closed { background:#f5f5f5; color:#757575; border-color:#e0e0e0; }
    .date-row { font-size: .8rem; margin-bottom: .35rem; color: #555; }
    .date-row strong { color: #333; }
    .card-actions { display:flex; gap:.4rem; padding-top:.75rem; border-top:1px solid #f0f0f0; margin-top:.75rem; }
    .card-actions > * { flex:1; font-size:.8rem; padding:.35rem; border-radius:6px; }
    .add-subject-tile {
        border: 2px dashed #c0cfe8;
        border-radius: 10px;
        height: 100%;
        min-height: 160px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #8aa4cc;
        text-decoration: none;
        transition: all .2s;
        font-size: .9rem;
    }
    .add-subject-tile:hover { border-color: #2E75B6; color: #2E75B6; background: #f0f6ff; }
    .add-subject-tile i { font-size: 2rem; margin-bottom: .4rem; }
    .timeline-dot {
        width: 14px; height: 14px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }
    .dot-active { background: #4ade80; box-shadow: 0 0 0 3px rgba(74,222,128,.3); }
    .dot-closed { background: #9ca3af; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Header --}}
    <div class="mb-4">
        <a href="{{ route('menu') }}" class="btn btn-link text-decoration-none ps-0">
            <i class="bi bi-chevron-left me-1"></i>กลับหน้าเมนู
        </a>
        <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
            <div>
                <h1 class="h2 fw-bold mb-0">
                    <i class="bi bi-book-fill me-2 text-primary"></i>จัดการรายวิชา
                </h1>
                <p class="text-muted small mb-0">จัดระเบียบรายวิชาตามปีการศึกษาและเทอม</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newTermModal">
                <i class="bi bi-calendar-plus me-1"></i>เปิดเทอมใหม่
            </button>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Term Groups --}}
    @forelse($groups as $group)
        @php
            $isActive      = $group['is_active'];
            $year          = $group['year'];
            $semester      = $group['semester'];
            $totalGroups   = $group['total_groups'];
            $subjectCounts = $group['subject_counts'];
        @endphp
        <div class="term-section">
            <div class="term-header {{ $isActive ? 'active' : 'closed' }}">
                <span class="timeline-dot {{ $isActive ? 'dot-active' : 'dot-closed' }}"></span>
                <span>ปีการศึกษา {{ $year }} เทอม {{ $semester }}</span>
                @if($isActive)
                    <span class="badge bg-success ms-1">กำลังดำเนินการ</span>
                @else
                    <span class="badge bg-secondary ms-1">ปิดแล้ว</span>
                @endif
                <div class="ms-auto d-flex align-items-center gap-2 small fw-normal">
                    <span class="opacity-75">{{ $group['subjects']->count() }} รายวิชา</span>
                    @if($totalGroups > 0)
                    <span class="opacity-25">|</span>
                    <span class="opacity-90">
                        <i class="bi bi-people me-1"></i>รวม {{ $totalGroups }} กลุ่ม
                        @foreach($subjectCounts as $code => $cnt)
                            · {{ $code }}: {{ $cnt }}
                        @endforeach
                    </span>
                    @endif
                </div>
            </div>

            <div class="term-body">
                <div class="row g-3">
                    @foreach($group['subjects'] as $subject)
                        @php
                            $accessStatus = $subject->access_status;
                            $evalStatus   = $subject->evaluation_status;
                            $gradeStatus  = $subject->grade_edit_status;
                            $pillClass = fn($s) => match($s) {
                                'เปิดใช้งาน','เปิดอยู่' => 'pill-open',
                                'ยังไม่เปิด'            => 'pill-soon',
                                default                  => 'pill-closed',
                            };
                        @endphp
                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <div class="card subject-card border-0">
                                <div class="subject-card-head {{ $subject->is_enabled ? 'enabled' : 'disabled' }}">
                                    <div class="small opacity-75 mb-1">{{ $subject->subject_code }}</div>
                                    <div class="fw-bold" style="font-size:.95rem;">{{ $subject->subject_name }}</div>
                                </div>
                                <div class="subject-card-body">
                                    {{-- Status --}}
                                    <div class="d-flex gap-1 flex-wrap mb-2">
                                        <span class="period-pill {{ $pillClass($accessStatus) }}">
                                            <i class="bi bi-person-lock"></i>{{ $accessStatus }}
                                        </span>
                                        <span class="period-pill {{ $pillClass($evalStatus) }}">
                                            <i class="bi bi-star"></i>{{ $evalStatus }}
                                        </span>
                                        <span class="period-pill {{ $pillClass($gradeStatus) }}">
                                            <i class="bi bi-pencil"></i>{{ $gradeStatus }}
                                        </span>
                                    </div>

                                    {{-- Dates --}}
                                    @if($subject->open_date || $subject->close_date)
                                    <div class="date-row">
                                        <strong>ขอบเขต:</strong>
                                        {{ $subject->open_date?->format('d/m/Y') ?? '—' }}
                                        → {{ $subject->close_date?->format('d/m/Y') ?? '—' }}
                                    </div>
                                    @endif
                                    @if($subject->access_open_date || $subject->access_close_date)
                                    <div class="date-row">
                                        <strong>เข้าใช้:</strong>
                                        {{ $subject->access_open_date?->format('d/m/Y') ?? '—' }}
                                        → {{ $subject->access_close_date?->format('d/m/Y') ?? '—' }}
                                    </div>
                                    @endif

                                    {{-- Group count --}}
                                    @php $gc = $subjectCounts[$subject->subject_code] ?? 0; @endphp
                                    <div class="mt-2 small text-muted">
                                        <i class="bi bi-people me-1"></i>{{ $gc }} กลุ่มโครงงาน
                                    </div>

                                    {{-- Actions --}}
                                    <div class="card-actions">
                                        <a href="{{ route('admin.subjects.edit', $subject) }}"
                                           class="btn btn-outline-primary btn-sm text-center">
                                            <i class="bi bi-pencil-square me-1"></i>แก้ไข
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm {{ $subject->is_enabled ? 'btn-outline-warning' : 'btn-outline-success' }} toggle-subject"
                                                data-subject-id="{{ $subject->subject_id }}"
                                                data-current-state="{{ $subject->is_enabled ? 1 : 0 }}">
                                            <i class="bi bi-{{ $subject->is_enabled ? 'lock' : 'unlock' }} me-1"></i>
                                            {{ $subject->is_enabled ? 'ปิด' : 'เปิด' }}
                                        </button>
                                        <form action="{{ route('admin.subjects.destroy', $subject) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('ลบ {{ $subject->subject_code }} ปี {{ $year }}/{{ $semester }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Add subject tile (only for active terms) --}}
                    @if($isActive)
                    <div class="col-sm-6 col-lg-4 col-xl-3">
                        <a href="{{ route('admin.subjects.create', ['year' => $year, 'semester' => $semester]) }}"
                           class="add-subject-tile">
                            <i class="bi bi-plus-circle"></i>
                            <span>เพิ่มวิชาในเทอมนี้</span>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-book" style="font-size:3rem;opacity:.3;"></i>
            <p class="mt-3">ยังไม่มีรายวิชา — กด <strong>เปิดเทอมใหม่</strong> เพื่อเริ่มต้น</p>
        </div>
    @endforelse

</div>

{{-- Modal: เปิดเทอมใหม่ --}}
<div class="modal fade" id="newTermModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST"
              action="{{ route('admin.subjects.openNewTerm') }}">
            @csrf
            <div class="modal-header border-0"
                 style="background:linear-gradient(135deg,#2E75B6,#1F4E79);border-radius:12px 12px 0 0;">
                <h5 class="modal-title text-white fw-bold">
                    <i class="bi bi-calendar-plus me-2"></i>เปิดเทอมใหม่
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-4">
                <p class="text-muted small mb-3">
                    ระบบจะ copy รายวิชาทั้งหมดจากเทอมล่าสุด มาสร้างให้อัตโนมัติ
                    พร้อมกำหนดขอบเขตเทอมใหม่ จากนั้นแก้ไขรายละเอียดได้ภายหลัง
                </p>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">ปีการศึกษา</label>
                        <input type="number" name="year" class="form-control"
                               value="{{ ($groups->first()['year'] ?? 2568) + (($groups->first()['semester'] ?? 2) >= 2 ? 1 : 0) }}"
                               min="2560" max="2650" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">เทอม</label>
                        <select name="semester" class="form-select" required>
                            @php
                                $nextSem = (($groups->first()['semester'] ?? 2) % 2) + 1;
                            @endphp
                            <option value="1" {{ $nextSem == 1 ? 'selected' : '' }}>เทอม 1</option>
                            <option value="2" {{ $nextSem == 2 ? 'selected' : '' }}>เทอม 2</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">วันเปิดเทอม</label>
                        <input type="date" name="open_date" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">วันปิดเทอม</label>
                        <input type="date" name="close_date" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>เปิดเทอมใหม่
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.toggle-subject').forEach(btn => {
    btn.addEventListener('click', async function () {
        const id = this.dataset.subjectId;
        const res = await fetch(`/admin/subjects/${id}/toggle`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const data = await res.json();
        if (data.success) {
            // Reload to reflect state change in category header
            window.location.reload();
        }
    });
});
</script>
@endpush

@endsection

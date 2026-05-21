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
                    @php $first = $group['subjects']->first(); @endphp
                    <button type="button"
                            class="btn btn-sm btn-light btn-term-edit opacity-90"
                            style="font-size:.75rem;padding:.2rem .6rem;border-radius:6px;"
                            data-year="{{ $year }}"
                            data-semester="{{ $semester }}"
                            data-open-date="{{ $first?->open_date?->format('Y-m-d\TH:i') }}"
                            data-close-date="{{ $first?->close_date?->format('Y-m-d\TH:i') }}"
                            data-eval-open="{{ $first?->evaluation_open_date?->format('Y-m-d\TH:i') }}"
                            data-eval-close="{{ $first?->evaluation_close_date?->format('Y-m-d\TH:i') }}"
                            data-grade-open="{{ $first?->grade_edit_open_date?->format('Y-m-d\TH:i') }}"
                            data-grade-close="{{ $first?->grade_edit_close_date?->format('Y-m-d\TH:i') }}"
                            data-bs-toggle="modal" data-bs-target="#termEditModal">
                        <i class="bi bi-pencil me-1"></i>แก้ไขช่วงเวลา
                    </button>
                    <button type="button"
                            class="btn btn-sm btn-danger btn-term-delete opacity-90"
                            style="font-size:.75rem;padding:.2rem .6rem;border-radius:6px;"
                            data-year="{{ $year }}"
                            data-semester="{{ $semester }}"
                            data-count="{{ $group['subjects']->count() }}"
                            data-bs-toggle="modal" data-bs-target="#termDeleteModal">
                        <i class="bi bi-trash me-1"></i>ลบเทอมนี้
                    </button>
                </div>
            </div>

            <div class="term-body">
                <div class="row g-3">
                    @foreach($group['subjects'] as $subject)
                        @php
                            $accessStatus = $subject->access_status;
                            $evalStatus   = $subject->evaluation_status;
                            $gradeStatus  = $subject->grade_edit_status;
                            // รวม eval+grade เป็น pill เดียว: เปิดอยู่ถ้าอันใดอันหนึ่งเปิด
                            $assessStatus = (in_array($evalStatus, ['เปิดอยู่']) || in_array($gradeStatus, ['เปิดอยู่']))
                                ? 'เปิดอยู่'
                                : ((in_array($evalStatus, ['ยังไม่เปิด']) || in_array($gradeStatus, ['ยังไม่เปิด']))
                                    ? 'ยังไม่เปิด'
                                    : 'ปิดแล้ว');
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
                                            <i class="bi bi-door-open"></i>เปิดรายวิชา: {{ $accessStatus }}
                                        </span>
                                        <span class="period-pill {{ $pillClass($assessStatus) }}">
                                            <i class="bi bi-star"></i>ประเมินคะแนน: {{ $assessStatus }}
                                        </span>
                                    </div>

                                    {{-- Dates --}}
                                    @if($subject->open_date || $subject->close_date)
                                    <div class="date-row">
                                        <strong>ขอบเขต:</strong>
                                        {{ ($subject->open_date ? thaiDate($subject->open_date) : '—') }}
                                        → {{ ($subject->close_date ? thaiDate($subject->close_date) : '—') }}
                                    </div>
                                    @endif
                                    @if($subject->access_open_date || $subject->access_close_date)
                                    <div class="date-row">
                                        <strong>เข้าใช้:</strong>
                                        {{ ($subject->access_open_date ? thaiDate($subject->access_open_date) : '—') }}
                                        → {{ ($subject->access_close_date ? thaiDate($subject->access_close_date) : '—') }}
                                    </div>
                                    @endif

                                    {{-- Group count --}}
                                    @php $gc = $subjectCounts[$subject->subject_code] ?? 0; @endphp
                                    <div class="mt-2 small text-muted">
                                        <i class="bi bi-people me-1"></i>{{ $gc }} กลุ่มโครงงาน
                                    </div>

                                    {{-- Actions --}}
                                    <div class="card-actions">
                                        <button type="button"
                                                class="btn btn-outline-primary btn-sm text-center btn-quick-edit"
                                                data-subject-id="{{ $subject->subject_id }}"
                                                data-subject-code="{{ $subject->subject_code }}"
                                                data-subject-name="{{ $subject->subject_name }}"
                                                data-year="{{ $subject->year }}"
                                                data-semester="{{ $subject->semester }}"
                                                data-is-enabled="{{ $subject->is_enabled ? 1 : 0 }}"
                                                data-open-date="{{ $subject->open_date?->format('Y-m-d\TH:i') }}"
                                                data-close-date="{{ $subject->close_date?->format('Y-m-d\TH:i') }}"
                                                data-eval-open="{{ $subject->evaluation_open_date?->format('Y-m-d\TH:i') }}"
                                                data-eval-close="{{ $subject->evaluation_close_date?->format('Y-m-d\TH:i') }}"
                                                data-grade-open="{{ $subject->grade_edit_open_date?->format('Y-m-d\TH:i') }}"
                                                data-grade-close="{{ $subject->grade_edit_close_date?->format('Y-m-d\TH:i') }}"
                                                data-bs-toggle="modal" data-bs-target="#quickEditModal">
                                            <i class="bi bi-pencil-square me-1"></i>แก้ไขรายวิชา
                                        </button>
                                        <button type="button"
                                                class="btn btn-sm {{ $subject->is_enabled ? 'btn-success' : 'btn-danger' }} toggle-subject"
                                                data-subject-id="{{ $subject->subject_id }}"
                                                data-current-state="{{ $subject->is_enabled ? 1 : 0 }}">
                                            <i class="bi bi-{{ $subject->is_enabled ? 'unlock-fill' : 'lock-fill' }} me-1"></i>
                                            {{ $subject->is_enabled ? 'เปิดรายวิชา' : 'ปิดรายวิชา' }}
                                        </button>
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

{{-- Modal: ยืนยันลบเทอม --}}
<div class="modal fade" id="termDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0"
                 style="background:linear-gradient(135deg,#dc3545,#a71d2a);border-radius:12px 12px 0 0;">
                <h5 class="modal-title text-white fw-bold">
                    <i class="bi bi-exclamation-triangle me-2"></i>ยืนยันการลบเทอม
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-4 pb-2">
                <p class="mb-1">คุณต้องการลบ <strong id="td-label"></strong> ใช่หรือไม่?</p>
                <p class="text-muted small mb-3">จะลบรายวิชาทั้งหมด <strong id="td-count"></strong> รายวิชาในเทอมนี้ออกจากระบบถาวร</p>
                <div class="alert alert-danger border-0 py-2" style="font-size:.85rem;">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    การดำเนินการนี้<strong>ไม่สามารถย้อนกลับได้</strong>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก ไม่ลบ</button>
                <form id="termDeleteForm" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>ยืนยัน ลบเทอมนี้
                    </button>
                </form>
            </div>
        </div>
    </div>
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

{{-- Modal: Term Bulk Edit --}}
<div class="modal fade" id="termEditModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content" id="termEditForm" method="POST"
              action="{{ route('admin.subjects.bulkUpdateTerm') }}">
            @csrf
            <input type="hidden" name="year"     id="te-year">
            <input type="hidden" name="semester" id="te-semester">
            <div class="modal-header border-0"
                 style="background:linear-gradient(135deg,#2E75B6,#1F4E79);border-radius:12px 12px 0 0;">
                <h5 class="modal-title text-white fw-bold">
                    <i class="bi bi-calendar-range me-2"></i>แก้ไขช่วงเวลาเทอม
                    <span id="te-title" class="fw-normal ms-1 opacity-75"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3 pb-2">
                <div class="alert alert-info border-0 py-2 mb-3" style="font-size:.83rem;">
                    <i class="bi bi-info-circle me-1"></i>
                    การเปลี่ยนแปลงนี้จะ<strong>อัปเดตทุกรายวิชาในเทอมนี้พร้อมกัน</strong>
                </div>

                @foreach([
                    ['primary', 'calendar2-week', 'เปิด-ปิด รายวิชา',    'te-open',       'open_date',             'te-close',      'close_date'],
                    ['success', 'star',           'ช่วงประเมินคะแนน',     'te-eval-open',  'evaluation_open_date',  'te-eval-close', 'evaluation_close_date'],
                    ['info',    'pencil',          'ช่วงแก้ไขคะแนน',       'te-grade-open', 'grade_edit_open_date',  'te-grade-close','grade_edit_close_date'],
                ] as [$color, $icon, $label, $openId, $openName, $closeId, $closeName])
                <div class="p-2 rounded-3 mb-2" style="background:#f8f9fa;border-left:3px solid var(--bs-{{ $color }});">
                    <div class="small fw-semibold mb-2">
                        <i class="bi bi-{{ $icon }} me-1 text-{{ $color }}"></i>{{ $label }}
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small mb-1">วันเปิด</label>
                            <input type="datetime-local" class="form-control form-control-sm"
                                   id="{{ $openId }}" name="{{ $openName }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-1">วันปิด</label>
                            <input type="datetime-local" class="form-control form-control-sm"
                                   id="{{ $closeId }}" name="{{ $closeName }}">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary btn-sm px-4">
                    <i class="bi bi-check-circle me-1"></i>บันทึก (ทุกรายวิชาในเทอม)
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Quick Edit --}}
<div class="modal fade" id="quickEditModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content" id="quickEditForm" method="POST">
            @csrf @method('PUT')
            <div class="modal-header border-0"
                 style="background:linear-gradient(135deg,#4e73df,#224abe);border-radius:12px 12px 0 0;">
                <h5 class="modal-title text-white fw-bold">
                    <i class="bi bi-pencil-square me-2"></i>แก้ไขรายวิชา
                    <span id="qe-title" class="fw-normal ms-1 opacity-75"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3 pb-2">

                {{-- ข้อมูลทั่วไป --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">รหัสวิชา</label>
                        <input type="text" class="form-control form-control-sm" name="subject_code" id="qe-code" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold small">ชื่อวิชา</label>
                        <input type="text" class="form-control form-control-sm" name="subject_name" id="qe-name" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small">ปีการศึกษา</label>
                        <input type="number" class="form-control form-control-sm" name="year" id="qe-year" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small">เทอม</label>
                        <select class="form-select form-select-sm" name="semester" id="qe-semester" required>
                            <option value="1">เทอม 1</option>
                            <option value="2">เทอม 2</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="qe-enabled" name="is_enabled" value="1">
                            <label class="form-check-label small fw-semibold" for="qe-enabled">เปิดใช้งาน</label>
                        </div>
                    </div>
                </div>

                {{-- ช่วงเวลา --}}
                @foreach([
                    ['primary', 'calendar2-week', 'เปิด-ปิด รายวิชา',       'qe-open',       'open_date',             'qe-close',      'close_date'],
                    ['success', 'star',           'ช่วงประเมินคะแนน',         'qe-eval-open',  'evaluation_open_date',  'qe-eval-close', 'evaluation_close_date'],
                    ['info',    'pencil',          'ช่วงแก้ไขคะแนน',           'qe-grade-open', 'grade_edit_open_date',  'qe-grade-close','grade_edit_close_date'],
                ] as [$color, $icon, $label, $openId, $openName, $closeId, $closeName])
                <div class="p-2 rounded-3 mb-2" style="background:#f8f9fa;border-left:3px solid var(--bs-{{ $color }});">
                    <div class="small fw-semibold mb-2">
                        <i class="bi bi-{{ $icon }} me-1 text-{{ $color }}"></i>{{ $label }}
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small mb-1">วันเปิด</label>
                            <input type="datetime-local" class="form-control form-control-sm"
                                   id="{{ $openId }}" name="{{ $openName }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-1">วันปิด</label>
                            <input type="datetime-local" class="form-control form-control-sm"
                                   id="{{ $closeId }}" name="{{ $closeName }}">
                        </div>
                    </div>
                </div>
                @endforeach

            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary btn-sm px-4">
                    <i class="bi bi-check-circle me-1"></i>บันทึก
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Quick Edit modal: populate fields from data attributes
document.querySelectorAll('.btn-quick-edit').forEach(btn => {
    btn.addEventListener('click', function () {
        const d = this.dataset;
        document.getElementById('quickEditForm').action = `/admin/subjects/${d.subjectId}`;
        document.getElementById('qe-title').textContent  = d.subjectCode;
        document.getElementById('qe-code').value         = d.subjectCode;
        document.getElementById('qe-name').value         = d.subjectName;
        document.getElementById('qe-year').value         = d.year;
        document.getElementById('qe-semester').value     = d.semester;
        document.getElementById('qe-enabled').checked    = d.isEnabled === '1';
        document.getElementById('qe-open').value         = d.openDate  || '';
        document.getElementById('qe-close').value        = d.closeDate || '';
        document.getElementById('qe-eval-open').value    = d.evalOpen  || '';
        document.getElementById('qe-eval-close').value   = d.evalClose || '';
        document.getElementById('qe-grade-open').value   = d.gradeOpen || '';
        document.getElementById('qe-grade-close').value  = d.gradeClose|| '';
    });
});

// Term Edit modal: populate fields from data attributes
document.querySelectorAll('.btn-term-edit').forEach(btn => {
    btn.addEventListener('click', function () {
        const d = this.dataset;
        document.getElementById('te-title').textContent   = `ปีการศึกษา ${d.year} เทอม ${d.semester}`;
        document.getElementById('te-year').value          = d.year;
        document.getElementById('te-semester').value      = d.semester;
        document.getElementById('te-open').value          = d.openDate   || '';
        document.getElementById('te-close').value         = d.closeDate  || '';
        document.getElementById('te-eval-open').value     = d.evalOpen   || '';
        document.getElementById('te-eval-close').value    = d.evalClose  || '';
        document.getElementById('te-grade-open').value    = d.gradeOpen  || '';
        document.getElementById('te-grade-close').value   = d.gradeClose || '';
    });
});

// Term Delete modal: populate fields
document.querySelectorAll('.btn-term-delete').forEach(btn => {
    btn.addEventListener('click', function () {
        const year     = this.dataset.year;
        const semester = this.dataset.semester;
        const count    = this.dataset.count;
        document.getElementById('td-label').textContent  = `ปีการศึกษา ${year} เทอม ${semester}`;
        document.getElementById('td-count').textContent  = count;
        document.getElementById('termDeleteForm').action =
            `/admin/subjects/term/${year}/${semester}`;
    });
});

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

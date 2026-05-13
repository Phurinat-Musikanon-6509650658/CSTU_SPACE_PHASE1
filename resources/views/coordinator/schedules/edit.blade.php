@extends('layouts.app')

@section('title', 'แก้ไขตารางสอบและคณะกรรมการ | CSTU SPACE')

@push('styles')
<style>
    .page-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,.08);
        margin-bottom: 1.5rem;
    }
    .page-card-header {
        padding: 1.1rem 1.5rem;
        border-radius: 12px 12px 0 0;
        color: white;
    }
    .section-label {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #6c757d;
        margin-bottom: .5rem;
    }
    .time-preset-btn {
        font-size: .82rem;
        padding: .25rem .65rem;
        border-radius: 6px;
        border: 1.5px solid #dee2e6;
        background: white;
        cursor: pointer;
        transition: all .15s;
    }
    .time-preset-btn:hover, .time-preset-btn.active {
        border-color: #0d6efd;
        background: #e7f0ff;
        color: #0d47a1;
        font-weight: 600;
    }
    .committee-select.duplicate-error {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 .2rem rgba(220,53,69,.25) !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Header --}}
    <div class="mb-4">
        <a href="{{ route('coordinator.schedules.index') }}" class="btn btn-link text-decoration-none ps-0">
            <i class="bi bi-chevron-left me-1"></i>กลับรายการ
        </a>
        <h1 class="h2 fw-bold mt-2">
            <i class="bi bi-calendar-check me-2 text-primary"></i>แก้ไขตารางสอบและคณะกรรมการ
        </h1>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i><strong>กรุณาตรวจสอบข้อมูล:</strong>
            <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div id="duplicateWarning" class="alert alert-danger d-none" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>พบการเลือกอาจารย์ซ้ำกัน!</strong> กรุณาเลือกอาจารย์ที่แตกต่างกันสำหรับแต่ละตำแหน่ง
    </div>

    <form action="{{ route('coordinator.schedules.update', $project->project_id) }}" method="POST" id="mainForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="exam_datetime" id="h_exam_datetime">
        <input type="hidden" name="exam_end_time" id="h_exam_end_time">

        <div class="row g-4">

            {{-- LEFT: Project Info + Time --}}
            <div class="col-lg-5">

                {{-- Project Info --}}
                <div class="card page-card">
                    <div class="page-card-header" style="background:linear-gradient(135deg,#4e73df,#224abe);">
                        <h5 class="mb-0"><i class="bi bi-folder me-2"></i>ข้อมูลโครงงาน</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                            <span class="badge bg-primary">กลุ่ม {{ sprintf('%02d', $project->group_id) }}</span>
                            <code class="text-primary fw-bold">{{ $project->project_code }}</code>
                        </div>
                        <div class="fw-semibold mb-2">{{ $project->project_name ?? 'ยังไม่ระบุชื่อโครงงาน' }}</div>
                        <div class="text-muted small">
                            <i class="bi bi-people me-1"></i>
                            @foreach($project->group->members as $member)
                                {{ $member->student->firstname_std ?? '' }} {{ $member->student->lastname_std ?? '' }}@if(!$loop->last), @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Date & Time --}}
                <div class="card page-card">
                    <div class="page-card-header" style="background:linear-gradient(135deg,#1cc88a,#13855c);">
                        <h5 class="mb-0"><i class="bi bi-clock me-2"></i>วันและเวลาสอบ</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="section-label">วันที่สอบ</label>
                            <input type="date" class="form-control" id="examDate"
                                   value="{{ $project->exam_datetime ? $project->exam_datetime->format('Y-m-d') : '' }}">
                        </div>
                        <div class="mb-1">
                            <label class="section-label">เวลาเริ่มสอบ</label>
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                @foreach(['08:00','09:00','10:00','11:00','13:00','14:00','15:00','16:00'] as $t)
                                    <button type="button" class="time-preset-btn" data-time="{{ $t }}">{{ $t }}</button>
                                @endforeach
                            </div>
                            <input type="time" class="form-control" id="examTime" step="300"
                                   value="{{ $project->exam_datetime ? $project->exam_datetime->format('H:i') : '' }}">
                        </div>
                        <div class="mb-1 mt-3">
                            <label class="section-label">เวลาสิ้นสุดสอบ</label>
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                @foreach(['09:00','10:00','11:00','12:00','14:00','15:00','16:00','17:00'] as $t)
                                    <button type="button" class="time-preset-btn end-preset-btn" data-time="{{ $t }}">{{ $t }}</button>
                                @endforeach
                            </div>
                            <input type="time" class="form-control" id="examEndTime" step="300"
                                   value="{{ $project->exam_end_time ? $project->exam_end_time->format('H:i') : '' }}">
                        </div>

                        {{-- Summary --}}
                        <div class="bg-light rounded p-3 mt-3">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-calendar-check fs-4 text-success"></i>
                                <div>
                                    <div class="fw-bold" id="summaryDate">—</div>
                                    <div class="text-muted small" id="summaryTime">—</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Committee --}}
            <div class="col-lg-7">
                <div class="card page-card">
                    <div class="page-card-header" style="background:linear-gradient(135deg,#f6c23e,#e0a800);color:#212529;">
                        <h5 class="mb-0"><i class="bi bi-people me-2"></i>คณะกรรมการสอบ</h5>
                    </div>
                    <div class="card-body">

                        {{-- Advisor --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person-badge text-primary me-1"></i>อาจารย์ที่ปรึกษา
                            </label>
                            <select name="advisor_code" class="form-select committee-select @error('advisor_code') is-invalid @enderror">
                                <option value="">— ไม่มี —</option>
                                @foreach($lecturers as $lecturer)
                                    <option value="{{ $lecturer->user_code }}"
                                            {{ $project->advisor_code == $lecturer->user_code ? 'selected' : '' }}>
                                        {{ $lecturer->user_code }} — {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }}
                                    </option>
                                @endforeach
                            </select>
                            @error('advisor_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Committee 1 --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person-check text-success me-1"></i>กรรมการคนที่ 1
                            </label>
                            <select name="committee1_code" class="form-select committee-select @error('committee1_code') is-invalid @enderror">
                                <option value="">— ไม่มี —</option>
                                @foreach($lecturers as $lecturer)
                                    <option value="{{ $lecturer->user_code }}"
                                            {{ $project->committee1_code == $lecturer->user_code ? 'selected' : '' }}>
                                        {{ $lecturer->user_code }} — {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }}
                                    </option>
                                @endforeach
                            </select>
                            @error('committee1_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Committee 2 --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person-check text-success me-1"></i>กรรมการคนที่ 2
                            </label>
                            <select name="committee2_code" class="form-select committee-select @error('committee2_code') is-invalid @enderror">
                                <option value="">— ไม่มี —</option>
                                @foreach($lecturers as $lecturer)
                                    <option value="{{ $lecturer->user_code }}"
                                            {{ $project->committee2_code == $lecturer->user_code ? 'selected' : '' }}>
                                        {{ $lecturer->user_code }} — {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }}
                                    </option>
                                @endforeach
                            </select>
                            @error('committee2_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Committee 3 --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person-check text-success me-1"></i>กรรมการคนที่ 3
                            </label>
                            <select name="committee3_code" class="form-select committee-select @error('committee3_code') is-invalid @enderror">
                                <option value="">— ไม่มี —</option>
                                @foreach($lecturers as $lecturer)
                                    <option value="{{ $lecturer->user_code }}"
                                            {{ $project->committee3_code == $lecturer->user_code ? 'selected' : '' }}>
                                        {{ $lecturer->user_code }} — {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }}
                                    </option>
                                @endforeach
                            </select>
                            @error('committee3_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <hr class="my-3">
                        <p class="text-muted small mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            ที่ปรึกษาร่วมภายนอก — ต้องสร้างบัญชีใน Admin → Users ก่อน
                        </p>

                        {{-- Co-Advisor Internal --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person-lines-fill text-info me-1"></i>ที่ปรึกษาร่วมภายใน
                                <span class="badge bg-secondary ms-1" style="font-size:.7rem;">Co-Advisor Internal</span>
                            </label>
                            <select name="coadv_int_code" class="form-select committee-select @error('coadv_int_code') is-invalid @enderror">
                                <option value="">— ไม่มี —</option>
                                @foreach($lecturers as $lecturer)
                                    <option value="{{ $lecturer->user_code }}"
                                            {{ ($project->coAdvisorInternal?->user_code) == $lecturer->user_code ? 'selected' : '' }}>
                                        {{ $lecturer->prefix_user ?? '' }} {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }}
                                        ({{ $lecturer->user_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('coadv_int_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Co-Advisor External --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person-plus text-warning me-1"></i>ที่ปรึกษาร่วมภายนอก
                                <span class="badge bg-warning text-dark ms-1" style="font-size:.7rem;">Co-Advisor External</span>
                            </label>
                            <select name="coadv_ext_code" class="form-select committee-select @error('coadv_ext_code') is-invalid @enderror">
                                <option value="">— ไม่มี —</option>
                                @foreach($lecturers as $lecturer)
                                    <option value="{{ $lecturer->user_code }}"
                                            {{ ($project->coAdvisorExternal?->user_code) == $lecturer->user_code ? 'selected' : '' }}>
                                        {{ $lecturer->prefix_user ?? '' }} {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }}
                                        ({{ $lecturer->user_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('coadv_ext_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="d-flex gap-2">
                    <a href="{{ route('coordinator.schedules.index') }}" class="btn btn-outline-secondary flex-fill">
                        <i class="bi bi-x-circle me-1"></i>ยกเลิก
                    </a>
                    <button type="submit" class="btn btn-primary flex-fill fw-bold">
                        <i class="bi bi-save me-1"></i>บันทึกการเปลี่ยนแปลง
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Time presets (start)
document.querySelectorAll('.time-preset-btn:not(.end-preset-btn)').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.time-preset-btn:not(.end-preset-btn)').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('examTime').value = this.dataset.time;
        updateSummary();
    });
});

// Time presets (end)
document.querySelectorAll('.end-preset-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.end-preset-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('examEndTime').value = this.dataset.time;
    });
});

document.getElementById('examDate').addEventListener('change', updateSummary);
document.getElementById('examTime').addEventListener('change', updateSummary);

function updateSummary() {
    const date = document.getElementById('examDate').value;
    const time = document.getElementById('examTime').value;
    if (date) {
        const d = new Date(date);
        document.getElementById('summaryDate').textContent =
            d.toLocaleDateString('th-TH', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
    } else {
        document.getElementById('summaryDate').textContent = '—';
    }
    document.getElementById('summaryTime').textContent = time ? `เวลา ${time} น.` : '—';
}

// Duplicate committee check
function checkDuplicates() {
    const selects = document.querySelectorAll('.committee-select');
    const values = Array.from(selects).map(s => s.value).filter(v => v !== '');
    const hasDuplicate = values.length !== new Set(values).size;
    document.getElementById('duplicateWarning').classList.toggle('d-none', !hasDuplicate);
    selects.forEach(s => {
        const isDup = s.value !== '' && values.filter(v => v === s.value).length > 1;
        s.classList.toggle('duplicate-error', isDup);
    });
    return !hasDuplicate;
}

document.querySelectorAll('.committee-select').forEach(s => s.addEventListener('change', checkDuplicates));

// Form submit
document.getElementById('mainForm').addEventListener('submit', function(e) {
    if (!checkDuplicates()) {
        e.preventDefault();
        document.getElementById('duplicateWarning').scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    const date    = document.getElementById('examDate').value;
    const time    = document.getElementById('examTime').value;
    const endTime = document.getElementById('examEndTime').value;
    if (date && time) {
        document.getElementById('h_exam_datetime').value = `${date}T${time}`;
    }
    if (date && endTime) {
        document.getElementById('h_exam_end_time').value = `${date}T${endTime}`;
    }
});

// Initial render
updateSummary();
checkDuplicates();
</script>
@endpush

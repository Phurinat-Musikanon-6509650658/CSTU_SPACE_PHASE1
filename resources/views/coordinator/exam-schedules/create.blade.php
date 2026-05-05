@extends('layouts.app')

@section('title', 'เพิ่มตารางสอบ | CSTU SPACE')

@push('styles')
<style>
    body { background-color: #f8f9fa; }
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
    .duration-btn {
        font-size: .82rem;
        padding: .25rem .65rem;
        border-radius: 6px;
        border: 1.5px solid #dee2e6;
        background: white;
        cursor: pointer;
        transition: all .15s;
    }
    .duration-btn:hover, .duration-btn.active {
        border-color: #198754;
        background: #d1e7dd;
        color: #0a3622;
        font-weight: 600;
    }
    .project-item {
        border: 1.5px solid #dee2e6;
        border-radius: 8px;
        padding: .6rem .9rem;
        margin-bottom: .4rem;
        cursor: pointer;
        transition: all .15s;
    }
    .project-item:hover { border-color: #0d6efd; background: #f0f5ff; }
    .project-item.selected { border-color: #0d6efd; background: #e7f0ff; }
    .project-item.has-schedule { opacity: .5; pointer-events: none; background: #f8f9fa; }
    .project-code { font-size: .78rem; color: #6c757d; }
    .project-name { font-weight: 600; font-size: .9rem; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Header --}}
    <div class="mb-4">
        <a href="{{ route('coordinator.exam-schedules.index') }}" class="btn btn-link text-decoration-none ps-0">
            <i class="bi bi-chevron-left me-1"></i>กลับรายการตารางสอบ
        </a>
        <h1 class="h2 fw-bold mt-2">
            <i class="bi bi-calendar-plus me-2 text-primary"></i>เพิ่มตารางสอบ
        </h1>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i><strong>กรุณาตรวจสอบข้อมูล:</strong>
            <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('coordinator.exam-schedules.store') }}" id="mainForm">
        @csrf
        {{-- hidden combined datetime fields --}}
        <input type="hidden" name="ex_start_time" id="h_start">
        <input type="hidden" name="ex_end_time"   id="h_end">

        <div class="row g-4">
            {{-- LEFT: Project selection --}}
            <div class="col-lg-7">
                <div class="card page-card h-100">
                    <div class="page-card-header" style="background:linear-gradient(135deg,#4e73df,#224abe);">
                        <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>เลือกโครงงาน</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2 mb-3">
                            <input type="text" class="form-control form-control-sm" id="projectSearch" placeholder="ค้นหาโครงงาน...">
                            <button type="button" class="btn btn-sm btn-outline-primary text-nowrap" id="selectAllBtn">
                                <i class="bi bi-check-all me-1"></i>เลือกทั้งหมด
                            </button>
                        </div>

                        <div style="max-height: 420px; overflow-y: auto; padding-right: 4px;">
                            @foreach($projects as $project)
                                @php $hasSchedule = $project->examSchedule !== null; @endphp
                                <div class="project-item {{ $hasSchedule ? 'has-schedule' : '' }}"
                                     data-search="{{ strtolower($project->project_code . ' ' . $project->project_name) }}"
                                     onclick="toggleProject(this)">
                                    <input class="form-check-input project-checkbox me-2"
                                           type="checkbox"
                                           name="project_ids[]"
                                           value="{{ $project->project_id }}"
                                           id="p_{{ $project->project_id }}"
                                           {{ $hasSchedule ? 'disabled' : '' }}
                                           onclick="event.stopPropagation()">
                                    <label for="p_{{ $project->project_id }}" class="d-inline" onclick="event.stopPropagation()">
                                        <span class="project-name">{{ $project->project_name }}</span><br>
                                        <span class="project-code">{{ $project->project_code }}</span>
                                        @if($hasSchedule)
                                            <span class="badge bg-secondary ms-2" style="font-size:.7rem;">มีตารางสอบแล้ว</span>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        @error('project_ids')
                            <div class="text-danger small mt-2"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                        @enderror
                        <div class="mt-3">
                            <span class="badge bg-primary" id="selectedCount">เลือกแล้ว 0 โครงงาน</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Time & Location --}}
            <div class="col-lg-5">
                {{-- Date & Time --}}
                <div class="card page-card">
                    <div class="page-card-header" style="background:linear-gradient(135deg,#1cc88a,#13855c);">
                        <h5 class="mb-0"><i class="bi bi-clock me-2"></i>วันและเวลาสอบ</h5>
                    </div>
                    <div class="card-body">
                        {{-- Date --}}
                        <div class="mb-3">
                            <label class="section-label">วันที่สอบ</label>
                            <input type="date" class="form-control" id="examDate" required>
                        </div>

                        {{-- Start Time --}}
                        <div class="mb-1">
                            <label class="section-label">เวลาเริ่มสอบ</label>
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                @foreach(['08:00','09:00','10:00','11:00','13:00','14:00','15:00','16:00'] as $t)
                                    <button type="button" class="time-preset-btn" data-target="examStart" data-time="{{ $t }}">{{ $t }}</button>
                                @endforeach
                            </div>
                            <input type="time" class="form-control" id="examStart" step="300" required>
                        </div>

                        {{-- Duration quick pick --}}
                        <div class="mb-3">
                            <label class="section-label mt-3">ระยะเวลา (กดเพื่อเซ็ตเวลาสิ้นสุดอัตโนมัติ)</label>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach(['30 นาที' => 30, '1 ชั่วโมง' => 60, '1.5 ชั่วโมง' => 90, '2 ชั่วโมง' => 120, '3 ชั่วโมง' => 180] as $label => $mins)
                                    <button type="button" class="duration-btn" data-mins="{{ $mins }}">{{ $label }}</button>
                                @endforeach
                            </div>
                        </div>

                        {{-- End Time --}}
                        <div class="mb-0">
                            <label class="section-label">เวลาสิ้นสุด</label>
                            <input type="time" class="form-control" id="examEnd" step="300" required>
                            <div class="form-text"><i class="bi bi-info-circle me-1"></i>กดปุ่มระยะเวลาเพื่อคำนวณอัตโนมัติ</div>
                        </div>
                    </div>
                </div>

                {{-- Location & Notes --}}
                <div class="card page-card">
                    <div class="page-card-header" style="background:linear-gradient(135deg,#f6c23e,#e0a800);color:#212529;">
                        <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>สถานที่และหมายเหตุ</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">สถานที่สอบ</label>
                            <input type="text" class="form-control @error('location') is-invalid @enderror"
                                   name="location" value="{{ old('location') }}"
                                   placeholder="เช่น ห้อง 301 อาคาร 3">
                            @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">หมายเหตุ</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      name="notes" rows="3"
                                      placeholder="ข้อมูลเพิ่มเติม...">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="d-flex gap-2">
                    <a href="{{ route('coordinator.exam-schedules.index') }}" class="btn btn-outline-secondary flex-fill">
                        <i class="bi bi-x-circle me-1"></i>ยกเลิก
                    </a>
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-save me-1"></i>สร้างตารางสอบ
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// ── Project selection ──
function toggleProject(el) {
    if (el.classList.contains('has-schedule')) return;
    const cb = el.querySelector('.project-checkbox');
    cb.checked = !cb.checked;
    el.classList.toggle('selected', cb.checked);
    updateCount();
}
function updateCount() {
    const n = document.querySelectorAll('.project-checkbox:checked').length;
    const el = document.getElementById('selectedCount');
    el.textContent = `เลือกแล้ว ${n} โครงงาน`;
    el.className = n > 0 ? 'badge bg-success' : 'badge bg-primary';
}
document.querySelectorAll('.project-checkbox').forEach(cb => {
    cb.addEventListener('change', () => {
        cb.closest('.project-item').classList.toggle('selected', cb.checked);
        updateCount();
    });
});

// Search
document.getElementById('projectSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.project-item').forEach(el => {
        el.style.display = el.dataset.search.includes(q) ? '' : 'none';
    });
});

// Select all
let allSelected = false;
document.getElementById('selectAllBtn').addEventListener('click', function() {
    allSelected = !allSelected;
    document.querySelectorAll('.project-item:not(.has-schedule):not([style*="none"])').forEach(el => {
        const cb = el.querySelector('.project-checkbox');
        cb.checked = allSelected;
        el.classList.toggle('selected', allSelected);
    });
    this.innerHTML = allSelected
        ? '<i class="bi bi-x-circle me-1"></i>ยกเลิกทั้งหมด'
        : '<i class="bi bi-check-all me-1"></i>เลือกทั้งหมด';
    updateCount();
});

// ── Time presets ──
document.querySelectorAll('.time-preset-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.time-preset-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('examStart').value = this.dataset.time;
        applyDuration(); // recalc end if duration is active
    });
});

// ── Duration buttons ──
let activeDuration = null;
document.querySelectorAll('.duration-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.duration-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        activeDuration = parseInt(this.dataset.mins);
        applyDuration();
    });
});

function applyDuration() {
    if (!activeDuration) return;
    const start = document.getElementById('examStart').value;
    if (!start) return;
    const [h, m] = start.split(':').map(Number);
    const total = h * 60 + m + activeDuration;
    document.getElementById('examEnd').value = `${String(Math.floor(total / 60) % 24).padStart(2,'0')}:${String(total % 60).padStart(2,'0')}`;
}
document.getElementById('examStart').addEventListener('change', applyDuration);

// ── Form submit: combine date+time into hidden fields ──
document.getElementById('mainForm').addEventListener('submit', function(e) {
    const date  = document.getElementById('examDate').value;
    const start = document.getElementById('examStart').value;
    const end   = document.getElementById('examEnd').value;

    if (!date || !start || !end) {
        e.preventDefault();
        alert('กรุณากรอกวัน เวลาเริ่ม และเวลาสิ้นสุดให้ครบ');
        return;
    }
    document.getElementById('h_start').value = `${date}T${start}`;
    document.getElementById('h_end').value   = `${date}T${end}`;
});
</script>
@endpush

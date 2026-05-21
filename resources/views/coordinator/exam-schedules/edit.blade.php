@extends('layouts.app')

@section('title', 'แก้ไขตารางสอบ | CSTU SPACE')

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
    .info-row { font-size: .85rem; }
    .info-row strong { color: #374151; }
</style>
@endpush

@section('content')
<div class="container">

    {{-- Page Header --}}
    <div style="background:white;border-radius:var(--border-radius);padding:2rem;margin-bottom:2rem;box-shadow:var(--shadow-light);">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 style="color:#2c3e50;font-weight:700;font-size:2rem;margin-bottom:.5rem;">
                    <i class="bi bi-pencil-square me-2"></i>แก้ไขตารางสอบ
                </h2>
                <p class="mb-0 opacity-75">กำหนดวันเวลาสอบและคณะกรรมการ</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('coordinator.exam-schedules.index') }}" class="btn" style="background:#f8f9fa;color:#2c3e50;font-weight:600;display:inline-flex;align-items:center;gap:.5rem;border-radius:10px;">
                    <i class="bi bi-arrow-left"></i><span>กลับรายการ</span>
                </a>
                <a href="{{ route('coordinator.dashboard') }}" class="btn" style="background:#f8f9fa;color:#2c3e50;font-weight:600;display:inline-flex;align-items:center;gap:.5rem;border-radius:10px;">
                    <i class="bi bi-house"></i><span>Dashboard</span>
                </a>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i><strong>กรุณาตรวจสอบข้อมูล:</strong>
            <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('coordinator.exam-schedules.update', $examSchedule->ex_id) }}" id="mainForm">
        @csrf @method('PUT')
        <input type="hidden" name="ex_start_time" id="h_start">
        <input type="hidden" name="ex_end_time"   id="h_end">

        <div class="row g-4">
            {{-- LEFT: Project info + location --}}
            <div class="col-lg-5">
                {{-- Project --}}
                <div class="card page-card">
                    <div class="page-card-header" style="background:linear-gradient(135deg,#4e73df,#224abe);">
                        <h5 class="mb-0"><i class="bi bi-folder me-2"></i>โครงงาน</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">เลือกโครงงาน <span class="text-danger">*</span></label>
                            <select class="form-select @error('project_id') is-invalid @enderror"
                                    name="project_id" required>
                                <option value="">— เลือกโครงงาน —</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->project_id }}"
                                        {{ old('project_id', $examSchedule->project_id) == $project->project_id ? 'selected' : '' }}>
                                        {{ $project->project_code }} — {{ $project->project_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Current project info --}}
                        @if($examSchedule->project)
                            <div class="bg-light rounded p-3 info-row">
                                <div class="mb-1"><strong>ที่ปรึกษา:</strong>
                                    {{ $examSchedule->project->advisor?->firstname_user }}
                                    {{ $examSchedule->project->advisor?->lastname_user ?? '—' }}
                                </div>
                                <div class="mb-1"><strong>กรรมการ 1:</strong>
                                    {{ $examSchedule->project->committee1?->firstname_user }}
                                    {{ $examSchedule->project->committee1?->lastname_user ?? '—' }}
                                </div>
                                <div class="mb-1"><strong>กรรมการ 2:</strong>
                                    {{ $examSchedule->project->committee2?->firstname_user }}
                                    {{ $examSchedule->project->committee2?->lastname_user ?? '—' }}
                                </div>
                                <div><strong>กรรมการ 3:</strong>
                                    {{ $examSchedule->project->committee3?->firstname_user }}
                                    {{ $examSchedule->project->committee3?->lastname_user ?? '—' }}
                                </div>
                            </div>
                        @endif
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
                                   name="location" value="{{ old('location', $examSchedule->location) }}"
                                   placeholder="เช่น ห้อง 301 อาคาร 3">
                            @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">หมายเหตุ</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      name="notes" rows="3"
                                      placeholder="ข้อมูลเพิ่มเติม...">{{ old('notes', $examSchedule->notes) }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Meta info --}}
                <div class="card page-card">
                    <div class="card-body py-3">
                        <div class="info-row text-muted">
                            <div><i class="bi bi-clock-history me-1"></i>สร้างเมื่อ: {{ thaiDateTime($examSchedule->created_at) }}</div>
                            <div><i class="bi bi-pencil me-1"></i>แก้ไขล่าสุด: {{ thaiDateTime($examSchedule->updated_at) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Time --}}
            <div class="col-lg-7">
                <div class="card page-card">
                    <div class="page-card-header" style="background:linear-gradient(135deg,#1cc88a,#13855c);">
                        <h5 class="mb-0"><i class="bi bi-clock me-2"></i>วันและเวลาสอบ</h5>
                    </div>
                    <div class="card-body">
                        {{-- Date --}}
                        <div class="mb-4">
                            <label class="section-label">วันที่สอบ</label>
                            <input type="date" class="form-control" id="examDate"
                                   value="{{ old('exam_date', $examSchedule->ex_start_time->format('Y-m-d')) }}" required>
                        </div>

                        <div class="row g-4">
                            {{-- Start Time --}}
                            <div class="col-md-6">
                                <label class="section-label">เวลาเริ่มสอบ</label>
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    @foreach(['08:00','09:00','10:00','11:00','13:00','14:00','15:00','16:00'] as $t)
                                        <button type="button" class="time-preset-btn"
                                                data-target="examStart" data-time="{{ $t }}">{{ $t }}</button>
                                    @endforeach
                                </div>
                                <input type="time" class="form-control" id="examStart" step="300"
                                       value="{{ old('exam_start', $examSchedule->ex_start_time->format('H:i')) }}" required>
                            </div>

                            {{-- End Time --}}
                            <div class="col-md-6">
                                <label class="section-label">เวลาสิ้นสุด</label>
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    @foreach(['30 นาที' => 30, '1 ชม.' => 60, '1.5 ชม.' => 90, '2 ชม.' => 120, '3 ชม.' => 180] as $label => $mins)
                                        <button type="button" class="duration-btn" data-mins="{{ $mins }}">{{ $label }}</button>
                                    @endforeach
                                </div>
                                <input type="time" class="form-control" id="examEnd" step="300"
                                       value="{{ old('exam_end', $examSchedule->ex_end_time->format('H:i')) }}" required>
                                <div class="form-text"><i class="bi bi-info-circle me-1"></i>กดปุ่มระยะเวลาเพื่อคำนวณจากเวลาเริ่ม</div>
                            </div>
                        </div>

                        {{-- Summary --}}
                        <div class="bg-light rounded p-3 mt-4" id="timeSummary">
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

                {{-- Submit --}}
                <div class="d-flex gap-2">
                    <a href="{{ route('coordinator.exam-schedules.index') }}" class="btn btn-outline-secondary flex-fill">
                        <i class="bi bi-x-circle me-1"></i>ยกเลิก
                    </a>
                    <button type="submit" class="btn btn-warning flex-fill fw-bold">
                        <i class="bi bi-save me-1"></i>บันทึกการแก้ไข
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Time presets
document.querySelectorAll('.time-preset-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.time-preset-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('examStart').value = this.dataset.time;
        updateSummary();
    });
});

// Duration buttons
let activeDuration = null;
document.querySelectorAll('.duration-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.duration-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        activeDuration = parseInt(this.dataset.mins);
        const start = document.getElementById('examStart').value;
        if (start) {
            const [h, m] = start.split(':').map(Number);
            const total = h * 60 + m + activeDuration;
            document.getElementById('examEnd').value = `${String(Math.floor(total / 60) % 24).padStart(2,'0')}:${String(total % 60).padStart(2,'0')}`;
        }
        updateSummary();
    });
});

document.getElementById('examDate').addEventListener('change', updateSummary);
document.getElementById('examStart').addEventListener('change', updateSummary);
document.getElementById('examEnd').addEventListener('change', updateSummary);

function updateSummary() {
    const date  = document.getElementById('examDate').value;
    const start = document.getElementById('examStart').value;
    const end   = document.getElementById('examEnd').value;
    if (date) {
        const d = new Date(date);
        document.getElementById('summaryDate').textContent = d.toLocaleDateString('th-TH', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
    }
    if (start && end) {
        const [sh, sm] = start.split(':').map(Number);
        const [eh, em] = end.split(':').map(Number);
        const durMins = (eh * 60 + em) - (sh * 60 + sm);
        const durH = Math.floor(durMins / 60), durM = durMins % 60;
        document.getElementById('summaryTime').textContent = `${start} — ${end}  (${durH > 0 ? durH + ' ชม.' : ''}${durM > 0 ? ' ' + durM + ' น.' : ''})`;
    }
}

// Form submit
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

// Initial summary render
updateSummary();
</script>
@endpush

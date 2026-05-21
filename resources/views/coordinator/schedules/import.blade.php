@extends('layouts.app')

@section('title', 'Import ตารางสอบ | CSTU SPACE')

@section('content')
<div class="container-fluid px-4 py-2">

    {{-- Page Header --}}
    <div style="background:white;border-radius:var(--border-radius);padding:2rem;margin-bottom:2rem;box-shadow:var(--shadow-light);">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 style="color:#2c3e50;font-weight:700;font-size:2rem;margin-bottom:.5rem;">
                    <i class="bi bi-file-earmark-arrow-up me-2"></i>Import ตารางสอบจาก CSV
                </h2>
                <p class="mb-0 opacity-75">นำเข้าข้อมูลวันเวลาสอบและคณะกรรมการทีเดียวหลายโครงงาน</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('coordinator.schedules.index') }}" class="btn" style="background:#f8f9fa;color:#2c3e50;font-weight:600;display:inline-flex;align-items:center;gap:.5rem;border-radius:10px;">
                    <i class="bi bi-arrow-left"></i><span>กลับรายการ</span>
                </a>
                <a href="{{ route('coordinator.dashboard') }}" class="btn" style="background:#f8f9fa;color:#2c3e50;font-weight:600;display:inline-flex;align-items:center;gap:.5rem;border-radius:10px;">
                    <i class="bi bi-house"></i><span>Dashboard</span>
                </a>
            </div>
        </div>
    </div>

    @if(!isset($preview))
    {{-- ───────────────── Upload Form ───────────────── --}}

    <div class="row">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">

                    <div class="alert alert-info border-0 mb-4" style="background:#e8f4ff;">
                        <h6 class="mb-2"><i class="bi bi-info-circle-fill me-2"></i>รูปแบบไฟล์ CSV ที่รองรับ</h6>
                        <p class="mb-1 small">คอลัมน์ต้องเรียงตามลำดับนี้ (header แถวแรก):</p>
                        <div class="csv-preview">
                            <div class="csv-preview-header"><i class="bi bi-file-earmark-text me-1"></i>ตัวอย่าง CSV</div>
                            <div class="csv-preview-body">
                                <pre>project_code,exam_datetime,advisor_code,committee1_code,committee2_code,committee3_code
68-1-01_kdc-r1,2025-05-20 09:00,SCH,JDO,SMY,
68-1-02_abc-r2,2025-05-20 10:00,SCH,KWT,JDO,KMT</pre>
                            </div>
                        </div>
                        <ul class="mb-0 mt-2 small">
                            <li><strong>project_code</strong> — รหัสโครงงานในระบบ (จำเป็น)</li>
                            <li><strong>exam_datetime</strong> — รูปแบบ <code>YYYY-MM-DD HH:MM</code> (จำเป็น)</li>
                            <li><strong>advisor_code / committee*_code</strong> — user_code ของอาจารย์ (ไม่บังคับ ถ้าว่างจะไม่อัปเดต)</li>
                        </ul>
                    </div>

                    <form action="{{ route('coordinator.schedules.import.preview') }}" method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-semibold">เลือกไฟล์ CSV <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control @error('file') is-invalid @enderror"
                                   accept=".csv,.txt" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">ประเภทไฟล์: .csv, .txt (ขนาดไม่เกิน 2MB)</small>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-2"></i>ตรวจสอบและ Preview
                            </button>
                            <a href="{{ route('coordinator.schedules.import.template') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-download me-2"></i>ดาวน์โหลด Template
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold border-bottom">
                    <i class="bi bi-people me-2 text-primary"></i>รายชื่ออาจารย์และ user_code
                </div>
                <div class="card-body p-0" style="max-height:420px; overflow-y:auto;">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="ps-3">user_code</th>
                                <th>ชื่อ-นามสกุล</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lecturers ?? [] as $lec)
                            <tr>
                                <td class="ps-3"><code class="text-primary fw-bold">{{ $lec->user_code }}</code></td>
                                <td>{{ $lec->firstname_user }} {{ $lec->lastname_user }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted py-3">ไม่พบข้อมูลอาจารย์</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @else
    {{-- ───────────────── Preview ───────────────── --}}

    @php
        $validRows   = collect($preview)->where('valid', true);
        $invalidRows = collect($preview)->where('valid', false);
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="h3 fw-bold text-dark mb-0">{{ count($preview) }}</div>
                <div class="small text-muted">แถวทั้งหมด</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="h3 fw-bold text-success mb-0">{{ $validRows->count() }}</div>
                <div class="small text-muted">พร้อม Import</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="h3 fw-bold text-danger mb-0">{{ $invalidRows->count() }}</div>
                <div class="small text-muted">มีข้อผิดพลาด</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3 d-flex flex-row align-items-center justify-content-center gap-2">
                @if($validRows->count() > 0)
                <form action="{{ route('coordinator.schedules.import.confirm') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bi bi-check-circle me-1"></i>ยืนยัน Import ({{ $validRows->count() }})
                    </button>
                </form>
                @endif
                <a href="{{ route('coordinator.schedules.import.form') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>อัปโหลดใหม่
                </a>
            </div>
        </div>
    </div>

    @if($invalidRows->count() > 0)
    <div class="alert alert-warning border-0 shadow-sm mb-4">
        <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>พบข้อผิดพลาด {{ $invalidRows->count() }} แถว</strong>
        — แถวเหล่านี้จะถูกข้ามเมื่อยืนยัน Import
    </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-table me-2"></i>ตรวจสอบข้อมูลก่อน Import
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle" style="font-size:.85rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:40px;">#</th>
                        <th>รหัสโครงงาน</th>
                        <th>ชื่อโครงงาน / สมาชิก</th>
                        <th>วันเวลาสอบ</th>
                        <th>ที่ปรึกษา</th>
                        <th>กรรมการ 1–3</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($preview as $row)
                    <tr class="{{ $row['valid'] ? '' : 'table-danger' }}">
                        <td class="ps-3 text-muted">{{ $row['line'] }}</td>
                        <td><code class="fw-bold {{ $row['valid'] ? 'text-primary' : 'text-danger' }}">{{ $row['project_code'] }}</code></td>
                        <td>
                            <div class="fw-semibold">{{ $row['project_name'] }}</div>
                            <div class="text-muted" style="font-size:.78rem;">{{ $row['members'] }}</div>
                        </td>
                        <td>
                            @if($row['exam_datetime'])
                                <span class="text-success fw-semibold">{{ $row['exam_datetime'] }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($row['advisor_code'])
                                <code>{{ $row['advisor_code'] }}</code>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @foreach(['committee1_code','committee2_code','committee3_code'] as $col)
                                @if($row[$col])
                                    <code class="me-1">{{ $row[$col] }}</code>
                                @endif
                            @endforeach
                            @if(!$row['committee1_code'] && !$row['committee2_code'] && !$row['committee3_code'])
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($row['valid'])
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle me-1"></i>OK
                                </span>
                            @else
                                <div>
                                    @foreach($row['errors'] as $err)
                                        <div class="badge bg-danger-subtle text-danger border border-danger-subtle d-block mb-1"
                                             style="white-space:normal; text-align:left;">
                                            <i class="bi bi-x-circle me-1"></i>{{ $err }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($validRows->count() > 0)
    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('coordinator.schedules.import.form') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-counterclockwise me-1"></i>อัปโหลดใหม่
        </a>
        <form action="{{ route('coordinator.schedules.import.confirm') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-circle me-2"></i>ยืนยัน Import {{ $validRows->count() }} โครงงาน
            </button>
        </form>
    </div>
    @endif

    @endif

</div>
@endsection

@push('styles')
<style>
    .csv-preview {
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #bee3f8;
        margin-top: 8px;
    }
    .csv-preview-header {
        background: #d1ecff;
        padding: 6px 12px;
        font-size: .8rem;
        font-weight: 600;
        color: #1a6fa0;
    }
    .csv-preview-body {
        padding: 10px 12px;
        background: #f0f8ff;
    }
    .csv-preview-body pre {
        font-size: 11px;
        color: #2c5282;
        margin: 0;
        white-space: pre-wrap;
    }
</style>
@endpush

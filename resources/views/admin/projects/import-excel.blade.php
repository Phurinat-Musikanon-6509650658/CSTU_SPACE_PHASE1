@extends('layouts.app')

@section('title', 'Import โครงงานจาก Excel | CSTU SPACE')

@push('styles')
<style>
    .page-card { border:none; border-radius:14px; box-shadow:0 2px 10px rgba(0,0,0,.08); background:white; }
    .upload-zone {
        border:2px dashed #c0c9e0; border-radius:12px; padding:2.5rem;
        text-align:center; background:#f8f9ff; transition:all .2s; cursor:pointer;
    }
    .upload-zone:hover, .upload-zone.drag-over { border-color:#667eea; background:#eef0ff; }
    .upload-icon { font-size:3rem; color:#667eea; margin-bottom:.75rem; }

    .tbl-sm td, .tbl-sm th { font-size:.79rem; padding:.38rem .5rem; vertical-align:middle; }
    .tbl-sm thead th {
        background:linear-gradient(135deg,#2E75B6,#1F4E79);
        color:white; border:none; font-weight:600; white-space:nowrap; position:sticky; top:0; z-index:1;
    }
    .row-new    { background:#f0fff4; }
    .row-exists { background:#fffbea; }
    .stats-bar { display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
    .stats-chip { flex:1; min-width:100px; text-align:center; border-radius:10px; padding:.6rem 1rem; font-weight:600; }
    .chip-new   { background:#dcfce7; color:#166534; }
    .chip-exist { background:#fef9c3; color:#854d0e; }
    .chip-warn  { background:#fff3cd; color:#856404; }
    .chip-total { background:#e0e7ff; color:#3730a3; }
    .type-badge { display:inline-block; font-size:.7rem; padding:.15rem .45rem; border-radius:10px; }
    .type-s  { background:#ede9fe; color:#5b21b6; }
    .type-r  { background:#dcfce7; color:#166534; }
    .type-rs { background:#fce7f3; color:#9d174d; }
    .warn-list { font-size:.72rem; color:#856404; margin:0; padding-left:1rem; }
    .step-num {
        width:26px; height:26px; border-radius:50%; background:linear-gradient(135deg,#667eea,#764ba2);
        color:white; display:inline-flex; align-items:center; justify-content:center;
        font-size:.8rem; font-weight:700; flex-shrink:0;
    }
    .member-tag { display:inline-block; font-size:.72rem; background:#e8f4f8; color:#1565c0;
                  border-radius:6px; padding:.1rem .4rem; margin:.1rem; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Header --}}
    <div class="mb-3">
        <a href="{{ route('users.index') }}" class="btn btn-link text-decoration-none ps-0 text-secondary">
            <i class="bi bi-chevron-left me-1"></i>กลับ User Management
        </a>
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mt-1">
            <div>
                <h1 class="h3 fw-bold mb-0">
                    <i class="bi bi-folder2-open me-2 text-primary"></i>Import โครงงานจาก Excel
                </h1>
                <p class="text-muted small mb-0">
                    รองรับ sheet ชื่อประกอบด้วย <code>--Project</code>
                    เช่น <code>CS303--ProjectsDetails</code>, <code>CS403--ProjectDetails</code>
                </p>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3">
            <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- Left: Info + Upload --}}
        <div class="col-lg-4">

            <div class="page-card p-4 mb-3">
                <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-info"></i>Column Mapping</h6>
                <table class="table table-sm mb-0" style="font-size:.79rem;">
                    <tr><th class="text-muted fw-normal" style="width:40%;">col</th><th>ฟิลด์</th></tr>
                    <tr><td>col[1]</td><td><code>project_code</code></td></tr>
                    <tr><td>col[3] (s/r/m)</td><td><code>student_type</code> → s/r/rs</td></tr>
                    <tr><td>col[4]</td><td><code>subject_code</code> (groups)</td></tr>
                    <tr><td>col[5]</td><td><code>project_name</code></td></tr>
                    <tr><td>col[7-12] (สมาชิก 1)</td><td>prefix / ชื่อ / รหัส / email / phone</td></tr>
                    <tr><td>col[13-18] (สมาชิก 2)</td><td>เหมือนกัน (ถ้ามี)</td></tr>
                    <tr><td><strong>col[19]</strong></td><td><strong>จำนวนสมาชิก</strong> (1 หรือ 2)</td></tr>
                    <tr><td>col[23-26]</td><td>advisor / comm1 / comm2 / comm3 user_code</td></tr>
                    <tr><td>col[28]</td><td>วันสอบ <span class="badge bg-secondary">ข้าม</span></td></tr>
                </table>
                <div class="alert alert-warning border-0 rounded-3 mt-3 mb-0 small py-2">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    วันสอบ (col[28]) เป็นรูปแบบ Thai text — ระบบจะข้ามและให้ coordinator กำหนดภายหลัง
                </div>
            </div>

            <div class="page-card p-4 mb-3">
                <h6 class="fw-bold mb-3"><i class="bi bi-list-ol me-2 text-primary"></i>ขั้นตอน</h6>
                <div class="d-flex gap-2 mb-2 align-items-start">
                    <span class="step-num">1</span>
                    <div class="small">Import นักศึกษาจาก <code>--Students</code> sheet ก่อน (ถ้ายังไม่ได้ทำ)</div>
                </div>
                <div class="d-flex gap-2 mb-2 align-items-start">
                    <span class="step-num">2</span>
                    <div class="small">Import ผู้ใช้ (ที่ปรึกษา/กรรมการ) ก่อน เพื่อให้ user_code ถูกต้อง</div>
                </div>
                <div class="d-flex gap-2 mb-2 align-items-start">
                    <span class="step-num">3</span>
                    <div class="small">อัปโหลดไฟล์ Excel — ตรวจสอบ Preview</div>
                </div>
                <div class="d-flex gap-2 align-items-start">
                    <span class="step-num">4</span>
                    <div class="small">กด <strong>ยืนยัน Import</strong></div>
                </div>
            </div>

            <div class="page-card p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-upload me-2 text-primary"></i>อัปโหลดไฟล์</h6>
                <form method="POST" action="{{ route('admin.projects.importExcel.preview') }}"
                      enctype="multipart/form-data" id="uploadForm">
                    @csrf

                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label fw-semibold small">
                                <i class="bi bi-calendar2 me-1 text-primary"></i>ภาคเรียน
                            </label>
                            <select class="form-select" name="semester">
                                <option value="1" {{ ($semester ?? 2) == 1 ? 'selected' : '' }}>1</option>
                                <option value="2" {{ ($semester ?? 2) == 2 ? 'selected' : '' }}>2</option>
                            </select>
                        </div>
                        <div class="col-8">
                            <label class="form-label fw-semibold small">
                                <i class="bi bi-calendar2 me-1 text-primary"></i>ปีการศึกษา (พ.ศ.)
                            </label>
                            <input type="number" class="form-control" name="year"
                                   value="{{ old('year', $year ?? 2568) }}" min="2560" max="2599">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            <i class="bi bi-calendar-month me-1 text-danger"></i>เดือน/ปี ที่สอบ (ค.ศ.)
                        </label>
                        <input type="month" class="form-control" name="exam_month"
                               value="{{ old('exam_month', $examMonth ?? '2025-08') }}"
                               placeholder="2025-08">
                        <small class="text-muted">ระบุเพื่อแปลงวันสอบจาก Excel เป็น datetime — ถ้าเว้นว่างจะไม่กำหนดวันสอบ</small>
                    </div>

                    <div class="upload-zone" id="uploadZone"
                         onclick="document.getElementById('fileInput').click()">
                        <div class="upload-icon"><i class="bi bi-file-earmark-excel"></i></div>
                        <div class="fw-semibold mb-1">คลิกหรือลากไฟล์มาวางที่นี่</div>
                        <div class="text-muted small">.xlsx เท่านั้น ขนาดไม่เกิน 20 MB</div>
                        <div id="fileNameDisplay" class="mt-2 text-primary small fw-semibold"></div>
                    </div>
                    <input type="file" name="file" id="fileInput" class="d-none"
                           accept=".xlsx,.xls" onchange="onFileSelect(this)">
                    <button type="submit" class="btn btn-primary w-100 mt-3"
                            id="uploadBtn" disabled>
                        <i class="bi bi-search me-2"></i>Preview ข้อมูล
                    </button>
                </form>
            </div>
        </div>

        {{-- Right: Preview --}}
        <div class="col-lg-8">
            @isset($preview)

            <div class="page-card p-3">
                <div class="stats-bar">
                    <div class="stats-chip" style="background:#e0f2fe;color:#0369a1;">
                        ภาคเรียน {{ $semester ?? '?' }}/{{ $year ?? '?' }}
                    </div>
                    <div class="stats-chip chip-total">รวม {{ count($preview) }} โครงงาน</div>
                    <div class="stats-chip chip-new">✓ ใหม่: {{ $newCount ?? 0 }}</div>
                    <div class="stats-chip chip-exist">⚠ มีอยู่แล้ว: {{ $existsCount ?? 0 }}</div>
                    @if(($dateCount ?? 0) > 0)
                    <div class="stats-chip" style="background:#d1fae5;color:#065f46;">
                        📅 มีวันสอบ: {{ $dateCount }}
                    </div>
                    @endif
                    @if(($warnCount ?? 0) > 0)
                    <div class="stats-chip chip-warn">⚠ warning: {{ $warnCount }}</div>
                    @endif
                </div>

                @if(($newCount ?? 0) > 0)
                <form method="POST" action="{{ route('admin.projects.importExcel.confirm') }}" id="confirmForm">
                    @csrf
                    <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
                        <div class="text-muted small">
                            <i class="bi bi-info-circle me-1"></i>
                            แถวสีเหลือง = มีอยู่แล้ว (จะข้าม) | แถวสีเขียว = สร้างใหม่
                        </div>
                        <button type="submit" class="btn btn-success btn-sm fw-semibold px-4">
                            <i class="bi bi-folder-plus me-2"></i>ยืนยัน Import
                            ({{ $newCount }} โครงงาน)
                        </button>
                    </div>
                </form>
                @else
                <div class="alert alert-warning border-0 rounded-3 small mb-3">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    โครงงานทั้งหมดในไฟล์มีในระบบแล้ว ไม่มีรายการใหม่
                </div>
                @endif

                <div class="table-responsive" style="max-height:600px; overflow-y:auto;">
                    <table class="table tbl-sm mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>สถานะ</th>
                                <th style="min-width:130px;">Project Code</th>
                                <th style="min-width:180px;">ชื่อโครงงาน</th>
                                <th>วิชา</th>
                                <th>ประเภท</th>
                                <th style="min-width:150px;">วันสอบ</th>
                                <th style="min-width:150px;">สมาชิก</th>
                                <th style="min-width:110px;">ที่ปรึกษา / กรรมการ</th>
                                <th>หมายเหตุ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($preview as $i => $item)
                            <tr class="{{ $item['exists'] ? 'row-exists' : 'row-new' }}">
                                <td class="text-center text-muted">{{ $i + 1 }}</td>
                                <td>
                                    @if($item['exists'])
                                        <span class="badge bg-warning text-dark" style="font-size:.7rem;">มีอยู่แล้ว</span>
                                    @else
                                        <span class="badge bg-success" style="font-size:.7rem;">ใหม่</span>
                                    @endif
                                </td>
                                <td><code style="font-size:.74rem;">{{ $item['project_code'] }}</code></td>
                                <td style="font-size:.78rem;">{{ $item['project_name'] }}</td>
                                <td>
                                    <span class="badge bg-primary rounded-pill" style="font-size:.68rem;">
                                        {{ $item['course_code'] }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $typeClass = match($item['student_type']) {
                                            's'  => 'type-s',
                                            'r'  => 'type-r',
                                            'rs' => 'type-rs',
                                            default => 'type-r',
                                        };
                                        $typeLabel = match($item['student_type']) {
                                            's'  => 'พิเศษ',
                                            'r'  => 'ปกติ',
                                            'rs' => 'ผสม',
                                            default => $item['student_type'],
                                        };
                                    @endphp
                                    <span class="type-badge {{ $typeClass }}">{{ $typeLabel }}</span>
                                    <span class="text-muted" style="font-size:.68rem;">({{ $item['raw_type'] }})</span>
                                </td>
                                <td style="font-size:.76rem;">
                                    @if($item['exam_datetime'])
                                        <span class="badge bg-success rounded-pill" style="font-size:.68rem;">
                                            {{ \Carbon\Carbon::parse($item['exam_datetime'])->format('d M Y H:i') }}
                                        </span>
                                    @elseif($item['thai_date_raw'])
                                        <span class="text-muted" title="{{ $item['thai_date_raw'] }}">
                                            <i class="bi bi-dash-circle text-warning"></i>
                                            {{ $item['thai_date_raw'] }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @foreach($item['members'] as $mem)
                                        <span class="member-tag {{ $mem['in_db'] ? '' : 'bg-warning text-dark' }}"
                                              title="{{ $mem['email_std'] }}">
                                            {{ $mem['firstname_std'] }} {{ $mem['lastname_std'] }}
                                            @if(!$mem['in_db'])<i class="bi bi-plus-circle ms-1" title="จะสร้างใหม่"></i>@endif
                                        </span>
                                    @endforeach
                                </td>
                                <td style="font-size:.74rem;">
                                    @if($item['advisor_code'])
                                        <div><i class="bi bi-person-badge text-primary me-1"></i>
                                            @if($item['resolved_adv'])
                                                <strong>{{ $item['advisor_code'] }}</strong>
                                            @else
                                                <span class="text-danger">{{ $item['advisor_code'] }} ✗</span>
                                            @endif
                                        </div>
                                    @endif
                                    @foreach([
                                        [$item['comm1_code'], $item['resolved_comm1']],
                                        [$item['comm2_code'], $item['resolved_comm2']],
                                        [$item['comm3_code'], $item['resolved_comm3']],
                                    ] as [$code, $resolved])
                                        @if($code)
                                            <div>
                                                @if($resolved)
                                                    <span class="text-muted">{{ $code }}</span>
                                                @else
                                                    <span class="text-danger">{{ $code }} ✗</span>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </td>
                                <td>
                                    @if(!empty($item['warnings']))
                                        <ul class="warn-list">
                                            @foreach($item['warnings'] as $w)
                                                <li>{{ $w }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-success" style="font-size:.74rem;">✓</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @else
            <div class="page-card p-5 text-center text-muted">
                <i class="bi bi-folder2-open" style="font-size:4rem; opacity:.15;"></i>
                <p class="mt-3 mb-0 fw-semibold">ยังไม่มีข้อมูล Preview</p>
                <small>อัปโหลดไฟล์ Excel ที่มี sheet <code>CS303--ProjectsDetails</code> หรือ <code>CS403--ProjectDetails</code></small>
            </div>
            @endisset
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function onFileSelect(input) {
    const name = input.files[0]?.name ?? '';
    document.getElementById('fileNameDisplay').textContent = name ? '📎 ' + name : '';
    document.getElementById('uploadBtn').disabled = !name;
}
const zone = document.getElementById('uploadZone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
zone.addEventListener('drop', e => {
    e.preventDefault(); zone.classList.remove('drag-over');
    const fi = document.getElementById('fileInput');
    fi.files = e.dataTransfer.files;
    onFileSelect(fi);
});
</script>
@endpush

@extends('layouts.app')

@section('title', 'Import โครงงาน | CSTU SPACE')

@push('styles')
<style>
    .page-card {
        border: none;
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(0,0,0,.08);
        background: white;
    }
    .upload-zone {
        border: 2px dashed #c0c9e0;
        border-radius: 12px;
        padding: 2.5rem;
        text-align: center;
        background: #f8f9ff;
        transition: all .2s;
        cursor: pointer;
    }
    .upload-zone:hover, .upload-zone.drag-over {
        border-color: #667eea;
        background: #eef0ff;
    }
    .upload-icon { font-size: 3rem; color: #667eea; margin-bottom: .75rem; }
    .preview-row-ok       { background: #f0fff4; }
    .preview-row-duplicate{ background: #fffbea; }
    .preview-row-error    { background: #fff5f5; }
    .status-ok        { background:#dcfce7; color:#166534; }
    .status-duplicate { background:#fef9c3; color:#854d0e; }
    .status-error     { background:#fee2e2; color:#991b1b; }
    .status-pill {
        display:inline-block; font-size:.74rem; font-weight:600;
        padding:.2rem .6rem; border-radius:20px;
    }
    .err-list { font-size:.76rem; color:#b91c1c; }
    .tbl-sm td, .tbl-sm th { font-size:.78rem; padding:.38rem .45rem; vertical-align:middle; }
    .tbl-sm thead th {
        background: linear-gradient(135deg,#2E75B6,#1F4E79);
        color:white; border:none; font-weight:600; white-space:nowrap;
    }
    .stats-bar { display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
    .stats-chip {
        flex:1; min-width:120px; text-align:center; border-radius:10px; padding:.6rem 1rem;
        font-weight:600;
    }
    .chip-ok   { background:#dcfce7; color:#166534; }
    .chip-dup  { background:#fef9c3; color:#854d0e; }
    .chip-err  { background:#fee2e2; color:#991b1b; }
    .chip-total{ background:#e0e7ff; color:#3730a3; }
    .step-num {
        width:28px; height:28px; border-radius:50%;
        background:var(--gradient-primary); color:white;
        display:inline-flex; align-items:center; justify-content:center;
        font-size:.82rem; font-weight:700; flex-shrink:0;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-2">

    {{-- Page Header --}}
    <div style="background:white;border-radius:var(--border-radius);padding:2rem;margin-bottom:2rem;box-shadow:var(--shadow-light);">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 style="color:#2c3e50;font-weight:700;font-size:2rem;margin-bottom:.5rem;">
                    <i class="bi bi-file-earmark-arrow-up me-2"></i>Import ข้อมูลโครงงาน
                </h2>
                <p class="mb-0 opacity-75">นำเข้าโดยตรง (ข้ามขั้นตอน workflow) — ต้องยืนยันก่อนบันทึก</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('coordinator.subject-summary.template') }}" class="btn btn-outline-success">
                    <i class="bi bi-download me-1"></i>ดาวน์โหลด Template
                </a>
                <a href="{{ route('coordinator.subject-summary.index') }}" class="btn" style="background:#f8f9fa;color:#2c3e50;font-weight:600;display:inline-flex;align-items:center;gap:.5rem;border-radius:10px;">
                    <i class="bi bi-arrow-left"></i><span>กลับรายการ</span>
                </a>
                <a href="{{ route('coordinator.dashboard') }}" class="btn" style="background:#f8f9fa;color:#2c3e50;font-weight:600;display:inline-flex;align-items:center;gap:.5rem;border-radius:10px;">
                    <i class="bi bi-house"></i><span>Dashboard</span>
                </a>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3">
            <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- ── Left: Steps & Upload ──────────────────────────── --}}
        <div class="col-lg-4">
            <div class="page-card p-4 mb-3">
                <h6 class="fw-bold mb-3"><i class="bi bi-list-ol me-2 text-primary"></i>ขั้นตอนการ Import</h6>
                <div class="d-flex gap-2 mb-2 align-items-start">
                    <span class="step-num">1</span>
                    <div class="small">ดาวน์โหลด <strong>Template</strong> แล้วกรอกข้อมูลโครงงาน</div>
                </div>
                <div class="d-flex gap-2 mb-2 align-items-start">
                    <span class="step-num">2</span>
                    <div class="small">อัปโหลดไฟล์ .xlsx ที่กรอกแล้ว</div>
                </div>
                <div class="d-flex gap-2 mb-2 align-items-start">
                    <span class="step-num">3</span>
                    <div class="small">ตรวจสอบข้อมูล preview — แก้ไขในไฟล์ถ้ามี error</div>
                </div>
                <div class="d-flex gap-2 align-items-start">
                    <span class="step-num">4</span>
                    <div class="small">กด <strong>"ยืนยัน Import"</strong> เพื่อบันทึกข้อมูลทั้งหมด</div>
                </div>
            </div>

            <div class="page-card p-4 mb-3">
                <h6 class="fw-bold mb-2"><i class="bi bi-info-circle me-2 text-info"></i>สีสถานะ</h6>
                <div class="d-flex flex-column gap-1 small">
                    <div><span class="status-pill status-ok">✓ OK</span> — พร้อม import</div>
                    <div><span class="status-pill status-duplicate">⚠ ซ้ำ</span> — มีในระบบแล้ว (จะอัปเดต)</div>
                    <div><span class="status-pill status-error">✗ Error</span> — ข้อมูลไม่ถูกต้อง (จะข้าม)</div>
                </div>
            </div>

            {{-- Upload form (always visible) --}}
            <div class="page-card p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-upload me-2 text-primary"></i>อัปโหลดไฟล์</h6>
                <form method="POST" action="{{ route('coordinator.subject-summary.import.preview') }}"
                      enctype="multipart/form-data" id="uploadForm">
                    @csrf
                    <div class="upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
                        <div class="upload-icon"><i class="bi bi-file-earmark-excel"></i></div>
                        <div class="fw-semibold mb-1">คลิกหรือลากไฟล์มาวางที่นี่</div>
                        <div class="text-muted small">.xlsx เท่านั้น ขนาดไม่เกิน 10 MB</div>
                        <div id="fileNameDisplay" class="mt-2 text-primary small fw-semibold"></div>
                    </div>
                    <input type="file" name="file" id="fileInput" class="d-none" accept=".xlsx,.xls"
                           onchange="onFileSelect(this)">
                    <button type="submit" class="btn btn-primary w-100 mt-3" id="uploadBtn" disabled>
                        <i class="bi bi-search me-2"></i>Preview ข้อมูล
                    </button>
                </form>
            </div>
        </div>

        {{-- ── Right: Preview table ─────────────────────────── --}}
        <div class="col-lg-8">
            @isset($preview)
            <div class="page-card p-3">
                {{-- Stats bar --}}
                <div class="stats-bar">
                    <div class="stats-chip chip-total">รวม {{ count($preview) }} แถว</div>
                    <div class="stats-chip chip-ok">✓ OK: {{ $ok ?? 0 }}</div>
                    <div class="stats-chip chip-dup">⚠ ซ้ำ: {{ $dup ?? 0 }}</div>
                    <div class="stats-chip chip-err">✗ Error: {{ $err ?? 0 }}</div>
                </div>

                @if(($ok ?? 0) + ($dup ?? 0) > 0)
                    <form method="POST" action="{{ route('coordinator.subject-summary.import.confirm') }}">
                        @csrf
                        <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
                            <div class="text-muted small">
                                <i class="bi bi-info-circle me-1"></i>
                                แถว Error จะถูกข้าม — ตรวจสอบแล้วกด <strong>ยืนยัน</strong>
                            </div>
                            <button type="submit" class="btn btn-success btn-sm fw-semibold px-4">
                                <i class="bi bi-check2-circle me-2"></i>ยืนยัน Import
                                ({{ ($ok ?? 0) + ($dup ?? 0) }} รายการ)
                            </button>
                        </div>
                    </form>
                @else
                    <div class="alert alert-warning border-0 rounded-3 small">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        ไม่มีแถวที่สามารถ import ได้ กรุณาแก้ไขข้อมูลในไฟล์แล้วอัปโหลดใหม่
                    </div>
                @endif

                {{-- Preview table --}}
                <div class="table-responsive" style="max-height:600px; overflow-y:auto;">
                    <table class="table tbl-sm mb-0">
                        <thead>
                            <tr>
                                <th>แถว</th>
                                <th>สถานะ</th>
                                <th>รหัสโครงงาน</th>
                                <th>วิชา</th>
                                <th style="min-width:180px;">ชื่อโครงงาน</th>
                                <th>ประเภท</th>
                                <th style="min-width:120px;">นศ.1</th>
                                <th style="min-width:120px;">นศ.2</th>
                                <th>ทปษ.</th>
                                <th>กก.1</th>
                                <th>กก.2</th>
                                <th>วันสอบ</th>
                                <th>ข้อผิดพลาด</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($preview as $item)
                            <tr class="preview-row-{{ $item['status'] }}">
                                <td class="text-center text-muted">{{ $item['line'] }}</td>
                                <td>
                                    <span class="status-pill status-{{ $item['status'] }}">
                                        @if($item['status']==='ok') ✓ OK
                                        @elseif($item['status']==='duplicate') ⚠ ซ้ำ
                                        @else ✗ Error
                                        @endif
                                    </span>
                                </td>
                                <td><code style="font-size:.74rem;">{{ $item['project_code'] ?: '-' }}</code></td>
                                <td>{{ $item['subject_code'] }}</td>
                                <td style="max-width:200px;">
                                    <div style="font-size:.78rem; line-height:1.3;">
                                        {{ \Illuminate\Support\Str::limit($item['project_name'], 60) }}
                                    </div>
                                </td>
                                <td>{{ $item['project_type'] }}</td>
                                <td>
                                    <div style="font-size:.77rem;">
                                        {{ $item['m1_firstname'] }} {{ $item['m1_lastname'] }}
                                        @if($item['m1_id'])<br><small class="text-muted">{{ $item['m1_id'] }}</small>@endif
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size:.77rem;">
                                        @if($item['m2_id'])
                                            {{ $item['m2_firstname'] }} {{ $item['m2_lastname'] }}
                                            <br><small class="text-muted">{{ $item['m2_id'] }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td><code style="font-size:.74rem;">{{ $item['adv_code'] }}</code></td>
                                <td><code style="font-size:.74rem;">{{ $item['comm1_code'] ?: '-' }}</code></td>
                                <td><code style="font-size:.74rem;">{{ $item['comm2_code'] ?: '-' }}</code></td>
                                <td style="font-size:.77rem; white-space:nowrap;">{{ $item['exam_date'] ?: '-' }}</td>
                                <td>
                                    @if(!empty($item['errors']))
                                        <ul class="err-list mb-0 ps-3">
                                            @foreach($item['errors'] as $e)
                                                <li>{{ $e }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            {{-- Placeholder when no preview --}}
            <div class="page-card p-5 text-center text-muted">
                <i class="bi bi-table" style="font-size:4rem; opacity:.2;"></i>
                <p class="mt-3 mb-0 fw-semibold">ยังไม่มีข้อมูล Preview</p>
                <small>อัปโหลดไฟล์ Template ที่กรอกแล้วทางด้านซ้าย</small>
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

// Drag-and-drop
const zone = document.getElementById('uploadZone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('drag-over');
    const fi = document.getElementById('fileInput');
    fi.files = e.dataTransfer.files;
    onFileSelect(fi);
});
</script>
@endpush

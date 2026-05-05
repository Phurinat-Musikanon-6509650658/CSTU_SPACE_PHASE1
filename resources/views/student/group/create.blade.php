@extends('layouts.student')

@section('title', 'สร้างกลุ่มโครงงาน')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    :root {
        --blue: #0066cc;
        --blue-dark: #004999;
        --red: #dc143c;
        --green: #198754;
    }

    body { background-color: #f0f4f8; }

    /* ── Hero Header ── */
    .hero-header {
        background: linear-gradient(135deg, var(--blue) 0%, var(--blue-dark) 100%);
        border-radius: 16px;
        padding: 2rem 2.5rem;
        color: white;
        margin-bottom: 1.75rem;
        position: relative;
        overflow: hidden;
    }
    .hero-header::before {
        content: '';
        position: absolute;
        top: -40px; right: -40px;
        width: 160px; height: 160px;
        background: rgba(255,255,255,0.07);
        border-radius: 50%;
    }
    .hero-header::after {
        content: '';
        position: absolute;
        bottom: -60px; right: 60px;
        width: 220px; height: 220px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }
    .hero-header h1 {
        font-size: 1.75rem;
        font-weight: 800;
        margin-bottom: 0.25rem;
    }
    .hero-header p { opacity: .85; margin-bottom: 0; }
    .group-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255,255,255,0.18);
        border: 1px solid rgba(255,255,255,0.35);
        border-radius: 50px;
        padding: 0.45rem 1.1rem;
        font-size: 0.9rem;
        font-weight: 700;
        margin-top: 1rem;
        backdrop-filter: blur(4px);
    }

    /* ── Cards ── */
    .section-card {
        background: white;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        overflow: hidden;
        height: 100%;
    }
    .section-card-header {
        padding: 1.1rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-weight: 700;
        font-size: 1rem;
        border-bottom: 1px solid #f0f0f0;
    }
    .section-card-header.blue  { background: #eff5ff; color: var(--blue); }
    .section-card-header.red   { background: #fff0f3; color: var(--red); }
    .section-card-body { padding: 1.5rem; }

    /* ── Form controls ── */
    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.4rem;
        font-size: 0.9rem;
    }
    .form-control, .form-select {
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        padding: 0.6rem 0.9rem;
        font-size: 0.95rem;
        transition: border-color .2s, box-shadow .2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--blue);
        box-shadow: 0 0 0 3px rgba(0,102,204,.12);
    }
    .form-control[readonly] {
        background: #f9fafb;
        color: #6b7280;
    }
    .form-text { font-size: 0.8rem; color: #9ca3af; margin-top: 0.3rem; }

    /* ── Info tip ── */
    .tip-box {
        background: #eff5ff;
        border-left: 3px solid var(--blue);
        border-radius: 8px;
        padding: 0.9rem 1rem;
        font-size: 0.85rem;
        color: #374151;
    }
    .tip-box ul { margin: 0.5rem 0 0; padding-left: 1.3rem; }
    .tip-box li { margin-bottom: 0.3rem; }

    /* ── Current user card ── */
    .leader-card {
        border: 1.5px solid #d1fae5;
        background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
        border-radius: 10px;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .leader-avatar {
        width: 44px; height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--blue), var(--blue-dark));
        color: white;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .leader-name { font-weight: 700; color: #065f46; font-size: 0.95rem; }
    .leader-id   { font-size: 0.8rem; color: #6b7280; }

    /* ── Selected member preview ── */
    .selected-preview {
        border: 1.5px solid #bbf7d0;
        background: #f0fdf4;
        border-radius: 10px;
        padding: 0.9rem 1.1rem;
        display: none;
        align-items: center;
        gap: 0.75rem;
        margin-top: 0.75rem;
        animation: fadeIn .25s ease;
    }
    @keyframes fadeIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }
    .selected-preview i { color: var(--green); font-size: 1.3rem; flex-shrink:0; }
    .selected-preview-text { font-size: 0.88rem; color: #374151; font-weight: 500; }

    /* ── Select2 overrides ── */
    .select2-container { width: 100% !important; }
    .select2-container .select2-selection--single {
        height: 42px !important;
        border: 1.5px solid #e5e7eb !important;
        border-radius: 8px !important;
    }
    .select2-container .select2-selection--single .select2-selection__rendered {
        line-height: 40px !important;
        padding-left: 0.9rem !important;
        color: #374151;
        font-size: 0.95rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important; right: 10px;
    }
    .select2-container--default.select2-container--open .select2-selection--single,
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: var(--blue) !important;
        box-shadow: 0 0 0 3px rgba(0,102,204,.12) !important;
        outline: none;
    }
    .select2-dropdown {
        border: 1.5px solid var(--blue) !important;
        border-radius: 8px !important;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
    }
    .select2-results__option { padding: 0.65rem 1rem; font-size: 0.9rem; }
    .select2-results__option--highlighted { background-color: var(--blue) !important; }

    /* ── Footer buttons ── */
    .form-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding-top: 1.5rem;
        margin-top: 1.5rem;
        border-top: 1px solid #f0f0f0;
    }
    @media (max-width: 576px) {
        .form-footer { flex-direction: column-reverse; }
        .form-footer a, .form-footer button { width: 100%; justify-content: center; }
    }

    /* ── Alert ── */
    .alert { border-radius: 10px; border: none; }
    .alert-danger {
        background: #fff5f5;
        border-left: 3px solid #dc3545;
        color: #991b1b;
    }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">

        {{-- Hero Header --}}
        <div class="hero-header">
            <a href="{{ route('student.menu') }}" class="text-white text-decoration-none small opacity-75 d-inline-flex align-items-center gap-1 mb-3">
                <i class="bi bi-chevron-left"></i> กลับหน้าเมนู
            </a>
            <h1><i class="bi bi-people-fill me-2"></i>สร้างกลุ่มโครงงาน</h1>
            <p>กรอกข้อมูลและเชิญสมาชิกเพื่อเริ่มต้นโครงงาน</p>
            <div class="group-badge">
                <i class="bi bi-hash"></i>
                หมายเลขกลุ่มของคุณ: <strong>กลุ่มที่ {{ $nextGroupNumber }}</strong>
            </div>
        </div>

        {{-- Error Alert --}}
        @if($errors->any() || session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                @if($errors->any())
                    <strong>เกิดข้อผิดพลาด:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                @else
                    {{ session('error') }}
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Form --}}
        <form action="{{ route('groups.store') }}" method="POST" id="createGroupForm">
            @csrf
            <div class="row g-4 mb-4">

                {{-- Left: Group Info --}}
                <div class="col-md-6">
                    <div class="section-card">
                        <div class="section-card-header blue">
                            <i class="bi bi-info-circle-fill"></i> ข้อมูลกลุ่ม
                        </div>
                        <div class="section-card-body">
                            <div class="mb-3">
                                <label class="form-label">รหัสวิชา</label>
                                <input type="text" class="form-control" name="subject_code" value="{{ $courseCode }}" readonly>
                                <div class="form-text"><i class="bi bi-lock me-1"></i>ล็อกตามข้อมูลของคุณ</div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label">ปีการศึกษา</label>
                                    <input type="text" class="form-control" name="year" value="{{ $year }}" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">ภาคเรียน</label>
                                    <input type="text" class="form-control" name="semester" value="{{ $semester }}" readonly>
                                </div>
                            </div>
                            <div class="tip-box">
                                <i class="bi bi-lightbulb-fill text-warning me-1"></i>
                                <strong>หมายเหตุ:</strong> ชื่อโครงงานและรายละเอียดจะกรอกในขั้นตอนการเสนอหัวข้อโครงงานหลังจากสร้างกลุ่มสำเร็จ
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right: Member Invite --}}
                <div class="col-md-6">
                    <div class="section-card">
                        <div class="section-card-header red">
                            <i class="bi bi-person-plus-fill"></i> สมาชิกกลุ่ม
                        </div>
                        <div class="section-card-body">

                            {{-- Leader (me) --}}
                            <div class="mb-3">
                                <label class="form-label">สมาชิกคนที่ 1 (หัวหน้ากลุ่ม)</label>
                                <div class="leader-card">
                                    <div class="leader-avatar">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <div>
                                        <div class="leader-name">{{ Auth::guard('student')->user()->full_name }}</div>
                                        <div class="leader-id">{{ Auth::guard('student')->user()->username_std }}</div>
                                    </div>
                                    <span class="badge bg-success ms-auto">ตัวคุณ</span>
                                </div>
                            </div>

                            {{-- Invite --}}
                            <div class="mb-2">
                                <label class="form-label">
                                    สมาชิกคนที่ 2
                                    <span class="text-muted fw-normal">(ไม่บังคับ)</span>
                                </label>
                                <select id="invite_username" name="invite_username">
                                    <option value="">พิมพ์ชื่อหรือรหัสนักศึกษา...</option>
                                </select>
                                <div class="form-text"><i class="bi bi-info-circle me-1"></i>แสดงเฉพาะนักศึกษาที่ยังไม่มีกลุ่ม</div>
                            </div>

                            {{-- Selected preview --}}
                            <div class="selected-preview" id="selected-member">
                                <i class="bi bi-check-circle-fill"></i>
                                <div class="selected-preview-text" id="member-info"></div>
                            </div>

                            <div class="tip-box mt-3">
                                <ul>
                                    <li>กลุ่มมีสมาชิกได้สูงสุด <strong>2 คน</strong></li>
                                    <li>สมาชิกที่เชิญต้อง<strong>ตอบรับ</strong>คำเชิญ</li>
                                    <li>ทำงาน<strong>คนเดียวก็ได้</strong></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Buttons --}}
            <div class="form-footer">
                <a href="{{ route('student.menu') }}" class="btn btn-outline-secondary px-4">
                    <i class="bi bi-x-circle me-2"></i>ยกเลิก
                </a>
                <button type="submit" class="btn btn-primary px-5 fw-bold" style="background: linear-gradient(135deg,#0066cc,#004999); border:none; border-radius:8px; padding-top:.7rem; padding-bottom:.7rem;">
                    <i class="bi bi-check-circle-fill me-2"></i>สร้างกลุ่มโครงงาน
                </button>
            </div>
        </form>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#invite_username').select2({
        placeholder: 'พิมพ์ชื่อหรือรหัสนักศึกษา...',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#invite_username').parent(),
        ajax: {
            url: '{{ route("groups.search-students") }}',
            dataType: 'json',
            delay: 250,
            data: params => ({ search: params.term || '' }),
            processResults: data => ({
                results: data.map(s => ({
                    id: s.username_std,
                    text: s.firstname_std + ' ' + s.lastname_std + ' (' + s.username_std + ')'
                }))
            }),
            cache: true
        }
    });

    $('#invite_username').on('select2:select', function(e) {
        $('#member-info').text(e.params.data.text);
        $('#selected-member').css('display', 'flex');
    });

    $('#invite_username').on('select2:unselect', function() {
        $('#selected-member').hide();
    });
});
</script>
@endpush

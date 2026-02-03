@extends('layouts.student')

@section('title', 'สร้างกลุ่มโครงงาน')

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    :root {
        --color-primary: #0066cc;
        --color-secondary: #dc143c;
        --color-success: #28a745;
        --gradient-primary: linear-gradient(135deg, #0066cc 0%, #004999 100%);
        --gradient-card: linear-gradient(135deg, rgba(0, 102, 204, 0.05), rgba(220, 20, 60, 0.05));
        --shadow-card: 0 10px 40px rgba(0, 0, 0, 0.1);
        --border-radius: 16px;
    }

    .page-header {
        background: var(--gradient-card);
        padding: 2rem;
        border-radius: var(--border-radius);
        margin-bottom: 2rem;
        border-left: 5px solid var(--color-primary);
    }

    .page-header h1 {
        color: var(--color-primary);
        font-weight: 700;
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .page-header p {
        color: #6c757d;
        font-size: 1.1rem;
        margin-bottom: 0;
    }

    .group-number-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        border: 2px solid var(--color-primary);
        box-shadow: 0 4px 15px rgba(0, 102, 204, 0.2);
        margin-top: 1rem;
    }

    .group-number-badge i {
        font-size: 1.5rem;
        color: var(--color-primary);
    }

    .group-number-badge strong {
        color: var(--color-primary);
        font-size: 1.1rem;
    }

    .form-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 2.5rem;
        box-shadow: var(--shadow-card);
        border: none;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 3px solid #f0f0f0;
    }

    .section-header i {
        font-size: 1.5rem;
    }

    .section-header h5 {
        margin: 0;
        font-weight: 700;
        font-size: 1.3rem;
    }

    .section-header.blue {
        color: var(--color-primary);
    }

    .section-header.red {
        color: var(--color-secondary);
    }

    .form-label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-label .required {
        color: var(--color-secondary);
    }

    .form-control, .form-select {
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.15);
    }

    .form-control:disabled, .form-control[readonly] {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: #6c757d;
    }

    .info-card {
        background: var(--gradient-card);
        border: 2px solid rgba(0, 102, 204, 0.2);
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .info-card i {
        color: var(--color-primary);
        font-size: 1.2rem;
    }

    .info-card ul {
        margin-bottom: 0;
        padding-left: 1.5rem;
        margin-top: 0.75rem;
    }

    .info-card li {
        margin-bottom: 0.5rem;
        color: #495057;
    }

    .member-card {
        background: linear-gradient(135deg, rgba(0, 102, 204, 0.08), rgba(220, 20, 60, 0.08));
        border: 2px solid rgba(0, 102, 204, 0.3);
        border-radius: 12px;
        padding: 1.5rem;
        transition: all 0.3s ease;
    }

    .member-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 102, 204, 0.15);
    }

    .member-card .card-title {
        color: var(--color-primary);
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .member-card .card-title i {
        font-size: 1.3rem;
    }

    .selected-member-card {
        background: white;
        border: 2px solid var(--color-success);
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.1);
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .selected-member-card h6 {
        color: var(--color-success);
        font-weight: 700;
        margin-bottom: 0.75rem;
    }

    .btn-action-group {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 2rem;
        margin-top: 2rem;
        border-top: 3px solid #f0f0f0;
    }

    .btn-cancel {
        background: #6c757d;
        color: white;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-cancel:hover {
        background: #5a6268;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
    }

    .btn-submit {
        background: var(--gradient-primary);
        color: white;
        border: none;
        padding: 0.875rem 2.5rem;
        border-radius: 10px;
        font-weight: 700;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        box-shadow: 0 8px 20px rgba(0, 102, 204, 0.3);
    }

    .btn-submit:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(0, 102, 204, 0.4);
    }

    .btn-submit i {
        font-size: 1.3rem;
    }

    .select2-container {
        width: 100% !important;
    }
    
    .select2-container .select2-selection--single {
        height: 48px !important;
        border: 2px solid #e0e0e0 !important;
        border-radius: 10px !important;
        transition: all 0.3s ease;
    }
    
    .select2-container .select2-selection--single .select2-selection__rendered {
        line-height: 44px !important;
        padding-left: 1rem !important;
        color: #495057;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px !important;
        right: 10px;
    }
    
    .select2-container--default .select2-selection--single:focus,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: var(--color-primary) !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.15);
    }
    
    .select2-dropdown {
        border: 2px solid var(--color-primary) !important;
        border-radius: 10px !important;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        margin-top: 4px;
    }
    
    .select2-results__option {
        padding: 0.75rem 1rem;
    }

    .select2-results__option--highlighted {
        background-color: var(--color-primary) !important;
    }

    .alert {
        border-radius: 12px;
        border: none;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
    }

    .alert-danger {
        background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(220, 53, 69, 0.05));
        border-left: 4px solid #dc3545;
    }

    .alert-info {
        background: linear-gradient(135deg, rgba(0, 102, 204, 0.1), rgba(0, 102, 204, 0.05));
        border-left: 4px solid var(--color-primary);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header h1 {
            font-size: 1.5rem;
        }

        .form-card {
            padding: 1.5rem;
        }

        .btn-action-group {
            flex-direction: column;
            gap: 1rem;
        }

        .btn-cancel, .btn-submit {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Page Header -->
            <div class="page-header">
                <h1>
                    <i class="bi bi-plus-circle-fill me-2"></i>สร้างกลุ่มโครงงาน
                </h1>
                <p>กรอกข้อมูลโครงงานและเชิญสมาชิก</p>
                
                <!-- แสดงหมายเลขกลุ่มที่จะได้รับ -->
                <div class="group-number-badge">
                    <i class="bi bi-hash"></i>
                    <strong>หมายเลขกลุ่มของคุณจะเป็น: กลุ่มที่ {{ $nextGroupNumber }}</strong>
                </div>
            </div>

            <!-- Alerts -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <strong>เกิดข้อผิดพลาด!</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Create Group Form -->
            <div class="form-card">
                <form action="{{ route('groups.store') }}" method="POST" id="createGroupForm">
                    @csrf
                    
                    <div class="row">
                        <!-- Group Information -->
                        <div class="col-md-6 mb-4">
                            <div class="section-header blue">
                                <i class="bi bi-info-circle-fill"></i>
                                <h5>ข้อมูลกลุ่ม</h5>
                            </div>
                            
                            <div class="mb-4">
                                <label for="subject_code" class="form-label">
                                    รหัสวิชา <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control" id="subject_code" name="subject_code" value="{{ $courseCode }}" readonly>
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>รหัสวิชาถูกล็อกตามข้อมูลของคุณ
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-4">
                                        <label for="year" class="form-label">
                                            ปีการศึกษา <span class="required">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="year" name="year" value="{{ $year }}" readonly>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-4">
                                        <label for="semester" class="form-label">
                                            ภาคการศึกษา <span class="required">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="semester" name="semester" value="{{ $semester }}" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="info-card">
                                <i class="bi bi-lightbulb-fill me-2"></i>
                                <strong>หมายเหตุ:</strong>
                                <p class="mb-0 mt-2">ชื่อโครงงานและรายละเอียดจะกรอกในขั้นตอนการเสนอหัวข้อโครงงาน หลังจากสร้างกลุ่มเรียบร้อยแล้ว</p>
                            </div>
                        </div>

                        <!-- Member Invitation -->
                        <div class="col-md-6 mb-4">
                            <div class="section-header red">
                                <i class="bi bi-person-plus-fill"></i>
                                <h5>เชิญสมาชิก</h5>
                            </div>
                            
                            <div class="info-card">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                <strong>ข้อมูลสำคัญ:</strong>
                                <ul>
                                    <li>กลุ่มสามารถมีสมาชิกได้สูงสุด <strong>2 คน</strong></li>
                                    <li>คุณจะเป็นสมาชิกคนแรก (หัวหน้ากลุ่ม) โดยอัตโนมัติ</li>
                                    <li>การเชิญสมาชิก<strong>ไม่บังคับ</strong> (ทำงานคนเดียวได้)</li>
                                    <li>สมาชิกที่ถูกเชิญต้อง<strong>ตอบรับ</strong>เพื่อเข้าร่วมกลุ่ม</li>
                                </ul>
                            </div>

                            <div class="mb-4">
                                <label for="invite_username" class="form-label">
                                    <i class="bi bi-search me-1"></i>ค้นหาและเชิญสมาชิกคนที่ 2
                                </label>
                                <select class="form-select" id="invite_username" name="invite_username">
                                    <option value="">พิมพ์ชื่อหรือรหัสนักศึกษา (ไม่บังคับ)</option>
                                </select>
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>ระบบจะแสดงเฉพาะนักศึกษาที่ยังไม่มีกลุ่ม
                                </div>
                            </div>

                            <div class="mb-4" id="selected-member" style="display: none;">
                                <div class="selected-member-card">
                                    <h6>
                                        <i class="bi bi-check-circle-fill me-2"></i>สมาชิกที่เลือก
                                    </h6>
                                    <p class="card-text mb-2" id="member-info"></p>
                                    <small class="text-muted">
                                        <i class="bi bi-envelope me-1"></i>
                                        คำเชิญจะถูกส่งหลังจากสร้างกลุ่มสำเร็จ
                                    </small>
                                </div>
                            </div>

                            <!-- Current User Info -->
                            <div class="member-card">
                                <div class="card-title">
                                    <i class="bi bi-person-fill"></i>
                                    <span>สมาชิกคนที่ 1 (หัวหน้ากลุ่ม)</span>
                                </div>
                                <p class="card-text mb-1">
                                    <strong style="font-size: 1.1rem;">{{ Auth::guard('student')->user()->full_name }}</strong>
                                </p>
                                <p class="mb-0">
                                    <small class="text-muted">
                                        <i class="bi bi-person-badge me-1"></i>{{ Auth::guard('student')->user()->username_std }}
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="btn-action-group">
                        <a href="{{ route('student.menu') }}" class="btn-cancel">
                            <i class="bi bi-x-circle"></i>
                            <span>ยกเลิก</span>
                        </a>
                        <button type="submit" class="btn-submit">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>สร้างกลุ่มโครงงาน</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize Select2 for student selection
        $('#invite_username').select2({
            placeholder: 'พิมพ์ชื่อหรือรหัสนักศึกษา...',
            allowClear: true,
            width: '100%',
            dropdownAutoWidth: true,
            ajax: {
                url: '{{ route("groups.search-students") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term || ''
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.map(function(student) {
                            return {
                                id: student.username_std,
                                text: student.firstname_std + ' ' + student.lastname_std + ' (' + student.username_std + ')'
                            };
                        })
                    };
                },
                cache: true
            },
            // ป้องกันการเลื่อนหน้า
            dropdownParent: $('#invite_username').parent()
        });

        // Show selected member info
        $('#invite_username').on('select2:select', function (e) {
            var data = e.params.data;
            if (data.id) {
                $('#member-info').text(data.text);
                $('#selected-member').show();
            }
        });

        // Hide selected member info when cleared
        $('#invite_username').on('select2:unselect', function (e) {
            $('#selected-member').hide();
        });

        // Form validation
        $('#createGroupForm').on('submit', function(e) {
            var subjectCode = $('#subject_code').val();
            var year = $('#year').val();
            var semester = $('#semester').val();

            if (!subjectCode || !year || !semester) {
                e.preventDefault();
                alert('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
                return false;
            }
        });
    });
</script>
@endpush

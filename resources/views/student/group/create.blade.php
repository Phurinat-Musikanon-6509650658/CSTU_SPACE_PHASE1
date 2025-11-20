<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สร้างกลุ่มโครงงาน - CSTU SPACE</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Kanit', sans-serif;
        }
        
        .form-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .select2-container .select2-selection--single {
            height: 38px !important;
            border: 1px solid #ced4da !important;
            border-radius: 6px !important;
        }
        
        .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important;
            padding-left: 10px !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="form-card p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="fw-bold text-primary mb-1">
                                <i class="fas fa-plus-circle me-2"></i>สร้างกลุ่มโครงงาน
                            </h2>
                            <p class="text-muted mb-0">ข้อมูลโครงงานและการเชิญสมาชิก</p>
                        </div>
                        <a href="{{ route('student.menu') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>กลับ
                        </a>
                    </div>
                </div>

                <!-- Alerts -->
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>เกิดข้อผิดพลาด!</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Create Group Form -->
                <div class="form-card p-4">
                    <form action="{{ route('groups.store') }}" method="POST" id="createGroupForm">
                        @csrf
                        
                        <div class="row">
                            <!-- Project Information -->
                            <div class="col-md-6">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-info-circle me-2"></i>ข้อมูลโครงงาน
                                </h5>
                                
                                <div class="mb-3">
                                    <label for="project_name" class="form-label fw-semibold">ชื่อโครงงาน *</label>
                                    <input type="text" class="form-control" id="project_name" name="project_name" 
                                           value="{{ old('project_name') }}" required>
                                    <div class="form-text">ชื่อเต็มของโครงงาน</div>
                                </div>

                                <div class="mb-3">
                                    <label for="project_code" class="form-label fw-semibold">รหัสโครงงาน *</label>
                                    <input type="text" class="form-control" id="project_code" name="project_code" 
                                           value="{{ old('project_code') }}" required>
                                    <div class="form-text">รหัสเฉพาะของโครงงาน (ไม่ซ้ำกัน)</div>
                                </div>

                                <div class="mb-3">
                                    <label for="subject_code" class="form-label fw-semibold">รหัสวิชา *</label>
                                    <input type="text" class="form-control" id="subject_code" name="subject_code" 
                                           value="{{ old('subject_code') }}" required>
                                    <div class="form-text">รหัสวิชาที่ทำโครงงาน</div>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label for="year" class="form-label fw-semibold">ปีการศึกษา *</label>
                                            <select class="form-select" id="year" name="year" required>
                                                <option value="">เลือกปีการศึกษา</option>
                                                @for($y = 2020; $y <= 2030; $y++)
                                                    <option value="{{ $y }}" {{ old('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label for="semester" class="form-label fw-semibold">ภาคการศึกษา *</label>
                                            <select class="form-select" id="semester" name="semester" required>
                                                <option value="">เลือกภาคการศึกษา</option>
                                                <option value="1" {{ old('semester') == '1' ? 'selected' : '' }}>ภาคต้น</option>
                                                <option value="2" {{ old('semester') == '2' ? 'selected' : '' }}>ภาคปลาย</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label fw-semibold">คำอธิบายโครงงาน</label>
                                    <textarea class="form-control" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                                    <div class="form-text">อธิบายรายละเอียดของโครงงาน (ไม่บังคับ)</div>
                                </div>
                            </div>

                            <!-- Member Invitation -->
                            <div class="col-md-6">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-user-plus me-2"></i>เชิญสมาชิก
                                </h5>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>หมายเหตุ:</strong> 
                                    <ul class="mb-0 mt-2">
                                        <li>กลุ่มสามารถมีสมาชิกได้สูงสุด 2 คน</li>
                                        <li>คุณจะเป็นสมาชิกคนแรกโดยอัตโนมัติ</li>
                                        <li>การเชิญสมาชิกไม่ได้บังคับ (สามารถทำงานคนเดียวได้)</li>
                                        <li>สมาชิกที่ถูกเชิญจะได้รับแจ้งเตือนเพื่อตอบรับ</li>
                                    </ul>
                                </div>

                                <div class="mb-3">
                                    <label for="invite_username" class="form-label fw-semibold">เชิญสมาชิกคนที่ 2</label>
                                    <select class="form-select" id="invite_username" name="invite_username">
                                        <option value="">เลือกนักศึกษาที่จะเชิญ (ไม่บังคับ)</option>
                                    </select>
                                    <div class="form-text">เลือกนักศึกษาที่ยังไม่มีกลุ่ม</div>
                                </div>

                                <div class="mb-3" id="selected-member" style="display: none;">
                                    <div class="card border-primary">
                                        <div class="card-body">
                                            <h6 class="card-title">สมาชิกที่เลือก</h6>
                                            <p class="card-text" id="member-info"></p>
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle me-1"></i>
                                                นักศึกษาที่ถูกเชิญจะต้องตอบรับเพื่อเข้าร่วมกลุ่ม
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Current User Info -->
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary">
                                            <i class="fas fa-user me-1"></i>สมาชิกคนที่ 1 (คุณ)
                                        </h6>
                                        <p class="card-text mb-0">
                                            <strong>{{ Auth::guard('student')->user()->full_name }}</strong><br>
                                            <small class="text-muted">{{ Auth::guard('student')->user()->username_std }}</small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('student.menu') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>ยกเลิก
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>สร้างกลุ่มโครงงาน
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
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
                }
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
                var projectName = $('#project_name').val().trim();
                var projectCode = $('#project_code').val().trim();
                var subjectCode = $('#subject_code').val().trim();
                var year = $('#year').val();
                var semester = $('#semester').val();

                if (!projectName || !projectCode || !subjectCode || !year || !semester) {
                    e.preventDefault();
                    alert('กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน');
                    return false;
                }
            });
        });
    </script>
</body>
</html>
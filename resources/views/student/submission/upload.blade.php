@extends('layouts.student')

@section('title', 'ส่งเล่มรายงาน')

@section('content')
<div class="container py-4">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('student.menu') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>กลับไปหน้าเมนู
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-file-earmark-pdf me-2"></i>ส่งเล่มรายงานโครงงาน
                    </h4>
                </div>

                <div class="card-body">
                    @if(!$isGroupLeader)
                    <!-- แจ้งเตือนสมาชิกที่ไม่ใช่หัวหน้า -->
                    <div class="alert alert-warning">
                        <h5><i class="bi bi-info-circle-fill me-2"></i>สำหรับสมาชิกกลุ่ม</h5>
                        <hr>
                        <p class="mb-0">คุณสามารถดูและดาวน์โหลดเล่มรายงานได้ แต่เฉพาะหัวหน้ากลุ่มเท่านั้นที่สามารถส่งหรืออัพโหลดเล่มรายงานได้</p>
                    </div>
                    @endif

                    @if($project->submission_file)
                    <!-- แสดงไฟล์ที่ส่งแล้ว -->
                    <div class="alert alert-success">
                        <h5><i class="bi bi-check-circle-fill me-2"></i>ส่งเล่มรายงานแล้ว</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>ชื่อไฟล์:</strong><br>
                                {{ $project->submission_original_name }}
                            </div>
                            <div class="col-md-6">
                                <strong>ส่งเมื่อ:</strong><br>
                                {{ $project->submitted_at->format('d/m/Y H:i') }} น.
                            </div>
                            <div class="col-md-6 mt-2">
                                <strong>ส่งโดย:</strong><br>
                                {{ $project->submittedBy->firstname_std ?? '' }} {{ $project->submittedBy->lastname_std ?? '' }}
                                ({{ $project->submitted_by }})
                            </div>
                            <div class="col-12 mt-3">
                                <a href="{{ route('student.submission.download', $project->project_id) }}" 
                                   class="btn btn-primary">
                                    <i class="bi bi-download me-2"></i>ดาวน์โหลดไฟล์
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>หมายเหตุ:</strong> หากต้องการส่งไฟล์ใหม่ ไฟล์เดิมจะถูกแทนที่
                    </div>
                    @else
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>คำแนะนำ:</strong> กรุณาส่งเล่มรายงานในรูปแบบไฟล์ PDF เท่านั้น
                    </div>
                    @endif

                    <!-- ข้อมูลโครงงาน -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="text-primary mb-3">ข้อมูลโครงงาน</h6>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <strong>รหัสโครงงาน:</strong><br>
                                    {{ $project->project_code }}
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong>ชื่อโครงงาน:</strong><br>
                                    {{ $project->project_name ?? '-' }}
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong>อาจารย์ที่ปรึกษา:</strong><br>
                                    @if($project->advisor)
                                        {{ $project->advisor->firstname_user }} {{ $project->advisor->lastname_user }}
                                    @else
                                        -
                                    @endif
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong>สถานะ:</strong><br>
                                    <span class="badge bg-{{ $project->status_project === 'submitted' ? 'success' : 'warning' }}">
                                        {{ $project->status_project }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Upload -->
                    @if($isGroupLeader)
                    <form action="{{ route('student.submission.upload', $project->project_id) }}" 
                          method="POST" 
                          enctype="multipart/form-data"
                          id="uploadForm">
                        @csrf

                        <div class="mb-3">
                            <label for="report_file" class="form-label">
                                เลือกไฟล์เล่มรายงาน <span class="text-danger">*</span>
                            </label>
                            <input type="file" 
                                   class="form-control @error('report_file') is-invalid @enderror" 
                                   id="report_file" 
                                   name="report_file" 
                                   accept=".pdf"
                                   required>
                            @error('report_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                รูปแบบไฟล์: PDF เท่านั้น | ขนาดไม่เกิน 50 MB
                            </div>
                        </div>

                        <!-- แสดงชื่อไฟล์ที่เลือก -->
                        <div id="fileInfo" class="alert alert-secondary d-none">
                            <strong>ไฟล์ที่เลือก:</strong> <span id="fileName"></span><br>
                            <strong>ขนาด:</strong> <span id="fileSize"></span>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="bi bi-cloud-upload me-2"></i>{{ $project->submission_file ? 'อัพโหลดไฟล์ใหม่' : 'ส่งเล่มรายงาน' }}
                            </button>
                        </div>
                    </form>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-lock text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">เฉพาะหัวหน้ากลุ่มเท่านั้นที่สามารถส่งเล่มรายงานได้</p>
                    </div>
                    @endif

                    <!-- กำหนดรูปแบบชื่อไฟล์ -->
                    <div class="card bg-light mt-4">
                        <div class="card-body">
                            <h6 class="text-primary mb-2">
                                <i class="bi bi-file-text me-2"></i>รูปแบบชื่อไฟล์ที่บันทึก
                            </h6>
                            <p class="mb-0 font-monospace">
                                {{ $project->project_code }}_{{ date('Ymd') }}.pdf
                            </p>
                            <small class="text-muted">
                                ระบบจะเปลี่ยนชื่อไฟล์อัตโนมัติเพื่อความเป็นระเบียบ
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// แสดงข้อมูลไฟล์ที่เลือก
document.getElementById('report_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    
    if (file) {
        // แสดงชื่อไฟล์
        fileName.textContent = file.name;
        
        // แสดงขนาดไฟล์
        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        fileSize.textContent = sizeMB + ' MB';
        
        // แสดง alert
        fileInfo.classList.remove('d-none');
        
        // ตรวจสอบขนาดไฟล์
        if (file.size > 50 * 1024 * 1024) {
            alert('ไฟล์มีขนาดใหญ่เกิน 50 MB กรุณาเลือกไฟล์ใหม่');
            e.target.value = '';
            fileInfo.classList.add('d-none');
        }
        
        // ตรวจสอบประเภทไฟล์
        if (file.type !== 'application/pdf') {
            alert('กรุณาเลือกไฟล์ PDF เท่านั้น');
            e.target.value = '';
            fileInfo.classList.add('d-none');
        }
    } else {
        fileInfo.classList.add('d-none');
    }
});

// ยืนยันก่อนส่ง
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    const file = document.getElementById('report_file').files[0];
    
    if (!file) {
        e.preventDefault();
        alert('กรุณาเลือกไฟล์ก่อนส่ง');
        return false;
    }
    
    if (!confirm('ยืนยันการส่งเล่มรายงาน?\n\nไฟล์: ' + file.name + '\nขนาด: ' + (file.size / (1024 * 1024)).toFixed(2) + ' MB')) {
        e.preventDefault();
        return false;
    }
    
    // แสดง loading
    document.getElementById('submitBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>กำลังอัพโหลด...';
    document.getElementById('submitBtn').disabled = true;
});
</script>
@endpush
@endsection

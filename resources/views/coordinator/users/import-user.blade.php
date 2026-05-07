@extends('layouts.app')

@section('title', 'Import ผู้ใช้งาน | CSTU SPACE')

@section('content')
<div class="container">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-file-earmark-arrow-up me-2"></i>
                    Import ผู้ใช้จาก CSV
                </h2>
                <p class="mb-0 opacity-75">นำเข้าข้อมูลผู้ใช้งานระบบจากไฟล์ CSV</p>
            </div>
            <a href="{{ route('coordinator.users.index') }}" class="btn modern-btn btn-light">
                <i class="bi bi-arrow-left"></i>
                <span>กลับ</span>
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="modern-card form-card">
                <div class="form-card-body">

                    <div class="alert alert-warning alert-modern mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>หมายเหตุ:</strong> Coordinator ไม่สามารถกำหนด Role ได้
                        ผู้ใช้ที่ Import เข้ามาจะถูกกำหนดเป็น <strong>Lecturer</strong> โดยอัตโนมัติ
                    </div>

                    <div class="alert alert-info alert-modern mb-4">
                        <h5 class="mb-2"><i class="bi bi-info-circle me-1"></i> คำแนะนำการใช้งาน</h5>
                        <ol class="mb-0">
                            <li>ไฟล์ CSV ต้องมี header แถวแรก</li>
                            <li>คอลัมน์ต้องเรียงตามลำดับ: <strong>username, firstname, lastname, email, user_code</strong></li>
                            <li>Password เริ่มต้นจะถูกกำหนดเป็น <code>password123</code> และถูก hash อัตโนมัติ</li>
                        </ol>
                    </div>

                    <div class="csv-preview mb-4">
                        <div class="csv-preview-header">
                            <i class="bi bi-file-earmark-text me-2"></i>
                            <strong>ตัวอย่างไฟล์ CSV</strong>
                        </div>
                        <div class="csv-preview-body">
                            <pre class="mb-0">username,firstname,lastname,email,user_code
teacher01,สมชาย,ใจดี,somchai@cstu.ac.th,SCH
teacher02,สมหญิง,รักเรียน,somying@cstu.ac.th,SMY</pre>
                        </div>
                    </div>

                    <form action="{{ route('coordinator.users.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label for="file" class="form-label fw-semibold">เลือกไฟล์ CSV <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror"
                                   id="file" name="file" accept=".csv,.txt" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">ประเภทไฟล์: .csv, .txt (ขนาดไม่เกิน 2MB)</small>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('coordinator.users.index') }}" class="btn modern-btn btn-light">
                                <i class="bi bi-arrow-left"></i>
                                <span>ยกเลิก</span>
                            </a>
                            <button type="submit" class="btn modern-btn btn-success-modern">
                                <i class="bi bi-upload"></i>
                                <span>Import ข้อมูล</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-card-body { padding: 2rem; }
    .form-card:hover { transform: none; }

    .csv-preview {
        border-radius: 15px;
        overflow: hidden;
        border: 2px solid #e9ecef;
    }

    .csv-preview-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        color: #2c3e50;
    }

    .csv-preview-body {
        padding: 1rem;
        background: #fdfdfd;
    }

    .csv-preview-body pre {
        font-size: 12px;
        color: #495057;
        margin: 0;
    }
</style>
@endpush

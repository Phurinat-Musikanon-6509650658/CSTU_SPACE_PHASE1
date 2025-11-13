@extends('layouts.app')

@section('title', 'Import Users | CSTU SPACE')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Import ผู้ใช้จาก CSV</h4>
                </div>
                <div class="card-body">
                    <!-- คำแนะนำ -->
                    <div class="alert alert-info">
                        <h5><i class="bi bi-info-circle"></i> คำแนะนำการใช้งาน:</h5>
                        <ol class="mb-2">
                            <li>ไฟล์ CSV ต้องมี header แถวแรก</li>
                            <li>คอลัมน์ต้องเรียงตามลำดับ: <strong>username, firstname, lastname, email, password, role, user_code</strong></li>
                            <li>Role ที่รองรับ: admin, coordinator, advisor (default: advisor)</li>
                            <li>Password จะถูก hash อัตโนมัติ</li>
                        </ol>
                        <p class="mb-0">
                            <a href="{{ route('users.downloadTemplate') }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download"></i> ดาวน์โหลดไฟล์ตัวอย่าง
                            </a>
                        </p>
                    </div>

                    <!-- ตัวอย่าง CSV -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <strong>ตัวอย่างไฟล์ CSV:</strong>
                        </div>
                        <div class="card-body">
                            <pre class="mb-0" style="font-size: 12px;">username,firstname,lastname,email,password,role,user_code
teacher01,สมชาย,ใจดี,somchai@cstu.ac.th,pass1234,advisor,SCH
teacher02,สมหญิง,รักเรียน,somying@cstu.ac.th,pass5678,coordinator,SMY</pre>
                        </div>
                    </div>

                    <!-- Form Upload -->
                    <form action="{{ route('users.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="csv_file" class="form-label">เลือกไฟล์ CSV <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('csv_file') is-invalid @enderror" 
                                   id="csv_file" name="csv_file" accept=".csv,.txt" required>
                            @error('csv_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">ประเภทไฟล์: .csv, .txt (ขนาดไม่เกิน 2MB)</small>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> ยกเลิก
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-upload"></i> Import ข้อมูล
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@endpush

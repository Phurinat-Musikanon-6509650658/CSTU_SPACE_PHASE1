@extends('layouts.app')

@section('title', 'Import นักศึกษา | CSTU SPACE')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Import นักศึกษาจาก CSV</h4>
                </div>
                <div class="card-body">
                    <!-- คำแนะนำ -->
                    <div class="alert alert-info">
                        <h5><i class="bi bi-info-circle"></i> คำแนะนำการใช้งาน:</h5>
                        <ol class="mb-2">
                            <li>ไฟล์ CSV ต้องมี header แถวแรก</li>
                            <li>คอลัมน์ต้องเรียงตามลำดับ: <strong>username, firstname, lastname, email, password, course_code, semester, year</strong></li>
                            <li>Password จะถูก hash อัตโนมัติ</li>
                            <li>course_code: CS303 หรือ CS403</li>
                            <li>semester: 1 หรือ 2</li>
                            <li>year: ปีการศึกษา (พ.ศ.) เช่น 2568</li>
                        </ol>
                    </div>

                    <!-- ตัวอย่าง CSV -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <strong>ตัวอย่างไฟล์ CSV:</strong>
                        </div>
                        <div class="card-body">
                            <pre class="mb-0" style="font-size: 12px;">username,firstname,lastname,email,password,course_code,semester,year
6509650099,ทดสอบ,ระบบ,test.std@dome.tu.ac.th,testpass123,CS303,2,2568
6509650098,ตัวอย่าง,นักศึกษา,example.std@dome.tu.ac.th,examplepass456,CS403,1,2568</pre>
                        </div>
                    </div>

                    <!-- Form Upload -->
                    <form action="{{ route('coordinator.students.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="file" class="form-label">เลือกไฟล์ CSV <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                   id="file" name="file" accept=".csv,.txt" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">ประเภทไฟล์: .csv, .txt (ขนาดไม่เกิน 2MB)</small>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('coordinator.users.index') }}" class="btn btn-secondary">
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

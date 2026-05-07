@extends('layouts.app')

@section('title', 'Edit Student | CSTU SPACE')

@section('content')
<div class="container">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-pencil-square me-2"></i>
                    แก้ไขนักศึกษา
                </h2>
                <p class="mb-0 opacity-75">{{ $student->firstname_std }} {{ $student->lastname_std }}</p>
            </div>
            <a href="{{ route('users.index') }}?tab=students" class="btn modern-btn btn-light">
                <i class="bi bi-arrow-left"></i>
                <span>กลับ</span>
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="modern-card form-card">
                <div class="form-card-body">
                    <form action="{{ route('students.update', $student->student_id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="username_std" class="form-label fw-semibold">รหัสนักศึกษา</label>
                            <input type="text" class="form-control" id="username_std" value="{{ $student->username_std }}" disabled>
                            <small class="text-muted">รหัสนักศึกษาไม่สามารถแก้ไขได้</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstname_std" class="form-label fw-semibold">ชื่อ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('firstname_std') is-invalid @enderror"
                                       id="firstname_std" name="firstname_std"
                                       value="{{ old('firstname_std', $student->firstname_std) }}" required>
                                @error('firstname_std')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="lastname_std" class="form-label fw-semibold">นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('lastname_std') is-invalid @enderror"
                                       id="lastname_std" name="lastname_std"
                                       value="{{ old('lastname_std', $student->lastname_std) }}" required>
                                @error('lastname_std')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email_std" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email_std') is-invalid @enderror"
                                   id="email_std" name="email_std"
                                   value="{{ old('email_std', $student->email_std) }}" required>
                            @error('email_std')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="course_code" class="form-label fw-semibold">รหัสวิชา <span class="text-danger">*</span></label>
                                <select class="form-select @error('course_code') is-invalid @enderror"
                                        id="course_code" name="course_code" required>
                                    <option value="">เลือกรหัสวิชา</option>
                                    <option value="CS303" {{ old('course_code', $student->course_code) == 'CS303' ? 'selected' : '' }}>CS303 - โครงงานพิเศษ 1</option>
                                    <option value="CS403" {{ old('course_code', $student->course_code) == 'CS403' ? 'selected' : '' }}>CS403 - โครงงานพิเศษ 2</option>
                                </select>
                                @error('course_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="semester" class="form-label fw-semibold">เทอม <span class="text-danger">*</span></label>
                                <select class="form-select @error('semester') is-invalid @enderror"
                                        id="semester" name="semester" required>
                                    <option value="">เลือกเทอม</option>
                                    <option value="1" {{ old('semester', $student->semester) == 1 ? 'selected' : '' }}>1</option>
                                    <option value="2" {{ old('semester', $student->semester) == 2 ? 'selected' : '' }}>2</option>
                                </select>
                                @error('semester')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="year" class="form-label fw-semibold">ปีการศึกษา (พ.ศ.) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('year') is-invalid @enderror"
                                       id="year" name="year"
                                       value="{{ old('year', $student->year) }}"
                                       min="2560" max="2600" required>
                                @error('year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password_std" class="form-label fw-semibold">Password ใหม่</label>
                            <input type="password" class="form-control @error('password_std') is-invalid @enderror"
                                   id="password_std" name="password_std">
                            <small class="text-muted">ใส่เฉพาะเมื่อต้องการเปลี่ยนรหัสผ่าน (อย่างน้อย 6 ตัวอักษร)</small>
                            @error('password_std')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('users.index') }}?tab=students" class="btn modern-btn btn-light">
                                <i class="bi bi-arrow-left"></i>
                                <span>ยกเลิก</span>
                            </a>
                            <button type="submit" class="btn modern-btn btn-warning-modern">
                                <i class="bi bi-save"></i>
                                <span>บันทึกการแก้ไข</span>
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

    .form-select {
        border-radius: 15px;
        border: 2px solid #e9ecef;
        padding: 0.75rem 1rem;
        transition: var(--transition);
    }

    .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
</style>
@endpush

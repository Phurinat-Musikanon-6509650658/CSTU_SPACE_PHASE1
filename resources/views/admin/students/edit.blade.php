@extends('layouts.app')

@section('title', 'Edit Student | CSTU SPACE')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">แก้ไขนักศึกษา</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('students.update', $student->student_id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="username_std" class="form-label">รหัสนักศึกษา</label>
                            <input type="text" class="form-control" id="username_std" value="{{ $student->username_std }}" disabled>
                            <small class="text-muted">รหัสนักศึกษาไม่สามารถแก้ไขได้</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstname_std" class="form-label">ชื่อ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('firstname_std') is-invalid @enderror" 
                                       id="firstname_std" name="firstname_std" 
                                       value="{{ old('firstname_std', $student->firstname_std) }}" required>
                                @error('firstname_std')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="lastname_std" class="form-label">นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('lastname_std') is-invalid @enderror" 
                                       id="lastname_std" name="lastname_std" 
                                       value="{{ old('lastname_std', $student->lastname_std) }}" required>
                                @error('lastname_std')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email_std" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email_std') is-invalid @enderror" 
                                   id="email_std" name="email_std" 
                                   value="{{ old('email_std', $student->email_std) }}" required>
                            @error('email_std')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="course_code" class="form-label">รหัสวิชา <span class="text-danger">*</span></label>
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
                                <label for="semester" class="form-label">เทอม <span class="text-danger">*</span></label>
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
                                <label for="year" class="form-label">ปีการศึกษา (พ.ศ.) <span class="text-danger">*</span></label>
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
                            <label for="password_std" class="form-label">Password ใหม่</label>
                            <input type="password" class="form-control @error('password_std') is-invalid @enderror" 
                                   id="password_std" name="password_std">
                            <small class="text-muted">ใส่เฉพาะเมื่อต้องการเปลี่ยนรหัสผ่าน (อย่างน้อย 6 ตัวอักษร)</small>
                            @error('password_std')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> ยกเลิก
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-save"></i> บันทึกการแก้ไข
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

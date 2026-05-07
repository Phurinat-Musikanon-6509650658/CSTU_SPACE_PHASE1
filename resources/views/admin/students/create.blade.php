@extends('layouts.app')

@section('title', 'Create Student | CSTU SPACE')

@section('content')
<div class="container">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-mortarboard-fill me-2"></i>
                    เพิ่มนักศึกษาใหม่
                </h2>
                <p class="mb-0 opacity-75">เพิ่มข้อมูลนักศึกษาในระบบ</p>
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
                    <form action="{{ route('students.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="username_std" class="form-label fw-semibold">รหัสนักศึกษา <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('username_std') is-invalid @enderror"
                                   id="username_std" name="username_std" value="{{ old('username_std') }}" required>
                            @error('username_std')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstname_std" class="form-label fw-semibold">ชื่อ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('firstname_std') is-invalid @enderror"
                                       id="firstname_std" name="firstname_std" value="{{ old('firstname_std') }}" required>
                                @error('firstname_std')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="lastname_std" class="form-label fw-semibold">นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('lastname_std') is-invalid @enderror"
                                       id="lastname_std" name="lastname_std" value="{{ old('lastname_std') }}" required>
                                @error('lastname_std')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email_std" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email_std') is-invalid @enderror"
                                   id="email_std" name="email_std" value="{{ old('email_std') }}" required>
                            @error('email_std')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_std" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password_std') is-invalid @enderror"
                                   id="password_std" name="password_std" required>
                            <small class="text-muted">รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร</small>
                            @error('password_std')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('users.index') }}?tab=students" class="btn modern-btn btn-light">
                                <i class="bi bi-arrow-left"></i>
                                <span>ยกเลิก</span>
                            </a>
                            <button type="submit" class="btn modern-btn btn-primary-modern">
                                <i class="bi bi-save"></i>
                                <span>บันทึก</span>
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
</style>
@endpush

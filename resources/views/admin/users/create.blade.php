@extends('layouts.app')

@section('title', 'Create User | CSTU SPACE')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">เพิ่มผู้ใช้ใหม่</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="username_user" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('username_user') is-invalid @enderror" 
                                   id="username_user" name="username_user" value="{{ old('username_user') }}" required>
                            @error('username_user')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstname_user" class="form-label">ชื่อ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('firstname_user') is-invalid @enderror" 
                                       id="firstname_user" name="firstname_user" value="{{ old('firstname_user') }}" required>
                                @error('firstname_user')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="lastname_user" class="form-label">นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('lastname_user') is-invalid @enderror" 
                                       id="lastname_user" name="lastname_user" value="{{ old('lastname_user') }}" required>
                                @error('lastname_user')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email_user" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email_user') is-invalid @enderror" 
                                   id="email_user" name="email_user" value="{{ old('email_user') }}" required>
                            @error('email_user')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_user" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password_user') is-invalid @enderror" 
                                   id="password_user" name="password_user" required>
                            <small class="text-muted">รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร</small>
                            @error('password_user')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                    <option value="">-- เลือก Role --</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->role }}" {{ old('role') == $role->role ? 'selected' : '' }}>
                                            {{ ucfirst($role->role) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="user_code" class="form-label">User Code</label>
                                <input type="text" class="form-control" id="user_code" name="user_code" value="{{ old('user_code') }}">
                                <small class="text-muted">รหัสย่อของผู้ใช้ (ถ้ามี)</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> ยกเลิก
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> บันทึก
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

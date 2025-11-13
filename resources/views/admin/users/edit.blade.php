@extends('layouts.app')

@section('title', 'Edit User | CSTU SPACE')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">แก้ไขผู้ใช้</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.update', $user->user_id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="username_user" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username_user" value="{{ $user->username_user }}" disabled>
                            <small class="text-muted">Username ไม่สามารถแก้ไขได้</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstname_user" class="form-label">ชื่อ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('firstname_user') is-invalid @enderror" 
                                       id="firstname_user" name="firstname_user" 
                                       value="{{ old('firstname_user', $user->firstname_user) }}" required>
                                @error('firstname_user')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="lastname_user" class="form-label">นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('lastname_user') is-invalid @enderror" 
                                       id="lastname_user" name="lastname_user" 
                                       value="{{ old('lastname_user', $user->lastname_user) }}" required>
                                @error('lastname_user')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email_user" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email_user') is-invalid @enderror" 
                                   id="email_user" name="email_user" 
                                   value="{{ old('email_user', $user->email_user) }}" required>
                            @error('email_user')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_user" class="form-label">Password ใหม่</label>
                            <input type="password" class="form-control @error('password_user') is-invalid @enderror" 
                                   id="password_user" name="password_user">
                            <small class="text-muted">ใส่เฉพาะเมื่อต้องการเปลี่ยนรหัสผ่าน (อย่างน้อย 6 ตัวอักษร)</small>
                            @error('password_user')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                    <option value="">-- เลือก Role --</option>
                                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="coordinator" {{ old('role', $user->role) == 'coordinator' ? 'selected' : '' }}>Coordinator</option>
                                    <option value="advisor" {{ old('role', $user->role) == 'advisor' ? 'selected' : '' }}>Advisor</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="user_code" class="form-label">User Code</label>
                                <input type="text" class="form-control" id="user_code" name="user_code" 
                                       value="{{ old('user_code', $user->user_code) }}">
                                <small class="text-muted">รหัสย่อของผู้ใช้ (ถ้ามี)</small>
                            </div>
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

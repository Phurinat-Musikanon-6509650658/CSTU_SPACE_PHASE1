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

                        <div class="mb-3">
                            <label class="form-label">Roles & Permissions <span class="text-danger">*</span></label>
                            <div class="card">
                                <div class="card-body">
                                    <p class="text-muted small mb-3">
                                        <i class="bi bi-info-circle"></i> เลือก roles ที่ต้องการให้ user นี้มี (สามารถเลือกได้หลาย roles)
                                    </p>
                                    
                                    <div class="role-checkboxes">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input role-checkbox" type="checkbox" 
                                                   id="role_admin" value="32768">
                                            <label class="form-check-label" for="role_admin">
                                                <span class="role-badge role-admin">
                                                    <i class="bi bi-shield-fill"></i> Admin
                                                </span>
                                                <small class="text-muted ms-2">(32768)</small>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check mb-2">
                                            <input class="form-check-input role-checkbox" type="checkbox" 
                                                   id="role_coordinator" value="16384">
                                            <label class="form-check-label" for="role_coordinator">
                                                <span class="role-badge role-coordinator">
                                                    <i class="bi bi-person-gear"></i> Coordinator
                                                </span>
                                                <small class="text-muted ms-2">(16384)</small>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check mb-2">
                                            <input class="form-check-input role-checkbox" type="checkbox" 
                                                   id="role_lecturer" value="8192">
                                            <label class="form-check-label" for="role_lecturer">
                                                <span class="role-badge role-advisor">
                                                    <i class="bi bi-person-check"></i> Lecturer
                                                </span>
                                                <small class="text-muted ms-2">(8192)</small>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check mb-2">
                                            <input class="form-check-input role-checkbox" type="checkbox" 
                                                   id="role_staff" value="4096">
                                            <label class="form-check-label" for="role_staff">
                                                <span class="role-badge role-staff">
                                                    <i class="bi bi-briefcase"></i> Staff
                                                </span>
                                                <small class="text-muted ms-2">(4096)</small>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check mb-2">
                                            <input class="form-check-input role-checkbox" type="checkbox" 
                                                   id="role_student" value="2048">
                                            <label class="form-check-label" for="role_student">
                                                <span class="role-badge role-student">
                                                    <i class="bi bi-mortarboard"></i> Student
                                                </span>
                                                <small class="text-muted ms-2">(2048)</small>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="role" id="role_combined" value="0" required>
                                    
                                    <div class="mt-3 p-2 bg-light rounded">
                                        <small class="text-muted">
                                            <strong>รวม Role Code:</strong> 
                                            <span id="role_display">0</span>
                                            <span class="ms-2" id="role_binary">(0000000000000000)</span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            @error('role')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="user_code" class="form-label">User Code</label>
                            <input type="text" class="form-control" id="user_code" name="user_code" value="{{ old('user_code') }}">
                            <small class="text-muted">รหัสย่อของผู้ใช้ (ถ้ามี)</small>
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
<style>
    .role-checkboxes {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .role-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    
    .role-admin {
        background: linear-gradient(45deg, #ff6b6b, #ee5a24);
        color: white;
    }
    
    .role-coordinator {
        background: linear-gradient(45deg, #4834d4, #686de0);
        color: white;
    }
    
    .role-advisor {
        background: linear-gradient(45deg, #0abde3, #006ba6);
        color: white;
    }
    
    .role-staff {
        background: linear-gradient(45deg, #f39c12, #e67e22);
        color: white;
    }
    
    .role-student {
        background: linear-gradient(45deg, #55a3ff, #003d82);
        color: white;
    }
    
    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.role-checkbox');
        const hiddenInput = document.getElementById('role_combined');
        const roleDisplay = document.getElementById('role_display');
        const roleBinary = document.getElementById('role_binary');
        
        function updateRoleValue() {
            let totalRole = 0;
            
            // ใช้ Bitwise OR (|) เพื่อรวม roles ที่ถูกเลือก
            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    totalRole |= parseInt(checkbox.value);
                }
            });
            
            // อัพเดท hidden input และ display
            hiddenInput.value = totalRole;
            roleDisplay.textContent = totalRole;
            
            // แสดง binary representation
            const binary = totalRole.toString(2).padStart(16, '0');
            roleBinary.textContent = '(' + binary + ')';
        }
        
        // เพิ่ม event listener ให้กับทุก checkbox
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateRoleValue);
        });
        
        // เรียกครั้งแรกเพื่อ set ค่าเริ่มต้น
        updateRoleValue();
    });
</script>
@endpush

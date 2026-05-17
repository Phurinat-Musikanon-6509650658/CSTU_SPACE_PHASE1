@extends('layouts.app')

@section('title', 'รีเซตรหัสผ่าน | CSTU SPACE')

@section('content')
<div class="login-wrapper">
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
    <div class="bg-shape shape-3"></div>
    <div class="bg-shape shape-4"></div>

    <div class="login-content">
        <div class="text-center mb-4">
            <h1 class="brand-title">CSTU SPACE</h1>
            <p class="brand-subtitle">
                ระบบบริหารและประสานงานโครงงานพิเศษ<br>
                ภาควิชาวิทยาการคอมพิวเตอร์
            </p>
        </div>

        @if($errors->has('verify'))
        <div class="alert login-alert alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>{{ $errors->first('verify') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="login-card">
            <div class="login-card-header">
                <i class="bi bi-shield-lock me-2"></i>รีเซตรหัสผ่าน
            </div>
            <div class="login-card-body">
                <p class="text-muted small mb-4 text-center">
                    กรอกชื่อผู้ใช้และอีเมลที่ลงทะเบียนไว้ในระบบ
                </p>

                <form action="{{ route('password.verify') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="username" class="form-label">ชื่อผู้ใช้</label>
                        <div class="input-wrap">
                            <i class="bi bi-person input-icon"></i>
                            <input type="text" class="custom-input @error('username') is-invalid @enderror"
                                   name="username" id="username"
                                   value="{{ old('username') }}"
                                   placeholder="กรอกชื่อผู้ใช้" required autocomplete="username">
                        </div>
                        @error('username')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label">อีเมล</label>
                        <div class="input-wrap">
                            <i class="bi bi-envelope input-icon"></i>
                            <input type="email" class="custom-input @error('email') is-invalid @enderror"
                                   name="email" id="email"
                                   value="{{ old('email') }}"
                                   placeholder="กรอกอีเมลที่ลงทะเบียน" required autocomplete="email">
                        </div>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="login-btn">
                        <i class="bi bi-arrow-right-circle"></i>
                        <span>ยืนยันตัวตน</span>
                    </button>
                </form>

                <div class="login-footer">
                    <a href="{{ route('login') }}" class="back-link">
                        <i class="bi bi-arrow-left me-1"></i>กลับหน้าเข้าสู่ระบบ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.login-wrapper {
    position: fixed; inset: 0; z-index: 9999;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    display: flex; align-items: center; justify-content: center;
    padding: 2rem 1rem; overflow-y: auto;
}
.login-content {
    width: 100%; max-width: 420px; position: relative; z-index: 10;
    animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) both;
}
.bg-shape {
    position: fixed; border-radius: 50%; pointer-events: none;
    animation: float 10s ease-in-out infinite;
}
.shape-1 { width: 320px; height: 320px; top: -80px; left: -80px; background: rgba(102,126,234,.12); animation-delay: 0s; }
.shape-2 { width: 220px; height: 220px; bottom: 5%; right: -60px; background: rgba(118,75,162,.1); animation-delay: 3s; }
.shape-3 { width: 140px; height: 140px; top: 40%; left: 3%; background: rgba(79,172,254,.1); animation-delay: 6s; }
.shape-4 { width: 90px; height: 90px; top: 15%; right: 8%; background: rgba(240,147,251,.1); animation-delay: 1.5s; }
.brand-title {
    font-size: 2.8rem; font-weight: 800; letter-spacing: -1.5px; margin-bottom: .5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.brand-subtitle { color: #6c757d; font-size: .9rem; line-height: 1.7; margin-bottom: 0; }
.login-alert {
    background: rgba(220,53,69,.08); border: none; border-left: 4px solid #dc3545;
    border-radius: 12px; color: #842029; box-shadow: 0 4px 15px rgba(220,53,69,.12); font-size: .9rem;
}
.login-card {
    background: rgba(255,255,255,.97); backdrop-filter: blur(20px);
    border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,.1), 0 4px 16px rgba(102,126,234,.08);
    overflow: hidden; position: relative;
}
.login-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.login-card-header {
    padding: 1.5rem 2rem 1.25rem; border-bottom: 1px solid #f0f0f0;
    text-align: center; font-size: 1.15rem; font-weight: 600; color: #495057;
}
.login-card-body { padding: 2rem; }
.form-label { font-weight: 600; font-size: .875rem; color: #495057; margin-bottom: .5rem; display: block; }
.input-wrap { position: relative; display: flex; align-items: center; }
.input-icon {
    position: absolute; left: .9rem; font-size: 1rem; color: #adb5bd;
    z-index: 2; transition: color .25s; pointer-events: none;
}
.input-wrap:focus-within .input-icon { color: #667eea; }
.custom-input {
    width: 100%; padding: .85rem 1rem .85rem 2.6rem; border: 2px solid #e9ecef;
    border-radius: 12px; font-size: 1rem; color: #212529; background: #fff;
    transition: border-color .25s, box-shadow .25s; outline: none;
}
.custom-input:focus { border-color: #667eea; box-shadow: 0 0 0 .2rem rgba(102,126,234,.15); }
.custom-input::placeholder { color: #ced4da; }
.login-btn {
    width: 100%; padding: .875rem 1rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none; border-radius: 12px; color: white; font-size: 1rem; font-weight: 600;
    cursor: pointer; display: flex; align-items: center; justify-content: center; gap: .5rem;
    position: relative; overflow: hidden; transition: transform .25s, box-shadow .25s;
}
.login-btn::after {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.2), transparent);
    transform: translateX(-100%); transition: transform .5s;
}
.login-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(102,126,234,.4); }
.login-btn:hover::after { transform: translateX(100%); }
.login-btn:active { transform: translateY(0); box-shadow: none; }
.login-footer {
    margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid #f0f0f0;
    text-align: center; font-size: .85rem;
}
.back-link { color: #667eea; text-decoration: none; transition: color .2s; }
.back-link:hover { color: #764ba2; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(28px); } to { opacity: 1; transform: translateY(0); } }
@keyframes float { 0%, 100% { transform: translateY(0) scale(1); } 50% { transform: translateY(-22px) scale(1.04); } }
@media (max-width: 576px) { .brand-title { font-size: 2.2rem; } .login-card-body { padding: 1.5rem; } }
</style>
@endpush
@endsection

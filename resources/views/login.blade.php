@extends('layouts.app')

@section('title', 'เข้าสู่ระบบ | CSTU SPACE')

@section('content')
<div class="login-wrapper">
    {{-- Decorative background shapes --}}
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
    <div class="bg-shape shape-3"></div>
    <div class="bg-shape shape-4"></div>

    <div class="login-content">
        {{-- Brand Header --}}
        <div class="text-center mb-4">
            <h1 class="brand-title">CSTU SPACE</h1>
            <p class="brand-subtitle">
                ระบบบริหารและประสานงานโครงงานพิเศษ<br>
                ภาควิชาวิทยาการคอมพิวเตอร์
            </p>
        </div>

        {{-- Error Alerts --}}
        @if(session('success_message'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert"
             style="border-radius:12px; border:none; border-left:4px solid #198754;">
            <i class="bi bi-check-circle-fill me-2"></i>
            <strong>{{ session('success_message') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('login_error_message'))
        <div class="alert login-alert alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>{{ session('login_error_message') }}</strong>
            @if(session('login_error_description'))
                <div class="mt-1 small">{{ session('login_error_description') }}</div>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($errors->has('login_error'))
        <div class="alert login-alert mb-3">
            <i class="bi bi-exclamation-circle me-2"></i>
            {{ $errors->first('login_error') }}
        </div>
        @endif

        {{-- Login Card --}}
        <div class="login-card">
            <div class="login-card-header">
                <i class="bi bi-person-circle me-2"></i>เข้าสู่ระบบ
            </div>
            <div class="login-card-body">
                <form action="{{ url('login') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="username" class="form-label">ชื่อผู้ใช้</label>
                        <div class="input-wrap">
                            <i class="bi bi-person input-icon"></i>
                            <input type="text" class="custom-input" name="username" id="username"
                                   placeholder="กรอกชื่อผู้ใช้" required autocomplete="username">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">รหัสผ่าน</label>
                        <div class="input-wrap">
                            <i class="bi bi-lock input-icon"></i>
                            <input type="password" class="custom-input" name="password" id="password"
                                   placeholder="กรอกรหัสผ่าน" required autocomplete="current-password">
                            <button type="button" class="toggle-btn" onclick="togglePassword()" tabindex="-1">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="login-btn">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span>เข้าสู่ระบบ</span>
                    </button>
                </form>

                <div class="login-footer">
                    <a href="{{ route('password.reset') }}" class="forgot-link">
                        <i class="bi bi-key me-1"></i>ลืมรหัสผ่าน?
                    </a>
                    <span class="mx-2 text-muted">|</span>
                    <i class="bi bi-shield-check me-1"></i>
                    ระบบปลอดภัย | ภาควิชาวิทยาการคอมพิวเตอร์ มหาวิทยาลัยธรรมศาสตร์
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* ── Escape the container mt-5 wrapper from layouts.app ─────────── */
.login-wrapper {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    overflow-y: auto;
}

/* ── Content container ───────────────────────────────────────────── */
.login-content {
    width: 100%;
    max-width: 420px;
    position: relative;
    z-index: 10;
    animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) both;
}

/* ── Background decorative shapes ───────────────────────────────── */
.bg-shape {
    position: fixed;
    border-radius: 50%;
    pointer-events: none;
    animation: float 10s ease-in-out infinite;
}
.shape-1 {
    width: 320px; height: 320px;
    top: -80px; left: -80px;
    background: rgba(102, 126, 234, 0.12);
    animation-delay: 0s;
}
.shape-2 {
    width: 220px; height: 220px;
    bottom: 5%; right: -60px;
    background: rgba(118, 75, 162, 0.1);
    animation-delay: 3s;
}
.shape-3 {
    width: 140px; height: 140px;
    top: 40%; left: 3%;
    background: rgba(79, 172, 254, 0.1);
    animation-delay: 6s;
}
.shape-4 {
    width: 90px; height: 90px;
    top: 15%; right: 8%;
    background: rgba(240, 147, 251, 0.1);
    animation-delay: 1.5s;
}

/* ── Brand ───────────────────────────────────────────────────────── */
.brand-title {
    font-size: 2.8rem;
    font-weight: 800;
    letter-spacing: -1.5px;
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.brand-subtitle {
    color: #6c757d;
    font-size: 0.9rem;
    line-height: 1.7;
    margin-bottom: 0;
}

/* ── Alert ───────────────────────────────────────────────────────── */
.login-alert {
    background: rgba(220, 53, 69, 0.08);
    border: none;
    border-left: 4px solid #dc3545;
    border-radius: 12px;
    color: #842029;
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.12);
    font-size: 0.9rem;
}

/* ── Login Card ──────────────────────────────────────────────────── */
.login-card {
    background: rgba(255, 255, 255, 0.97);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1), 0 4px 16px rgba(102, 126, 234, 0.08);
    overflow: hidden;
    position: relative;
}
.login-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.login-card-header {
    padding: 1.5rem 2rem 1.25rem;
    border-bottom: 1px solid #f0f0f0;
    text-align: center;
    font-size: 1.15rem;
    font-weight: 600;
    color: #495057;
}
.login-card-body {
    padding: 2rem;
}

/* ── Form Labels ─────────────────────────────────────────────────── */
.form-label {
    font-weight: 600;
    font-size: 0.875rem;
    color: #495057;
    margin-bottom: 0.5rem;
    display: block;
}

/* ── Input Fields ────────────────────────────────────────────────── */
.input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.input-icon {
    position: absolute;
    left: 0.9rem;
    font-size: 1rem;
    color: #adb5bd;
    z-index: 2;
    transition: color 0.25s;
    pointer-events: none;
}
.input-wrap:focus-within .input-icon {
    color: #667eea;
}
.custom-input {
    width: 100%;
    padding: 0.85rem 2.8rem 0.85rem 2.6rem;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 1rem;
    color: #212529;
    background: #fff;
    transition: border-color 0.25s, box-shadow 0.25s;
    outline: none;
}
.custom-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
}
.custom-input::placeholder { color: #ced4da; }

.toggle-btn {
    position: absolute;
    right: 0.8rem;
    background: none;
    border: none;
    color: #adb5bd;
    cursor: pointer;
    padding: 0.25rem;
    font-size: 1rem;
    z-index: 2;
    line-height: 1;
    transition: color 0.25s;
}
.toggle-btn:hover { color: #495057; }

/* ── Submit Button ───────────────────────────────────────────────── */
.login-btn {
    width: 100%;
    padding: 0.875rem 1rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 12px;
    color: white;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    position: relative;
    overflow: hidden;
    transition: transform 0.25s, box-shadow 0.25s;
}
.login-btn::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transform: translateX(-100%);
    transition: transform 0.5s;
}
.login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(102, 126, 234, 0.4);
}
.login-btn:hover::after { transform: translateX(100%); }
.login-btn:active        { transform: translateY(0); box-shadow: none; }

/* ── Card Footer ─────────────────────────────────────────────────── */
.login-footer {
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 1px solid #f0f0f0;
    text-align: center;
    font-size: 0.82rem;
    color: #adb5bd;
}
.forgot-link {
    color: #667eea;
    text-decoration: none;
    transition: color .2s;
}
.forgot-link:hover { color: #764ba2; }

/* ── Animations ──────────────────────────────────────────────────── */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(28px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes float {
    0%, 100% { transform: translateY(0) scale(1); }
    50%       { transform: translateY(-22px) scale(1.04); }
}

/* ── Responsive ──────────────────────────────────────────────────── */
@media (max-width: 576px) {
    .brand-title     { font-size: 2.2rem; }
    .login-card-body { padding: 1.5rem; }
}
</style>
@endpush

@push('scripts')
<script>
function togglePassword() {
    const field = document.getElementById('password');
    const icon  = document.getElementById('toggleIcon');
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>
@endpush
@endsection

@extends('layouts.app')

@section('title', 'เข้าสู่ระบบ | CSTU SPACE')

@section('content')
<div class="login-container">
    <div class="login-decoration">
        <div class="decoration-circle circle-1"></div>
        <div class="decoration-circle circle-2"></div>
        <div class="decoration-circle circle-3"></div>
        <div class="decoration-circle circle-4"></div>
    </div>
    
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5 col-lg-4">
            <!-- Brand Header -->
            <div class="brand-header mb-4">
                <h1 class="brand-title">CSTU SPACE</h1>
                <p class="brand-subtitle">ระบบบริหารและประสานงานโครงงานพิเศษ ภาควิชาวิทยาการคอมพิวเตอร์
</p>
            </div>
            
            <!-- Error Messages -->
            @if(session('login_error_message'))
                <div class="alert alert-danger alert-dismissible fade show custom-alert" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>{{ session('login_error_message') }}</strong>
                    @if(session('login_error_description'))
                        <div class="mt-1">{{ session('login_error_description') }}</div>
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <!-- Login Card -->
            <div class="login-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="bi bi-person-circle me-2"></i>เข้าสู่ระบบ
                    </h5>
                </div>
                
                <div class="card-body">
                    @if ($errors->has('login_error'))
                        <div class="alert alert-danger custom-alert">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            {{ $errors->first('login_error') }}
                        </div>
                    @endif

                    <form action="{{ url('login') }}" method="POST">
                        @csrf
                        
                        <!-- Username Field -->
                        <div class="form-group">
                            <label for="username" class="form-label">ชื่อผู้ใช้</label>
                            <div class="input-group">
                                <span class="input-icon">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" class="form-control custom-input" name="username" id="username" 
                                       placeholder="กรอกชื่อผู้ใช้" required>
                            </div>
                        </div>
                        <!-- Password Field -->
                        <div class="form-group">
                            <label for="password" class="form-label">รหัสผ่าน</label>
                            <div class="input-group">
                                <span class="input-icon">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" class="form-control custom-input" name="password" id="password" 
                                       placeholder="กรอกรหัสผ่าน" required>
                                <button type="button" class="input-toggle" onclick="togglePassword()">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Login Button -->
                        <button type="submit" class="btn btn-primary w-100 login-btn">
                            <i class="bi bi-box-arrow-in-right me-2"></i>เข้าสู่ระบบ
                        </button>
                    </form>
                    
                    <!-- Footer Info -->
                    <div class="login-footer">
                        <p class="text-muted small text-center mb-0">
                            <i class="bi bi-shield-check me-1"></i>
                            ระบบปลอดภัย | ภาควิชาวิทยาการคอมพิวเตอร์ มหาวิทยาลัยธรรมศาสตร์
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* =================================================================
   CSS VARIABLES & GLOBAL STYLES
   ================================================================= */
:root {
    --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --gradient-success: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --border-radius: 15px;
    --shadow-light: 0 10px 40px rgba(0, 0, 0, 0.1);
    --shadow-medium: 0 15px 50px rgba(0, 0, 0, 0.15);
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

/* =================================================================
   LOGIN CONTAINER & DECORATION
   ================================================================= */
.login-container {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
    position: relative;
    overflow: hidden;
}

.login-decoration {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 1;
}

.decoration-circle {
    position: absolute;
    border-radius: 50%;
    background: rgba(102, 126, 234, 0.1);
    animation: float 8s ease-in-out infinite;
}

/* Individual Circle Positioning */
.circle-1 {
    width: 200px;
    height: 200px;
    top: 10%;
    left: -5%;
    animation-delay: 0s;
    background: rgba(102, 126, 234, 0.1);
}

.circle-2 {
    width: 150px;
    height: 150px;
    top: 60%;
    right: -3%;
    animation-delay: 2s;
    background: rgba(118, 75, 162, 0.1);
}

.circle-3 {
    width: 100px;
    height: 100px;
    bottom: 20%;
    left: 10%;
    animation-delay: 4s;
    background: rgba(240, 147, 251, 0.1);
}

.circle-4 {
    width: 80px;
    height: 80px;
    top: 30%;
    right: 20%;
    animation-delay: 6s;
    background: rgba(79, 172, 254, 0.1);
}

/* =================================================================
   ANIMATIONS
   ================================================================= */
@keyframes float {
    0%, 100% { 
        transform: translateY(0) rotate(0deg) scale(1); 
    }
    50% { 
        transform: translateY(-30px) rotate(180deg) scale(1.1); 
    }
}

@keyframes slideInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes iconPulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

@keyframes cardEntrance {
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

@keyframes rippleAnimation {
    to {
        transform: scale(2);
        opacity: 0;
    }
}

/* =================================================================
   BRAND HEADER STYLES
   ================================================================= */
.brand-header {
    text-align: center;
    position: relative;
    z-index: 10;
}

.brand-icon {
    font-size: 5rem;
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 1.5rem;
    display: inline-block;
    animation: iconPulse 2s ease-in-out infinite;
}

.brand-title {
    font-size: 3.2rem;
    font-weight: 700;
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.75rem;
    letter-spacing: -1px;
}

.brand-subtitle {
    color: #6c757d;
    font-size: 1.3rem;
    margin-bottom: 0;
    font-weight: 400;
}

/* =================================================================
   LOGIN CARD STYLES
   ================================================================= */
.login-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-medium);
    position: relative;
    z-index: 10;
    overflow: hidden;
    max-width: 100%;
    margin: 0 auto;
    opacity: 0;
    transform: scale(0.9) translateY(30px);
    animation: cardEntrance 0.8s ease forwards;
    animation-delay: 0.2s;
}

.login-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-primary);
}

.card-header {
    background: transparent;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    padding: 1.5rem 2rem 1rem;
    text-align: center;
}

.card-title {
    font-size: 1.4rem;
    font-weight: 600;
    color: #495057;
    margin: 0;
}

.card-body {
    padding: 2.5rem;
}

/* =================================================================
   FORM STYLES
   ================================================================= */
.form-group {
    margin-bottom: 2rem;
    opacity: 0;
    transform: translateY(20px);
    animation: slideInUp 0.6s ease forwards;
}

.form-group:nth-child(1) { animation-delay: 0.1s; }
.form-group:nth-child(2) { animation-delay: 0.2s; }

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 1rem;
    font-size: 1rem;
}

/* =================================================================
   INPUT STYLES
   ================================================================= */
.input-group {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    z-index: 5;
    font-size: 1.1rem;
}

.input-group.focused .input-icon {
    color: #667eea;
}

.custom-input {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 1.125rem 1.25rem 1.125rem 3.5rem;
    font-size: 1.1rem;
    transition: var(--transition);
    background: #fff;
    height: 3.5rem;
}

.custom-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    background: #fff;
}

.input-toggle {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    z-index: 5;
    padding: 0.25rem;
    font-size: 1.1rem;
    transition: var(--transition);
}

.input-toggle:hover {
    color: #495057;
}

/* =================================================================
   BUTTON STYLES
   ================================================================= */
.login-btn {
    width: 100%;
    background: var(--gradient-primary);
    border: none;
    border-radius: 12px;
    padding: 1rem 1.5rem;
    font-size: 1.1rem;
    font-weight: 600;
    color: white;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 2rem;
    position: relative;
    overflow: hidden;
    animation-delay: 0.3s;
}

.login-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.5s;
}

.login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.login-btn:hover::before {
    left: 100%;
}

.login-btn:active {
    transform: translateY(0);
}

.btn-text {
    flex: 1;
    text-align: center;
}

.btn-icon {
    font-size: 1.2rem;
    transition: var(--transition);
}

.login-btn:hover .btn-icon {
    transform: translateX(5px);
}

/* =================================================================
   FOOTER & ALERT STYLES
   ================================================================= */
.login-footer {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
}

.custom-alert {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2);
    border-left: 4px solid #dc3545;
}

.alert-danger.custom-alert {
    background: rgba(220, 53, 69, 0.1);
    color: #721c24;
}

/* =================================================================
   INTERACTIVE EFFECTS
   ================================================================= */
.ripple {
    position: absolute;
    border-radius: 50%;
    transform: scale(0);
    animation: rippleAnimation 0.6s linear;
    background-color: rgba(255, 255, 255, 0.3);
    pointer-events: none;
}
        right: -3%;
        animation-delay: 2s;
        background: rgba(118, 75, 162, 0.1);
    }

    .circle-3 {
        width: 100px;
        height: 100px;
        bottom: 20%;
        left: 10%;
        animation-delay: 4s;
        background: rgba(240, 147, 251, 0.1);
    }

    .circle-4 {
        width: 80px;
        height: 80px;
        top: 30%;
        right: 20%;
        animation-delay: 6s;
        background: rgba(79, 172, 254, 0.1);
    }

    @keyframes float {
        0%, 100% { 
            transform: translateY(0) rotate(0deg) scale(1); 
        }
        50% { 
            transform: translateY(-30px) rotate(180deg) scale(1.1); 
        }
    }

    .brand-header {
        text-align: center;
        position: relative;
        z-index: 10;
    }

    .brand-icon {
        font-size: 5rem;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 1.5rem;
        display: inline-block;
        animation: iconPulse 2s ease-in-out infinite;
    }

    .brand-title {
        font-size: 3.2rem;
        font-weight: 700;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 0.75rem;
        letter-spacing: -1px;
    }

    .brand-subtitle {
        color: #6c757d;
        font-size: 1.3rem;
        margin-bottom: 0;
        font-weight: 400;
    }

    .login-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-medium);
        position: relative;
        z-index: 10;
        overflow: hidden;
        max-width: 100%;
        margin: 0 auto;
    }

    .login-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--gradient-primary);
    }

    .card-header {
        background: transparent;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        padding: 1.5rem 2rem 1rem;
        text-align: center;
    }

    .card-title {
        font-size: 1.4rem;
        font-weight: 600;
        color: #495057;
        margin: 0;
    }

    .card-body {
        padding: 2.5rem;
    }

    .form-group {
        margin-bottom: 2rem;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 1rem;
        font-size: 1rem;
    }

    .input-group {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        z-index: 5;
        font-size: 1.1rem;
    }

    .custom-input {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 1.125rem 1.25rem 1.125rem 3.5rem;
        font-size: 1.1rem;
        transition: var(--transition);
        background: #fff;
        height: 3.5rem;
    }

    .custom-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        background: #fff;
    }

    .input-toggle {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
        z-index: 5;
        padding: 0.25rem;
        font-size: 1.1rem;
        transition: var(--transition);
    }

    .input-toggle:hover {
        color: #495057;
    }

    .login-btn {
        width: 100%;
        background: var(--gradient-primary);
        border: none;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        font-size: 1.1rem;
        font-weight: 600;
        color: white;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 2rem;
        position: relative;
        overflow: hidden;
    }

    .login-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.5s;
    }

    .login-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    }

    .login-btn:hover::before {
        left: 100%;
    }

    .login-btn:active {
        transform: translateY(0);
    }

    .btn-text {
        flex: 1;
        text-align: center;
    }

    .btn-icon {
        font-size: 1.2rem;
        transition: var(--transition);
    }

    .login-btn:hover .btn-icon {
        transform: translateX(5px);
    }

    .login-footer {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
    }

    .custom-alert {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2);
        border-left: 4px solid #dc3545;
    }

    .alert-danger.custom-alert {
        background: rgba(220, 53, 69, 0.1);
        color: #721c24;
    }

/* =================================================================
   RESPONSIVE DESIGN - DESKTOP LARGE
   ================================================================= */
@media (min-width: 1400px) {
    .brand-icon {
        font-size: 6rem;
    }
    
    .brand-title {
        font-size: 3.8rem;
    }
    
    .brand-subtitle {
        font-size: 1.4rem;
    }
    
    .card-body {
        padding: 3rem;
    }
    
    .form-group {
        margin-bottom: 2.5rem;
    }
    
    .custom-input {
        height: 4rem;
        font-size: 1.2rem;
        padding: 1.25rem 1.5rem 1.25rem 4rem;
    }
    
    .login-btn {
        padding: 1.25rem 2rem;
        font-size: 1.2rem;
    }
}

/* =================================================================
   RESPONSIVE DESIGN - DESKTOP
   ================================================================= */
@media (max-width: 1200px) {
    .brand-icon {
        font-size: 4.5rem;
    }
    
    .brand-title {
        font-size: 2.8rem;
    }
    
    .brand-subtitle {
        font-size: 1.2rem;
    }
}

/* =================================================================
   RESPONSIVE DESIGN - TABLET
   ================================================================= */
@media (max-width: 992px) {
    .brand-icon {
        font-size: 4rem;
    }
    
    .brand-title {
        font-size: 2.5rem;
    }
    
    .brand-subtitle {
        font-size: 1.1rem;
    }
    
    .card-body {
        padding: 2rem;
    }
}

/* =================================================================
   RESPONSIVE DESIGN - MOBILE LARGE
   ================================================================= */
@media (max-width: 768px) {
    .login-container {
        padding: 1rem 0.5rem;
    }
    
    .brand-icon {
        font-size: 3.5rem;
    }
    
    .brand-title {
        font-size: 2rem;
    }
    
    .brand-subtitle {
        font-size: 1rem;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .custom-input {
        padding: 1rem 1rem 1rem 3rem;
        height: 3rem;
        font-size: 1rem;
    }
    
    .decoration-circle {
        opacity: 0.3;
    }
    
    .circle-1, .circle-2, .circle-3, .circle-4 {
        width: 150px;
        height: 150px;
    }
}

/* =================================================================
   RESPONSIVE DESIGN - MOBILE SMALL
   ================================================================= */
@media (max-width: 576px) {
    .login-container {
        padding: 0.5rem;
    }
    
    .brand-header {
        margin-bottom: 2rem;
    }
    
    .card-body {
        padding: 1.25rem;
    }
    
    .circle-1, .circle-2, .circle-3, .circle-4 {
        width: 100px;
        height: 100px;
    }
}
</style>

<script>
/* =================================================================
   JAVASCRIPT FUNCTIONS & EVENT HANDLERS
   ================================================================= */

// Toggle password visibility
function togglePassword() {
    const passwordField = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('bi-eye');
        toggleIcon.classList.add('bi-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('bi-eye-slash');
        toggleIcon.classList.add('bi-eye');
    }
}

// Add ripple effect to login button
document.addEventListener('DOMContentLoaded', function() {
    const loginBtn = document.querySelector('.login-btn');
    
    if (loginBtn) {
        loginBtn.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    }
});

// Add focus animations for input fields
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.custom-input');
    
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
});
</script>
@endsection

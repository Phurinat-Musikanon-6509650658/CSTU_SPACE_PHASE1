@extends('layouts.app')

@section('title', 'Coordinator Menu - CSTU SPACE')

@push('styles')
<style>
    :root {
        --color-primary: #667eea;
        --color-secondary: #764ba2;
        --color-success: #48bb78;
        --color-warning: #f6ad55;
        --color-danger: #f56565;
        --color-info: #4299e1;
        --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --gradient-success: linear-gradient(135deg, #48bb78, #38a169);
        --gradient-warning: linear-gradient(135deg, #f6ad55, #ed8936);
        --gradient-danger: linear-gradient(135deg, #f56565, #e53e3e);
        --shadow-light: 0 8px 25px rgba(0, 0, 0, 0.1);
        --shadow-medium: 0 15px 35px rgba(0, 0, 0, 0.15);
        --border-radius: 20px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
        min-height: 100vh;
        font-family: 'Kanit', sans-serif;
        position: relative;
        overflow-x: hidden;
    }

    body::before {
        content: '';
        position: fixed;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(102, 126, 234, 0.15) 0%, transparent 70%);
        animation: rotate 30s linear infinite;
        z-index: 0;
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .container {
        position: relative;
        z-index: 1;
        padding: 2rem 15px;
    }

    /* Welcome Header */
    .welcome-header {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: var(--border-radius);
        padding: 2.5rem;
        box-shadow: var(--shadow-light);
        position: relative;
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .welcome-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, var(--color-primary) 0%, var(--color-warning) 50%, var(--color-success) 100%);
    }

    .welcome-content h2 {
        color: #2c3e50;
        font-weight: 700;
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .welcome-content p {
        color: #718096;
        font-size: 1.1rem;
    }

    /* Menu Grid */
    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .menu-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 2rem;
        box-shadow: var(--shadow-light);
        transition: var(--transition);
        text-decoration: none;
        color: #2c3e50;
        position: relative;
        overflow: hidden;
        border-left: 5px solid;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .menu-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-medium);
        color: #2c3e50;
    }

    .menu-card.primary { border-left-color: var(--color-primary); }
    .menu-card.success { border-left-color: var(--color-success); }
    .menu-card.warning { border-left-color: var(--color-warning); }
    .menu-card.danger { border-left-color: var(--color-danger); }
    .menu-card.info { border-left-color: var(--color-info); }

    .menu-card-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 80px;
        height: 80px;
        border-radius: 15px;
        background: rgba(102, 126, 234, 0.1);
    }

    .menu-card.primary .menu-card-icon { color: var(--color-primary); background: rgba(102, 126, 234, 0.1); }
    .menu-card.success .menu-card-icon { color: var(--color-success); background: rgba(72, 187, 120, 0.1); }
    .menu-card.warning .menu-card-icon { color: var(--color-warning); background: rgba(246, 173, 85, 0.1); }
    .menu-card.danger .menu-card-icon { color: var(--color-danger); background: rgba(245, 101, 101, 0.1); }
    .menu-card.info .menu-card-icon { color: var(--color-info); background: rgba(66, 153, 225, 0.1); }

    .menu-card-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .menu-card-description {
        color: #718096;
        font-size: 0.95rem;
        line-height: 1.6;
    }

    .menu-card-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: var(--gradient-danger);
        color: white;
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.85rem;
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    .logout-btn {
        background: white;
        color: #e53e3e;
        padding: 1rem 2rem;
        border-radius: var(--border-radius);
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: var(--shadow-light);
        transition: var(--transition);
    }

    .logout-btn:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-medium);
        color: #c53030;
    }
</style>
@endpush

@section('content')
<div class="container">
    <!-- Welcome Header -->
    <div class="welcome-header">
        <div class="welcome-content">
            <h2>
                <i class="bi bi-gear-fill me-2"></i>
                ยินดีต้อนรับ, ผู้ประสานงาน {{ Auth::user()->name }}
            </h2>
            <p class="mb-0">เลือกเมนูด้านล่างเพื่อเข้าสู่ระบบจัดการโครงงาน</p>
        </div>
    </div>

    <!-- Section Title -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="text-white mb-0 ps-2">
                <i class="bi bi-grid-3x3-gap-fill me-2"></i>เมนูหลัก
            </h5>
        </div>
    </div>

    <!-- Menu Cards -->
    <div class="menu-grid">
        <!-- 1. Dashboard -->
        <a href="{{ route('coordinator.dashboard') }}" class="menu-card primary">
            <div class="menu-card-icon">
                <i class="bi bi-speedometer2"></i>
            </div>
            <div class="menu-card-title">Dashboard</div>
            <div class="menu-card-description">
                ภาพรวมและสถิติกลุ่มโครงงาน พร้อมรายการกลุ่มที่รออนุมัติ
            </div>
        </a>

        <!-- 2. จัดการกลุ่ม -->
        <a href="{{ route('coordinator.groups.index') }}" class="menu-card warning">
            <div class="menu-card-icon">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="menu-card-title">จัดการกลุ่ม</div>
            <div class="menu-card-description">
                อนุมัติกลุ่มโครงงาน ดูรายละเอียดกลุ่ม และจัดการสมาชิก
            </div>
        </a>

        <!-- 3. ข้อเสนอโครงงาน -->
        <a href="{{ route('coordinator.proposals.index') }}" class="menu-card info">
            <div class="menu-card-icon">
                <i class="bi bi-file-earmark-text-fill"></i>
            </div>
            <div class="menu-card-title">ข้อเสนอโครงงาน</div>
            <div class="menu-card-description">
                ดูรายการข้อเสนอโครงงานทั้งหมดในระบบ
            </div>
        </a>

        <!-- 4. ตารางสอบและกรรมการ -->
        <a href="{{ route('coordinator.schedules.index') }}" class="menu-card success">
            <div class="menu-card-icon">
                <i class="bi bi-calendar-event-fill"></i>
            </div>
            <div class="menu-card-title">ตารางสอบและกรรมการ</div>
            <div class="menu-card-description">
                กำหนดตารางสอบและมอบหมายกรรมการสอบโครงงาน
            </div>
        </a>

        <!-- 5. การประเมินและเกรด -->
        <a href="{{ route('coordinator.evaluations.index') }}" class="menu-card primary">
            <div class="menu-card-icon">
                <i class="bi bi-clipboard-check-fill"></i>
            </div>
            <div class="menu-card-title">การประเมินและเกรด</div>
            <div class="menu-card-description">
                ดูผลการประเมิน คำนวณเกรด และจัดการเกรดโครงงาน
            </div>
        </a>

        <!-- 6. สรุปคะแนน -->
        <a href="{{ route('coordinator.evaluations.summary') }}" class="menu-card warning">
            <div class="menu-card-icon">
                <i class="bi bi-award-fill"></i>
            </div>
            <div class="menu-card-title">สรุปคะแนนทั้งหมด</div>
            <div class="menu-card-description">
                ดูสรุปคะแนนและผลการประเมินโครงงานทั้งหมด
            </div>
        </a>

        <!-- 7. จัดการผู้ใช้ -->
        <a href="{{ route('coordinator.users.index') }}" class="menu-card info">
            <div class="menu-card-icon">
                <i class="bi bi-person-gear"></i>
            </div>
            <div class="menu-card-title">จัดการผู้ใช้</div>
            <div class="menu-card-description">
                จัดการข้อมูลผู้ใช้ นำเข้าข้อมูล และส่งออกรายงาน
            </div>
        </a>

        <!-- 8. จัดการตารางสอบ -->
        <a href="{{ route('coordinator.exam-schedules.index') }}" class="menu-card success">
            <div class="menu-card-icon">
                <i class="bi bi-calendar3"></i>
            </div>
            <div class="menu-card-title">จัดการตารางสอบ</div>
            <div class="menu-card-description">
                สร้างและจัดการช่วงเวลาสอบโครงงาน
            </div>
        </a>
    </div>

    <!-- Logout Section -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="text-center">
                <a href="javascript:void(0);" onclick="logout()" class="logout-btn">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>ออกจากระบบ</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function logout() {
    window.location.href = '/logout';
}

// Send logout beacon when user closes window/tab
window.addEventListener('beforeunload', function() {
    if (navigator.sendBeacon) {
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        navigator.sendBeacon('/logout-beacon', formData);
    }
});
</script>
@endsection

@extends('layouts.app')

@section('title', 'Lecturer Dashboard')

@push('styles')
<style>
    .page-header {
        background: white;
        border-radius: var(--border-radius);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-light);
    }

    .page-header h2 {
        color: #2c3e50;
        font-weight: 700;
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--shadow-light);
        transition: var(--transition);
        border-left: 4px solid;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-medium);
    }

    .stat-card.primary {
        border-left-color: #667eea;
    }

    .stat-card.warning {
        border-left-color: #f6ad55;
    }

    .stat-card.success {
        border-left-color: #48bb78;
    }

    .stat-card.info {
        border-left-color: #4299e1;
    }

    .stat-card-icon {
        position: absolute;
        top: 50%;
        right: 1.5rem;
        transform: translateY(-50%);
        font-size: 4rem;
        opacity: 0.1;
    }

    .stat-card-title {
        font-size: 0.875rem;
        color: #718096;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .stat-card-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }

    .stat-card-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.875rem;
        transition: var(--transition);
    }

    .stat-card-link:hover {
        color: #764ba2;
        gap: 0.75rem;
    }

    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .menu-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 2rem;
        box-shadow: var(--shadow-light);
        transition: var(--transition);
        cursor: pointer;
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
    }

    .menu-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-heavy);
        text-decoration: none;
        color: inherit;
    }

    .menu-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transform: scaleX(0);
        transition: var(--transition);
    }

    .menu-card:hover::before {
        transform: scaleX(1);
    }

    .menu-card-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .menu-card-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }

    .menu-card-description {
        font-size: 0.875rem;
        color: #718096;
        line-height: 1.6;
        flex-grow: 1;
    }

    .menu-card-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%);
        color: white;
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.75rem;
        box-shadow: var(--shadow-medium);
    }

    .notification-alert {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        border-radius: var(--border-radius);
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow-medium);
        animation: slideInDown 0.5s ease-out;
    }

    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modern-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius);
        font-weight: 600;
        transition: var(--transition);
        border: none;
    }

    .modern-btn.btn-primary {
        background: var(--gradient-primary);
        color: white;
    }

    .modern-btn.btn-light {
        background: #f8f9fa;
        color: #2c3e50;
    }

    .modern-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-medium);
    }
</style>
@endpush

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-mortarboard-fill me-2"></i>
                    Lecturer Dashboard
                </h2>
                <p class="mb-0 opacity-75">ระบบจัดการข้อเสนอและโครงงานนักศึกษา</p>
            </div>
            <a href="{{ route('menu') }}" class="btn modern-btn btn-light">
                <i class="bi bi-arrow-left"></i>
                <span>กลับไปเมนูหลัก</span>
            </a>
        </div>
    </div>

    <!-- Notifications -->
    @if($newProposals > 0)
        <div class="notification-alert alert alert-dismissible fade show d-flex align-items-center gap-2">
            <i class="bi bi-bell-fill flex-shrink-0"></i>
            <span><strong>มีข้อเสนอโครงงานใหม่!</strong> นักศึกษาได้เสนอโครงงานมาหาคุณ {{ $newProposals }} รายการ</span>
            <button type="button" class="btn-close btn-close-white ms-auto flex-shrink-0" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($newReports > 0)
        <div class="notification-alert alert alert-dismissible fade show d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-pdf-fill flex-shrink-0"></i>
            <span><strong>มีรายงานที่ส่งมาใหม่!</strong> นักศึกษาได้ส่งรายงานโครงงาน {{ $newReports }} รายการ</span>
            <button type="button" class="btn-close btn-close-white ms-auto flex-shrink-0" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($recentGroups > 0)
        <div class="notification-alert alert alert-dismissible fade show d-flex align-items-center gap-2">
            <i class="bi bi-people-fill flex-shrink-0"></i>
            <span><strong>มีกลุ่มใหม่ที่เสนอมาหาคุณ!</strong> มีกลุ่มนักศึกษาที่เพิ่งสร้างและเสนอมา {{ $recentGroups }} กลุ่ม</span>
            <button type="button" class="btn-close btn-close-white ms-auto flex-shrink-0" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('exam_scheduled'))
        <div class="notification-alert alert alert-dismissible fade show">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-calendar-check-fill flex-shrink-0 mt-1"></i>
                <div class="flex-grow-1">
                    <strong>ตารางสอบโครงงานใหม่!</strong>
                    <ul class="mt-2 mb-0 ps-3">
                        @foreach(session('exam_scheduled') as $schedule)
                            <li>
                                <strong>{{ $schedule['project_name'] }}</strong> ({{ $schedule['role'] }})<br>
                                <small>วันเวลาสอบ: {{ $schedule['exam_datetime'] }}</small>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close btn-close-white flex-shrink-0" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card warning">
            <i class="bi bi-clock-fill stat-card-icon"></i>
            <div class="stat-card-title">รอพิจารณา</div>
            <div class="stat-card-value">{{ $stats['pending_proposals'] }}</div>
            <a href="{{ route('lecturer.proposals.index') }}" class="stat-card-link">
                ดูรายละเอียด
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="stat-card success">
            <i class="bi bi-check-circle-fill stat-card-icon"></i>
            <div class="stat-card-title">อนุมัติแล้ว</div>
            <div class="stat-card-value">{{ $stats['approved_proposals'] }}</div>
            <a href="{{ route('lecturer.proposals.index') }}" class="stat-card-link">
                ดูรายละเอียด
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="stat-card primary">
            <i class="bi bi-folder-fill stat-card-icon"></i>
            <div class="stat-card-title">โครงงานของฉัน</div>
            <div class="stat-card-value">{{ $stats['my_projects'] }}</div>
            <a href="{{ route('lecturer.projects.index') }}" class="stat-card-link">
                ดูรายละเอียด
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="stat-card info">
            <i class="bi bi-clipboard-check-fill stat-card-icon"></i>
            <div class="stat-card-title">รอประเมิน</div>
            <div class="stat-card-value">{{ $stats['pending_evaluations'] }}</div>
            <a href="{{ route('lecturer.evaluations.index') }}" class="stat-card-link">
                ดูรายละเอียด
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Menu Cards -->
    <div class="menu-grid">
        <!-- 1. ข้อเสนอโครงงาน -->
        <a href="{{ route('lecturer.proposals.index') }}" class="menu-card">
            @if($stats['pending_proposals'] > 0)
                <span class="menu-card-badge">{{ $stats['pending_proposals'] }}</span>
            @endif
            <div class="menu-card-icon">
                <i class="bi bi-file-earmark-text-fill"></i>
            </div>
            <div class="menu-card-title">ข้อเสนอโครงงาน</div>
            <div class="menu-card-description">
                พิจารณาอนุมัติหรือปฏิเสธข้อเสนอหัวข้อโครงงานจากนักศึกษา
            </div>
        </a>

        <!-- 2. โครงงานของฉัน -->
        <a href="{{ route('lecturer.projects.index') }}" class="menu-card">
            <div class="menu-card-icon">
                <i class="bi bi-folder-fill"></i>
            </div>
            <div class="menu-card-title">โครงงานของฉัน</div>
            <div class="menu-card-description">
                ดูรายการโครงงานที่คุณเป็นอาจารย์ที่ปรึกษา และติดตามความคืบหน้า
            </div>
        </a>

        <!-- 3. รายงานโครงงาน (เล่มที่นักศึกษาส่ง) -->
        <a href="{{ route('lecturer.submissions.index') }}" class="menu-card">
            <div class="menu-card-icon">
                <i class="bi bi-file-earmark-pdf-fill"></i>
            </div>
            <div class="menu-card-title">รายงานโครงงาน</div>
            <div class="menu-card-description">
                ดาวน์โหลดและตรวจสอบรายงานโครงงานที่นักศึกษาส่งมา
            </div>
        </a>

        <!-- 4. ตารางสอบและการประเมินโครงงาน -->
        <a href="{{ route('lecturer.evaluations.index') }}" class="menu-card">
            @if($stats['pending_evaluations'] > 0)
                <span class="menu-card-badge">{{ $stats['pending_evaluations'] }}</span>
            @endif
            <div class="menu-card-icon">
                <i class="bi bi-calendar-check-fill"></i>
            </div>
            <div class="menu-card-title">ตารางสอบและการประเมินโครงงาน</div>
            <div class="menu-card-description">
                ตรวจสอบตารางสอบและให้คะแนนประเมินผลโครงงานที่คุณเป็นที่ปรึกษาหรือกรรมการ
            </div>
        </a>

    </div>
</div>
@endsection

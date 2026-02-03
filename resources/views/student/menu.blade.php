<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Menu - CSTU SPACE</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --color-red: #DC143C;
            --color-yellow: #FFD700;
            --color-orange: #FF8C00;
            --color-green: #28a745;
            --color-black: #1a1a1a;
            --color-blue: #0066CC;
            --color-dark-blue: #003d82;
            --color-gray: #6c757d;
            --gradient-primary: linear-gradient(135deg, var(--color-blue) 0%, var(--color-dark-blue) 100%);
            --gradient-accent: linear-gradient(135deg, var(--color-red) 0%, #FF6347 100%);
            --gradient-warning: linear-gradient(135deg, var(--color-yellow) 0%, #FFA500 100%);
            --gradient-dark: linear-gradient(135deg, #2c3e50 0%, var(--color-black) 100%);
            --shadow-light: 0 8px 25px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 15px 35px rgba(0, 0, 0, 0.2);
            --border-radius: 20px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
            min-height: 100vh;
            font-family: 'Kanit', sans-serif;
            color: #333;
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
            background: radial-gradient(circle, rgba(220, 20, 60, 0.1) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
            z-index: 0;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .container-fluid {
            position: relative;
            z-index: 1;
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
            background: linear-gradient(90deg, var(--color-red) 0%, var(--color-yellow) 50%, var(--color-blue) 100%);
        }
        
        .welcome-content {
            display: flex;
            align-items: center;
            gap: 2rem;
            position: relative;
            z-index: 2;
        }
        
        .welcome-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            box-shadow: 0 8px 20px rgba(0, 102, 204, 0.3);
        }
        
        .welcome-text h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--color-black);
            margin-bottom: 0.5rem;
        }
        
        .welcome-text h4 {
            font-size: 1.8rem;
            font-weight: 700;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        
        .role-badge {
            display: inline-block;
        }
        
        .role-badge .badge {
            padding: 0.5rem 1.2rem;
            font-size: 0.9rem;
            font-weight: 500;
            border-radius: 50px;
            background: var(--gradient-accent);
            border: none;
        }
        
        .logout-btn {
            background: var(--gradient-accent);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 500;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(220, 20, 60, 0.3);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(220, 20, 60, 0.4);
            color: white;
        }
        
        /* Dashboard Cards */
        .dashboard-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--color-red) 0%, var(--color-yellow) 50%, var(--color-blue) 100%);
        }
        
        /* Feature Cards */
        .feature-card {
            cursor: pointer;
            height: 100%;
            position: relative;
            transition: var(--transition);
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }
        
        .feature-card .card-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
            transition: var(--transition);
        }
        
        .feature-card:nth-child(1) .card-icon {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 8px 20px rgba(0, 102, 204, 0.3);
        }
        
        .feature-card:nth-child(2) .card-icon {
            background: var(--gradient-accent);
            color: white;
            box-shadow: 0 8px 20px rgba(220, 20, 60, 0.3);
        }
        
        .feature-card:nth-child(3) .card-icon {
            background: var(--gradient-warning);
            color: var(--color-black);
            box-shadow: 0 8px 20px rgba(255, 215, 0, 0.3);
        }
        
        .feature-card:hover .card-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .feature-card .card-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .feature-card .card-title {
            font-weight: 700;
            color: var(--color-black);
            margin-bottom: 1rem;
            font-size: 1.25rem;
        }
        
        .feature-card .card-description {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: auto;
            min-height: 45px;
        }
        
        .feature-card .btn {
            border-radius: 50px;
            padding: 0.75rem 2rem;
            font-weight: 500;
            border: none;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .feature-card:nth-child(1) .btn {
            background: var(--gradient-primary);
            box-shadow: 0 4px 15px rgba(0, 102, 204, 0.3);
        }
        
        .feature-card:nth-child(2) .btn {
            background: var(--gradient-accent);
            box-shadow: 0 4px 15px rgba(220, 20, 60, 0.3);
        }
        
        .feature-card:nth-child(3) .btn {
            background: var(--gradient-warning);
            color: var(--color-black);
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
        }
        
        .feature-card .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }
        
        /* Notification Card */
        .notification-card {
            background: var(--gradient-accent);
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .notification-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--color-yellow);
        }
        
        .notification-card .card {
            border: none;
            border-radius: 15px;
            transition: var(--transition);
        }
        
        .notification-card .card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }
        
        /* Group Card */
        .group-card {
            background: var(--gradient-primary);
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .group-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--color-yellow);
        }
        
        .group-card .btn-light {
            background: white;
            color: var(--color-blue);
            border: none;
            font-weight: 600;
            transition: var(--transition);
        }
        
        .group-card .btn-light:hover {
            background: var(--color-yellow);
            color: var(--color-black);
            transform: translateY(-2px);
        }
        
        .group-card .btn-danger {
            background: var(--color-red);
            border: none;
            font-weight: 600;
        }
        
        /* No Group Card */
        .no-group-card {
            background: rgba(255, 255, 255, 0.98);
            text-align: center;
            padding: 4rem 2rem;
        }
        
        .no-group-card i {
            color: var(--color-blue);
            opacity: 0.5;
            margin-bottom: 1.5rem;
        }
        
        .no-group-card h4 {
            color: var(--color-black);
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .no-group-card p {
            color: #666;
            margin-bottom: 2rem;
        }
        
        /* Alerts */
        .alert {
            border-radius: 15px;
            border: none;
            padding: 1rem 1.5rem;
            box-shadow: var(--shadow-light);
        }
        
        .alert-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .alert-danger {
            background: var(--gradient-accent);
            color: white;
        }
        
        /* Modal */
        .modal-content {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            background: var(--gradient-primary);
            color: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            border: none;
        }
        
        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .welcome-content {
                flex-direction: column;
                text-align: center;
            }
            
            .welcome-text h4 {
                font-size: 1.5rem;
            }
            
            .feature-card {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="welcome-header">
                    <div class="welcome-content">
                        <div class="welcome-avatar">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <div class="welcome-text">
                            <h2>ยินดีต้อนรับ</h2>
                            <h4>{{ $student->full_name }}</h4>
                            <div class="role-badge">
                                <span class="badge">
                                    <i class="bi bi-mortarboard-fill me-1"></i>นักศึกษา
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('member_accepted'))
            <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm" role="alert">
                <h5 class="alert-heading">
                    <i class="bi bi-person-check-fill me-2"></i>สมาชิกใหม่เข้าร่วมกลุ่ม!
                </h5>
                <hr>
                <p class="mb-0">{{ session('member_accepted') }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('proposal_approved'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <h5 class="alert-heading">
                    <i class="bi bi-check-circle-fill me-2"></i>ข้อเสนอโครงงานได้รับการอนุมัติ!
                </h5>
                <hr>
                <p class="mb-0">{{ session('proposal_approved') }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('proposal_rejected'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                <h5 class="alert-heading">
                    <i class="bi bi-x-circle-fill me-2"></i>ข้อเสนอโครงงานถูกปฏิเสธ
                </h5>
                <hr>
                <p class="mb-0">{{ session('proposal_rejected') }}</p>
                <small class="d-block mt-2 text-muted">
                    <i class="bi bi-info-circle me-1"></i>คุณสามารถเสนอโครงงานใหม่หรือติดต่ออาจารย์ท่านอื่นได้
                </small>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('report_submitted'))
            <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm" role="alert">
                <h5 class="alert-heading">
                    <i class="bi bi-file-earmark-check-fill me-2"></i>เล่มรายงานถูกส่งแล้ว!
                </h5>
                <hr>
                <p class="mb-0">{{ session('report_submitted') }}</p>
                <small class="d-block mt-2 text-muted">
                    <i class="bi bi-info-circle me-1"></i>คุณสามารถดาวน์โหลดเล่มรายงานได้จากการ์ด "เล่มรายงาน"
                </small>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('exam_scheduled'))
            <div class="alert alert-primary alert-dismissible fade show border-0 shadow-sm" role="alert">
                <h5 class="alert-heading">
                    <i class="bi bi-calendar-check-fill me-2"></i>📅 กำหนดการสอบโครงงาน
                </h5>
                <hr>
                <p class="mb-2">
                    <strong>โครงงาน:</strong> {{ session('exam_scheduled')['project_name'] }}
                </p>
                <p class="mb-0">
                    <strong>วันเวลาสอบ:</strong> {{ session('exam_scheduled')['exam_datetime'] }}
                </p>
                <small class="d-block mt-2 text-muted">
                    <i class="bi bi-info-circle me-1"></i>กรุณาเตรียมตัวสอบให้พร้อม
                </small>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('grade_released'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <h5 class="alert-heading">
                    <i class="bi bi-award-fill me-2"></i>🎓 เกรดของคุณได้รับการประกาศแล้ว!
                </h5>
                <hr>
                <p class="mb-0">
                    อาจารย์ได้ประกาศเกรดโครงงานของคุณแล้ว
                </p>
                <small class="d-block mt-2 text-muted">
                    <i class="bi bi-info-circle me-1"></i>คุณสามารถดูรายละเอียดเกรดและคะแนนได้จากการ์ด "ดูเกรด"
                </small>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Pending Invitations -->
        @if($pendingInvitations->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card notification-card p-4">
                    <h4 class="fw-bold mb-3">
                        <i class="fas fa-bell me-2"></i>คำเชิญเข้าร่วมกลุ่ม
                        <span class="badge bg-light text-dark ms-2">{{ $pendingInvitations->count() }}</span>
                    </h4>
                    <div class="row">
                        @foreach($pendingInvitations as $invitation)
                        <div class="col-md-6 mb-3">
                            <div class="card bg-white text-dark">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        @if($invitation->group->project)
                                            {{ $invitation->group->project->project_name }}
                                        @else
                                            กลุ่มที่ {{ $invitation->group->group_id }} - {{ $invitation->group->subject_code }}
                                        @endif
                                    </h6>
                                    <p class="card-text small">
                                        <strong>ผู้เชิญ:</strong> {{ $invitation->inviter->full_name }}<br>
                                        <strong>รหัสโครงงาน:</strong> {{ $invitation->group->project_code }}<br>
                                        <strong>วิชา:</strong> {{ $invitation->group->subject_code }}
                                    </p>
                                    <div class="d-flex gap-2">
                                        <form action="{{ route('invitations.accept', $invitation) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fas fa-check me-1"></i>ตอบรับ
                                            </button>
                                        </form>
                                        <form action="{{ route('invitations.decline', $invitation) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-times me-1"></i>ปฏิเสธ
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- My Group Status -->
        <div class="row mb-4">
            <div class="col-12">
                @if($myGroup)
                <div class="dashboard-card group-card p-4">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <h4 class="fw-bold mb-0">
                            <i class="bi bi-people-fill me-2"></i>กลุ่มของฉัน
                        </h4>
                        <span class="badge bg-primary" style="font-size: 1.2rem; padding: 0.75rem 1.5rem;">
                            <i class="bi bi-hash me-1"></i>กลุ่มที่ {{ $myGroup->group_id }}
                        </span>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="mb-3">
                                @if($myGroup->project)
                                    {{ $myGroup->project->project_name }}
                                @else
                                    กลุ่มที่ {{ $myGroup->group_id }} - {{ $myGroup->subject_code }}
                                @endif
                            </h5>
                            <div class="mb-2">
                                <i class="bi bi-code-square me-2"></i>
                                <strong>รหัสโครงงาน:</strong> {{ $myGroup->project_code }}
                            </div>
                            <div class="mb-2">
                                <i class="bi bi-book me-2"></i>
                                <strong>วิชา:</strong> {{ $myGroup->subject_code }}
                            </div>
                            <div class="mb-3">
                                <i class="bi bi-calendar3 me-2"></i>
                                <strong>ปีการศึกษา:</strong> {{ $myGroup->year }}/{{ $myGroup->semester }}
                            </div>
                            <div class="mb-3">
                                <i class="bi bi-file-text me-2"></i>
                                <strong>คำอธิบาย:</strong> {{ $myGroup->description ?: '-' }}
                            </div>
                            
                            <h6 class="mt-4 mb-3">
                                <i class="bi bi-person-badge me-2"></i>
                                สมาชิกกลุ่ม ({{ $myGroup->members->count() }}/2)
                            </h6>
                            <ul class="list-unstyled">
                                @foreach($myGroup->members as $member)
                                <li class="mb-2">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    {{ $member->student->full_name }} ({{ $member->student->username_std }})
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex flex-column gap-3">
                                <a href="{{ route('groups.show', $myGroup) }}" class="btn btn-light">
                                    <i class="bi bi-eye-fill me-2"></i>ดูรายละเอียด
                                </a>
                                <button type="button" class="btn btn-danger" onclick="confirmLeaveGroup()">
                                    <i class="bi bi-box-arrow-left me-2"></i>ออกจากกลุ่ม
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="dashboard-card no-group-card">
                    <i class="bi bi-people fa-4x"></i>
                    <h4>คุณยังไม่มีกลุ่มโครงงาน</h4>
                    <p>สร้างกลุ่มใหม่และเชิญเพื่อนร่วมงาน หรือรอคำเชิญจากเพื่อน</p>
                    <a href="{{ route('groups.create') }}" class="btn" style="background: white; color: #0066CC; border: 2px solid #0066CC; padding: 1rem 3rem; border-radius: 50px; font-weight: 600; font-size: 1.1rem; box-shadow: 0 8px 20px rgba(0, 102, 204, 0.3); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                        <i class="bi bi-plus-circle-fill me-2"></i>สร้างกลุ่มโครงงาน
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <h5 class="text-white mb-4 ps-2">
                    <i class="bi bi-grid-3x3-gap-fill me-2"></i>เมนูหลัก
                </h5>
            </div>
        </div>
        
        <div class="row g-4 mb-4">
            <!-- 1. จัดการกลุ่ม / สร้างกลุ่ม -->
            @if($myGroup)
            <div class="col-lg-4 col-md-6">
                <div class="dashboard-card feature-card p-4 text-center h-100" style="border-left: 4px solid var(--color-blue);">
                    <div class="card-icon mb-3" style="color: var(--color-blue);">
                        <i class="bi bi-diagram-3-fill"></i>
                    </div>
                    <div class="card-content">
                        <h5 class="card-title">จัดการกลุ่ม</h5>
                        <p class="card-description">ดูข้อมูลและจัดการกลุ่มของคุณ</p>
                        <a href="{{ route('groups.show', $myGroup) }}" class="btn btn-primary mt-2">
                            <span>เข้าสู่กลุ่ม</span>
                            <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            @else
            <div class="col-lg-4 col-md-6">
                <div class="dashboard-card feature-card p-4 text-center h-100" style="border-left: 4px solid var(--color-green);">
                    <div class="card-icon mb-3" style="color: var(--color-green);">
                        <i class="bi bi-plus-circle-fill"></i>
                    </div>
                    <div class="card-content">
                        <h5 class="card-title">สร้างกลุ่มโครงงาน</h5>
                        <p class="card-description">เริ่มต้นโครงงานใหม่ของคุณ</p>
                        <a href="{{ route('groups.create') }}" class="btn btn-success mt-2">
                            <span>สร้างกลุ่มใหม่</span>
                            <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- 2. คำเชิญ -->
            <div class="col-lg-4 col-md-6">
                <div class="dashboard-card feature-card p-4 text-center h-100" style="border-left: 4px solid var(--color-yellow);">
                    <div class="card-icon mb-3" style="color: var(--color-yellow);">
                        <i class="bi bi-envelope-fill"></i>
                    </div>
                    <div class="card-content">
                        <h5 class="card-title">คำเชิญเข้ากลุ่ม</h5>
                        @if($pendingInvitations->count() > 0)
                        <p class="card-description">
                            <span class="badge bg-danger">{{ $pendingInvitations->count() }}</span> คำเชิญใหม่
                        </p>
                        @else
                        <p class="card-description">ดูคำเชิญเข้าร่วมกลุ่มทั้งหมด</p>
                        @endif
                        <a href="{{ route('invitations.index') }}" class="btn btn-warning mt-2">
                            <span>ดูคำเชิญ</span>
                            <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- 3. รอการตอบรับคำเชิญ (หัวหน้ากลุ่ม) -->
            @if(isset($myGroup) && $myGroup && $myGroup->hasPendingInvitation() && isset($isGroupLeader) && $isGroupLeader)
            <div class="col-lg-4 col-md-6">
                <div class="dashboard-card feature-card p-4 text-center h-100" style="border-left: 4px solid #6c757d;">
                    <div class="card-icon mb-3" style="color: #6c757d;">
                        <i class="bi bi-clock-fill"></i>
                    </div>
                    <div class="card-content">
                        <h5 class="card-title">รอการตอบรับ</h5>
                        <p class="card-description">
                            <span class="badge bg-secondary">รอสมาชิกตอบรับคำเชิญ</span><br>
                            <small class="text-muted mt-2 d-block">รอให้สมาชิกตอบรับหรือปฏิเสธก่อนเสนอหัวข้อ</small>
                        </p>
                        <a href="{{ route('groups.show', $myGroup->group_id) }}" class="btn btn-secondary mt-2">
                            <span>ดูรายละเอียด</span>
                            <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- 4. เสนอหัวข้อโครงงาน (หัวหน้ากลุ่ม) -->
            @if(isset($myGroup) && $myGroup && isset($isGroupLeader) && $isGroupLeader && !$myGroup->hasPendingInvitation())
                @php
                    $latestProposal = $myGroup->latestProposal;
                    $projectApproved = $latestProposal && $latestProposal->status === 'approved';
                @endphp
                
                @if(!$projectApproved)
                <div class="col-lg-4 col-md-6">
                    <div class="dashboard-card feature-card p-4 text-center h-100" style="border-left: 4px solid var(--color-orange);">
                        <div class="card-icon mb-3" style="color: var(--color-orange);">
                            <i class="bi bi-lightbulb-fill"></i>
                        </div>
                        <div class="card-content">
                            <h5 class="card-title">เสนอหัวข้อโครงงาน</h5>
                            @if($latestProposal)
                                @if($latestProposal->status === 'pending')
                                    <p class="card-description">
                                        <span class="badge bg-warning text-dark">รอการพิจารณา</span><br>
                                        <small class="text-muted mt-2 d-block">{{ Str::limit($latestProposal->proposed_title, 35) }}</small>
                                    </p>
                                    <a href="{{ route('groups.show', $myGroup->group_id) }}" class="btn btn-warning mt-2">
                                        <span>ดูรายละเอียด</span>
                                        <i class="bi bi-arrow-right ms-2"></i>
                                    </a>
                                @elseif($latestProposal->status === 'rejected')
                                    <p class="card-description">
                                        <span class="badge bg-danger">ถูกปฏิเสธ</span><br>
                                        <small class="text-muted mt-2 d-block">สามารถเสนอหัวข้อใหม่ได้</small>
                                    </p>
                                    <a href="{{ route('proposals.create', $myGroup->group_id) }}" class="btn btn-danger mt-2">
                                        <span>เสนอหัวข้อใหม่</span>
                                        <i class="bi bi-arrow-right ms-2"></i>
                                    </a>
                                @endif
                            @else
                                <p class="card-description">ส่งข้อเสนอหัวข้อโครงงานให้อาจารย์พิจารณา</p>
                                <a href="{{ route('proposals.create', $myGroup->group_id) }}" class="btn btn-warning mt-2">
                                    <span>เสนอหัวข้อ</span>
                                    <i class="bi bi-arrow-right ms-2"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            @endif
            
            <!-- 5. โครงงานอนุมัติแล้ว -->
            @if(isset($myGroup) && $myGroup && $myGroup->project && 
                $myGroup->project->status_project === 'approved' && 
                !$myGroup->project->submission_file)
            <div class="col-lg-4 col-md-6">
                <div class="dashboard-card feature-card p-4 text-center h-100" style="border-left: 4px solid var(--color-green);">
                    <div class="card-icon mb-3" style="color: var(--color-green);">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="card-content">
                        <h5 class="card-title">โครงงานอนุมัติแล้ว</h5>
                        <p class="card-description">
                            <span class="badge bg-success">อนุมัติแล้ว</span><br>
                            <small class="text-muted mt-2 d-block">{{ Str::limit($myGroup->project->project_name, 35) }}</small>
                        </p>
                        <a href="{{ route('groups.show', $myGroup->group_id) }}" class="btn btn-success mt-2">
                            <span>ดูรายละเอียด</span>
                            <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- 6. ส่งเล่มรายงาน -->
            @if(isset($myGroup) && $myGroup && $myGroup->project && 
                $myGroup->project->status_project === 'approved' &&
                !$myGroup->project->submission_file)
            <div class="col-lg-4 col-md-6">
                <div class="dashboard-card feature-card p-4 text-center h-100" style="border-left: 4px solid var(--color-orange);">
                    <div class="card-icon mb-3" style="color: var(--color-orange);">
                        <i class="bi bi-file-earmark-pdf-fill"></i>
                    </div>
                    <div class="card-content">
                        <h5 class="card-title">ส่งเล่มรายงาน</h5>
                        <p class="card-description">
                            <span class="badge bg-warning text-dark">รอส่งเล่มรายงาน</span><br>
                            <small class="text-muted mt-2 d-block">อัพโหลดไฟล์รายงานฉบับสมบูรณ์ (PDF)</small>
                        </p>
                        <a href="{{ route('student.submission.form') }}" class="btn btn-warning mt-2">
                            <span>ส่งเล่มรายงาน</span>
                            <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- 7. เล่มรายงานที่ส่งแล้ว -->
            @if(isset($myGroup) && $myGroup && $myGroup->project && $myGroup->project->submission_file)
            <div class="col-lg-4 col-md-6">
                <div class="dashboard-card feature-card p-4 text-center h-100" style="border-left: 4px solid var(--color-green);">
                    <div class="card-icon mb-3" style="color: var(--color-green);">
                        <i class="bi bi-file-earmark-check-fill"></i>
                    </div>
                    <div class="card-content">
                        <h5 class="card-title">เล่มรายงาน</h5>
                        <p class="card-description">
                            <span class="badge bg-success">ส่งแล้ว</span><br>
                            <small class="text-muted mt-2 d-block">
                                ส่งเมื่อ: {{ \Carbon\Carbon::parse($myGroup->project->submitted_at)->locale('th')->translatedFormat('j M Y H:i') }} น.
                            </small>
                        </p>
                        <div class="d-flex gap-2 justify-content-center mt-2">
                            <a href="{{ route('student.submission.download', $myGroup->project->project_id) }}" class="btn btn-success btn-sm">
                                <i class="bi bi-download me-1"></i>ดาวน์โหลด
                            </a>
                            <a href="{{ route('student.submission.form') }}" class="btn btn-outline-warning btn-sm">
                                <i class="bi bi-arrow-clockwise me-1"></i>อัพโหลดใหม่
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- 8. ดูคะแนนและผลการประเมิน -->
            @if(isset($myGroup) && $myGroup && $myGroup->project && $myGroup->project->submission_file)
            <div class="col-lg-4 col-md-6">
                <div class="dashboard-card feature-card p-4 text-center h-100" style="border-left: 4px solid #9333ea;">
                    <div class="card-icon mb-3" style="color: #9333ea;">
                        <i class="bi bi-award-fill"></i>
                    </div>
                    <div class="card-content">
                        <h5 class="card-title">คะแนนและผลประเมิน</h5>
                        <p class="card-description">
                            <span class="badge" style="background: linear-gradient(135deg, #9333ea, #a855f7); color: white;">ดูคะแนน</span><br>
                            <small class="text-muted mt-2 d-block">ตรวจสอบคะแนนและผลการประเมินโครงงาน</small>
                        </p>
                        <a href="{{ route('student.grades') }}" class="btn btn-sm mt-2" style="background: linear-gradient(135deg, #9333ea, #a855f7); color: white; border: none;">
                            <span>ดูคะแนน</span>
                            <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endif
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

    <!-- Leave Group Confirmation Modal -->
    <div class="modal fade" id="leaveGroupModal" tabindex="-1" aria-labelledby="leaveGroupModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="leaveGroupModalLabel">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        ยืนยันการออกจากกลุ่ม
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-users text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="mb-3">คุณมั่นใจแล้วใช่ไหมว่าจะออกจากกลุ่ม?</h6>
                    <p class="text-muted">
                        การดำเนินการนี้ไม่สามารถยกเลิกได้ หากกลุ่มไม่มีสมาชิกเหลือ กลุ่มจะถูกลบอัตโนมัติ
                    </p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>ยกเลิก
                    </button>
                    <form action="{{ route('groups.leave') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-sign-out-alt me-1"></i>ออกจากกลุ่ม
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function confirmLeaveGroup() {
        const modal = new bootstrap.Modal(document.getElementById('leaveGroupModal'));
        modal.show();
    }

    // Logout function
    function logout() {
        window.location.href = '/logout';
    }

    // Send logout beacon when user closes window/tab
    window.addEventListener('beforeunload', function() {
        // ส่ง request เพื่ออัปเดต logout time
        if (navigator.sendBeacon) {
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            navigator.sendBeacon('/logout-beacon', formData);
        }
    });
    </script>
</body>
</html>
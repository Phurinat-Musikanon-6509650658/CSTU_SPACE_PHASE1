<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Menu - CSTU SPACE</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Kanit', sans-serif;
        }
        
        .dashboard-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .feature-card {
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .notification-card {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
        }
        
        .group-card {
            background: linear-gradient(45deg, #4ecdc4, #44bd87);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="fw-bold text-primary mb-1">
                                <i class="fas fa-user-graduate me-2"></i>Student Menu
                            </h2>
                            <p class="text-muted mb-0">ยินดีต้อนรับ {{ $student->full_name }}</p>
                        </div>
                        <div>
                            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                               class="btn btn-outline-danger">
                                <i class="fas fa-sign-out-alt me-1"></i>ออกจากระบบ
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
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
                                    <h6 class="card-title">{{ $invitation->group->project_name }}</h6>
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
                    <h4 class="fw-bold mb-3">
                        <i class="fas fa-users me-2"></i>กลุ่มของฉัน
                    </h4>
                    <div class="row">
                        <div class="col-md-8">
                            <h5>{{ $myGroup->project_name }}</h5>
                            <p class="mb-1"><strong>รหัสโครงงาน:</strong> {{ $myGroup->project_code }}</p>
                            <p class="mb-1"><strong>วิชา:</strong> {{ $myGroup->subject_code }}</p>
                            <p class="mb-1"><strong>ปีการศึกษา:</strong> {{ $myGroup->year }}/{{ $myGroup->semester }}</p>
                            <p class="mb-3"><strong>คำอธิบาย:</strong> {{ $myGroup->description ?: '-' }}</p>
                            
                            <h6>สมาชิกกลุ่ม ({{ $myGroup->members->count() }}/2):</h6>
                            <ul class="list-unstyled">
                                @foreach($myGroup->members as $member)
                                <li><i class="fas fa-user me-2"></i>{{ $member->student->full_name }} ({{ $member->student->username_std }})</li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('groups.show', $myGroup) }}" class="btn btn-light">
                                <i class="fas fa-eye me-1"></i>ดูรายละเอียด
                            </a>
                        </div>
                    </div>
                </div>
                @else
                <div class="dashboard-card p-4 text-center">
                    <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted mb-3">คุณยังไม่มีกลุ่มโครงงาน</h4>
                    <p class="text-muted mb-4">สร้างกลุ่มใหม่และเชิญเพื่อนร่วมงาน หรือรอคำเชิญจากเพื่อน</p>
                    <a href="{{ route('groups.create') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus me-2"></i>สร้างกลุ่มโครงงาน
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="dashboard-card feature-card p-4 text-center h-100">
                    <i class="fas fa-project-diagram fa-3x text-primary mb-3"></i>
                    <h5 class="fw-bold">จัดการโครงงาน</h5>
                    <p class="text-muted">ดูและจัดการโครงงานของคุณ</p>
                    @if($myGroup)
                        <a href="{{ route('groups.show', $myGroup) }}" class="btn btn-primary">เข้าสู่โครงงาน</a>
                    @else
                        <a href="{{ route('groups.create') }}" class="btn btn-primary">สร้างกลุ่มใหม่</a>
                    @endif
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="dashboard-card feature-card p-4 text-center h-100">
                    <i class="fas fa-envelope fa-3x text-info mb-3"></i>
                    <h5 class="fw-bold">คำเชิญ</h5>
                    <p class="text-muted">ดูคำเชิญเข้าร่วมกลุ่มทั้งหมด</p>
                    <a href="{{ route('invitations.index') }}" class="btn btn-info">
                        ดูคำเชิญ 
                        @if($pendingInvitations->count() > 0)
                            <span class="badge bg-danger ms-1">{{ $pendingInvitations->count() }}</span>
                        @endif
                    </a>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="dashboard-card feature-card p-4 text-center h-100">
                    <i class="fas fa-user-cog fa-3x text-success mb-3"></i>
                    <h5 class="fw-bold">ข้อมูลส่วนตัว</h5>
                    <p class="text-muted">แก้ไขข้อมูลและการตั้งค่า</p>
                    <a href="#" class="btn btn-success">จัดการข้อมูล</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
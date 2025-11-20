<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดกลุ่ม - {{ $group->project_name }}</title>
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
        
        .card-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .info-card {
            background: linear-gradient(45deg, #4ecdc4, #44bd87);
            color: white;
            border-radius: 15px;
        }
        
        .member-card {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px;
        }
        
        .invitation-card {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            border-radius: 15px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <!-- Header -->
        <div class="card-custom p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold text-primary mb-1">
                        <i class="fas fa-users me-2"></i>{{ $group->project_name }}
                    </h2>
                    <p class="text-muted mb-0">รายละเอียดกลุ่มโครงงาน</p>
                </div>
                <a href="{{ route('student.menu') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>กลับ
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Project Information -->
            <div class="col-lg-8 mb-4">
                <div class="card-custom info-card p-4 h-100">
                    <h4 class="fw-bold mb-3">
                        <i class="fas fa-info-circle me-2"></i>ข้อมูลโครงงาน
                    </h4>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="fw-bold">ชื่อโครงงาน</h6>
                                <p class="mb-0">{{ $group->project_name }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <h6 class="fw-bold">รหัสโครงงาน</h6>
                                <p class="mb-0">{{ $group->project_code }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <h6 class="fw-bold">รหัสวิชา</h6>
                                <p class="mb-0">{{ $group->subject_code }}</p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="fw-bold">ปีการศึกษา</h6>
                                <p class="mb-0">{{ $group->year }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <h6 class="fw-bold">ภาคการศึกษา</h6>
                                <p class="mb-0">{{ $group->semester == 1 ? 'ภาคต้น' : 'ภาคปลาย' }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <h6 class="fw-bold">สถานะกลุ่ม</h6>
                                <span class="badge bg-light text-dark">{{ ucfirst($group->status_group) }}</span>
                            </div>
                        </div>
                    </div>
                    
                    @if($group->description)
                    <div class="mt-3">
                        <h6 class="fw-bold">คำอธิบายโครงงาน</h6>
                        <p class="mb-0">{{ $group->description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Group Members -->
            <div class="col-lg-4 mb-4">
                <div class="card-custom member-card p-4 h-100">
                    <h4 class="fw-bold mb-3">
                        <i class="fas fa-users me-2"></i>สมาชิกกลุ่ม
                        <span class="badge bg-light text-dark ms-2">{{ $group->members->count() }}/2</span>
                    </h4>
                    
                    @foreach($group->members as $index => $member)
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light text-dark rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 50px; height: 50px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $member->student->full_name }}</h6>
                            <small class="opacity-75">{{ $member->student->username_std }}</small>
                            @if($index === 0)
                                <br><small class="badge bg-warning text-dark">ผู้สร้างกลุ่ม</small>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    
                    @if($group->members->count() < 2)
                        <div class="text-center mt-3 pt-3 border-top border-light opacity-50">
                            <i class="fas fa-user-plus fa-2x mb-2"></i>
                            <p class="small mb-0">รอสมาชิกคนที่ 2</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Pending Invitations -->
        @if($group->pendingInvitations->count() > 0)
        <div class="row">
            <div class="col-12">
                <div class="card-custom invitation-card p-4">
                    <h4 class="fw-bold mb-3">
                        <i class="fas fa-clock me-2"></i>คำเชิญที่รอการตอบรับ
                        <span class="badge bg-light text-dark ms-2">{{ $group->pendingInvitations->count() }}</span>
                    </h4>
                    
                    <div class="row">
                        @foreach($group->pendingInvitations as $invitation)
                        <div class="col-md-6 mb-3">
                            <div class="bg-white text-dark p-3 rounded">
                                <h6 class="mb-2">{{ $invitation->invitee->full_name }}</h6>
                                <p class="small mb-2">
                                    <strong>รหัส:</strong> {{ $invitation->invitee->username_std }}<br>
                                    <strong>เชิญเมื่อ:</strong> {{ $invitation->created_at->format('d/m/Y H:i') }}
                                </p>
                                @if($invitation->message)
                                    <p class="small text-muted mb-0">{{ $invitation->message }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Group Timeline/Activity (Future feature) -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card-custom p-4">
                    <h4 class="fw-bold mb-3">
                        <i class="fas fa-history me-2"></i>กิจกรรมล่าสุด
                    </h4>
                    
                    <div class="timeline">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 40px; height: 40px;">
                                <i class="fas fa-plus text-white"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">สร้างกลุ่มโครงงาน</h6>
                                <small class="text-muted">{{ $group->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        
                        @foreach($group->members as $member)
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 40px; height: 40px;">
                                <i class="fas fa-user-check text-white"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $member->student->full_name }} เข้าร่วมกลุ่ม</h6>
                                <small class="text-muted">{{ $member->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        @endforeach
                        
                        @foreach($group->invitations->where('status', 'accepted') as $invitation)
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-info rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 40px; height: 40px;">
                                <i class="fas fa-handshake text-white"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $invitation->invitee->full_name }} ตอบรับคำเชิญ</h6>
                                <small class="text-muted">{{ $invitation->responded_at?->diffForHumans() }}</small>
                            </div>
                        </div>
                        @endforeach
                        
                        @foreach($group->invitations->where('status', 'declined') as $invitation)
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 40px; height: 40px;">
                                <i class="fas fa-times text-white"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $invitation->invitee->full_name }} ปฏิเสธคำเชิญ</h6>
                                <small class="text-muted">{{ $invitation->responded_at?->diffForHumans() }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คำเชิญเข้าร่วมกลุ่ม - CSTU SPACE</title>
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
        
        .invitation-card {
            transition: transform 0.3s ease;
        }
        
        .invitation-card:hover {
            transform: translateY(-5px);
        }
        
        .status-pending {
            border-left: 5px solid #ffc107;
        }
        
        .status-accepted {
            border-left: 5px solid #28a745;
        }
        
        .status-declined {
            border-left: 5px solid #dc3545;
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
                        <i class="fas fa-envelope me-2"></i>คำเชิญเข้าร่วมกลุ่ม
                    </h2>
                    <p class="text-muted mb-0">รายการคำเชิญทั้งหมดของคุณ</p>
                </div>
                <a href="{{ route('student.menu') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>กลับหน้าหลัก
                </a>
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

        <!-- Invitations List -->
        @if($invitations->count() > 0)
            <div class="row">
                @foreach($invitations as $invitation)
                <div class="col-lg-6 mb-4">
                    <div class="card-custom invitation-card status-{{ $invitation->status }} p-4 h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold mb-1">{{ $invitation->group->project_name }}</h5>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>{{ $invitation->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <span class="badge 
                                @if($invitation->status === 'pending') bg-warning
                                @elseif($invitation->status === 'accepted') bg-success
                                @else bg-danger
                                @endif
                            ">
                                @if($invitation->status === 'pending') รอตอบรับ
                                @elseif($invitation->status === 'accepted') ตอบรับแล้ว
                                @else ปฏิเสธแล้ว
                                @endif
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <p class="mb-2">
                                <strong><i class="fas fa-user me-2 text-primary"></i>ผู้เชิญ:</strong> 
                                {{ $invitation->inviter->full_name }}
                            </p>
                            <p class="mb-2">
                                <strong><i class="fas fa-code me-2 text-info"></i>รหัสโครงงาน:</strong> 
                                {{ $invitation->group->project_code }}
                            </p>
                            <p class="mb-2">
                                <strong><i class="fas fa-book me-2 text-success"></i>วิชา:</strong> 
                                {{ $invitation->group->subject_code }}
                            </p>
                            <p class="mb-2">
                                <strong><i class="fas fa-calendar me-2 text-warning"></i>ปีการศึกษา:</strong> 
                                {{ $invitation->group->year }}/{{ $invitation->group->semester }}
                            </p>
                            
                            @if($invitation->message)
                            <div class="bg-light p-3 rounded mt-3">
                                <small class="text-muted d-block mb-1">ข้อความ:</small>
                                <p class="mb-0">{{ $invitation->message }}</p>
                            </div>
                            @endif
                            
                            @if($invitation->group->description)
                            <div class="mt-3">
                                <small class="text-muted d-block mb-1">คำอธิบายโครงงาน:</small>
                                <p class="small text-secondary mb-0">{{ Str::limit($invitation->group->description, 100) }}</p>
                            </div>
                            @endif
                        </div>
                        
                        @if($invitation->responded_at)
                            <div class="text-muted small">
                                <i class="fas fa-info-circle me-1"></i>
                                ตอบรับเมื่อ: {{ $invitation->responded_at->format('d/m/Y H:i') }}
                            </div>
                        @endif
                        
                        @if($invitation->isPending())
                            <div class="d-flex gap-2 mt-3">
                                <form action="{{ route('invitations.accept', $invitation) }}" method="POST" class="flex-fill">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100" 
                                            onclick="return confirm('ยืนยันการเข้าร่วมกลุ่มนี้?')">
                                        <i class="fas fa-check me-1"></i>ตอบรับ
                                    </button>
                                </form>
                                <form action="{{ route('invitations.decline', $invitation) }}" method="POST" class="flex-fill">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger w-100"
                                            onclick="return confirm('ยืนยันการปฏิเสธคำเชิญนี้?')">
                                        <i class="fas fa-times me-1"></i>ปฏิเสธ
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($invitations->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $invitations->links() }}
                </div>
            @endif
        @else
            <!-- No Invitations -->
            <div class="card-custom p-5 text-center">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h4 class="text-muted mb-3">ไม่มีคำเชิญ</h4>
                <p class="text-muted mb-4">คุณยังไม่มีคำเชิญเข้าร่วมกลุ่มใดๆ</p>
                <a href="{{ route('student.menu') }}" class="btn btn-primary">
                    <i class="fas fa-home me-1"></i>กลับหน้าหลัก
                </a>
            </div>
        @endif
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
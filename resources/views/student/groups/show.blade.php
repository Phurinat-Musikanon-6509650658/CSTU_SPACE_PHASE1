<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดกลุ่ม - CSTU SPACE</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
            --gradient-primary: linear-gradient(135deg, var(--color-blue) 0%, var(--color-dark-blue) 100%);
        }
        
        body {
            background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
            min-height: 100vh;
            font-family: 'Kanit', sans-serif;
            color: #333;
        }
        
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background: var(--gradient-primary);
            color: white;
            border-radius: 20px 20px 0 0 !important;
            padding: 1.5rem;
            font-weight: 600;
        }
        
        .btn-back {
            background: white;
            color: var(--color-blue);
            border: 2px solid white;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            background: var(--color-yellow);
            color: var(--color-black);
            border-color: var(--color-yellow);
            transform: translateY(-2px);
        }
        
        .page-header {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 500;
        }
        
        .table th {
            font-weight: 600;
            color: #666;
            border-bottom: 2px solid #dee2e6;
        }
        
        .member-badge {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .info-label {
            color: #666;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .info-value {
            color: #333;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid px-4 py-4">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('student.menu') }}" class="btn btn-back">
                <i class="bi bi-arrow-left me-2"></i>กลับสู่เมนูหลัก
            </a>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h1 class="mb-0">
                <i class="bi bi-folder-fill me-2" style="color: var(--color-blue);"></i>
                รายละเอียดกลุ่ม #{{ sprintf('%02d-%02d', $group->semester, $group->group_id) }}
            </h1>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('proposal_approved'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
                <h5 class="alert-heading">
                    <i class="bi bi-check-circle-fill me-2"></i>ข้อเสนอโครงงานได้รับการอนุมัติ!
                </h5>
                <hr>
                <p class="mb-0">{{ session('proposal_approved') }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('proposal_rejected'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm">
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

        <div class="row">
            <!-- Group Information -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="bi bi-info-circle me-2"></i>ข้อมูลกลุ่ม
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="info-label">ID กลุ่ม</div>
                            <div class="info-value">{{ sprintf('%02d-%02d', $group->semester, $group->group_id) }}</div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="info-label">รหัสวิชา</div>
                            <div class="info-value">{{ $group->subject_code }}</div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="info-label">ปีการศึกษา / ภาคการศึกษา</div>
                            <div class="info-value">{{ $group->year }} / {{ $group->semester }}</div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="info-label">สถานะกลุ่ม</div>
                            <div>
                                @if($group->status_group === 'pending')
                                    <span class="badge bg-warning">รออนุมัติ</span>
                                @elseif($group->status_group === 'approved')
                                    <span class="badge bg-success">อนุมัติแล้ว</span>
                                @else
                                    <span class="badge bg-secondary">{{ $group->status_group }}</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="info-label">คำอธิบาย</div>
                            <div class="info-value">{{ $group->description ?? '-' }}</div>
                        </div>
                        
                        <div class="mb-0">
                            <div class="info-label">สร้างเมื่อ</div>
                            <div class="info-value">{{ \Carbon\Carbon::parse($group->created_at)->locale('th')->translatedFormat('j M Y H:i') }} น.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Members -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="bi bi-people me-2"></i>สมาชิกกลุ่ม ({{ $group->members->count() }}/2)
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            @foreach($group->members as $index => $member)
                            <div class="list-group-item border-0 mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <span class="member-badge bg-primary text-white">
                                            {{ $index + 1 }}
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $member->student->firstname_std ?? 'N/A' }} {{ $member->student->lastname_std ?? '' }}</h6>
                                        <small class="text-muted">{{ $member->username_std }}</small>
                                        @if($index === 0)
                                            <span class="badge bg-info ms-2">ผู้สร้างกลุ่ม</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Information -->
            <div class="col-12">
                @if($group->project)
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-folder me-2"></i>ข้อมูลโครงงาน
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="info-label">รหัสโครงงาน</div>
                                <div class="info-value">{{ $group->project->project_code }}</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="info-label">ชื่อโครงงาน</div>
                                <div class="info-value">{{ $group->project->project_name ?? '-' }}</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="info-label">อาจารย์ที่ปรึกษา</div>
                                <div class="info-value">
                                    @if($group->project->advisor)
                                        {{ $group->project->advisor->firstname_user }} {{ $group->project->advisor->lastname_user }}
                                        <span class="text-muted">({{ $group->project->advisor_code }})</span>
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="info-label">กรรมการคนที่ 1</div>
                                <div class="info-value">
                                    @if($group->project->committee1)
                                        {{ $group->project->committee1->firstname_user }} {{ $group->project->committee1->lastname_user }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="info-label">กรรมการคนที่ 2</div>
                                <div class="info-value">
                                    @if($group->project->committee2)
                                        {{ $group->project->committee2->firstname_user }} {{ $group->project->committee2->lastname_user }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="info-label">กรรมการคนที่ 3</div>
                                <div class="info-value">
                                    @if($group->project->committee3)
                                        {{ $group->project->committee3->firstname_user }} {{ $group->project->committee3->lastname_user }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="info-label">วันเวลาสอบ</div>
                                <div class="info-value">
                                    {{ $group->project->exam_datetime ? \Carbon\Carbon::parse($group->project->exam_datetime)->locale('th')->translatedFormat('j M Y H:i') . ' น.' : '-' }}
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="info-label">ประเภทนักศึกษา</div>
                                <div class="info-value">
                                    @if($group->project->student_type === 'r')
                                        ภาคปกติ (r)
                                    @elseif($group->project->student_type === 's')
                                        ภาคพิเศษ (s)
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="info-label">ประเภทโครงงาน</div>
                                <div class="info-value">{{ $group->project->project_type ?? '-' }}</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="info-label">สถานะโครงงาน</div>
                                <div>
                                    @php
                                        $statusMap = [
                                            'not_proposed' => ['text' => 'ยังไม่เสนอ', 'class' => 'secondary'],
                                            'pending' => ['text' => 'รอดำเนินการ', 'class' => 'warning'],
                                            'approved' => ['text' => 'อนุมัติแล้ว', 'class' => 'success'],
                                            'rejected' => ['text' => 'ปฏิเสธ', 'class' => 'danger'],
                                            'in_progress' => ['text' => 'กำลังดำเนินการ', 'class' => 'info'],
                                            'late_submission' => ['text' => 'ส่งช้า', 'class' => 'danger'],
                                            'submitted' => ['text' => 'ส่งแล้ว', 'class' => 'success'],
                                        ];
                                        $status = $statusMap[$group->project->status_project] ?? ['text' => $group->project->status_project, 'class' => 'secondary'];
                                    @endphp
                                    <span class="badge bg-{{ $status['class'] }}">{{ $status['text'] }}</span>
                                </div>
                            </div>
                            
                            <!-- Submission Information -->
                            @if($group->project->submission_file)
                            <div class="col-12 mt-3">
                                <div class="alert alert-success">
                                    <h6 class="alert-heading">
                                        <i class="bi bi-file-earmark-pdf-fill me-2"></i>เล่มรายงาน
                                    </h6>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>ชื่อไฟล์:</strong> {{ $group->project->submission_original_name }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>ส่งเมื่อ:</strong> {{ \Carbon\Carbon::parse($group->project->submitted_at)->locale('th')->translatedFormat('j M Y H:i') }} น.
                                        </div>
                                        <div class="col-md-6 mt-2">
                                            <strong>ส่งโดย:</strong> {{ $group->project->submitted_by }}
                                        </div>
                                        <div class="col-md-6 mt-2">
                                            <a href="{{ route('student.submission.download', $group->project->project_id) }}" class="btn btn-success btn-sm">
                                                <i class="bi bi-download me-1"></i>ดาวน์โหลด
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @else
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <i class="bi bi-info-circle me-2"></i>โครงงาน
                    </div>
                    <div class="card-body text-center py-5">
                        <i class="bi bi-folder-x" style="font-size: 3rem; color: #ccc;"></i>
                        <h5 class="mt-3 text-muted">ยังไม่มีข้อมูลโครงงาน</h5>
                        <p class="text-muted">รอการอนุมัติจากผู้ประสานงาน</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

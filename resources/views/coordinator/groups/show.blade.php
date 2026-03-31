@extends('layouts.app')

@section('title', 'รายละเอียดกลุ่มและโครงงาน')

@push('styles')
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
    }
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.2s, box-shadow 0.2s;
        margin-bottom: 1.5rem;
    }
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }
    .card-header {
        font-weight: 600;
        font-size: 1.1rem;
        background-color: #fff;
        border-bottom: 2px solid #e9ecef;
        padding: 1rem 1.25rem;
    }
    .card-header h5 {
        color: inherit;
    }
    .card-header.text-white h5 {
        color: #ffffff !important;
    }
    .table {
        font-size: 0.95rem;
        margin-bottom: 0;
    }
    .table th {
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        padding: 1rem 0.75rem;
        white-space: nowrap;
        font-size: 0.9rem;
    }
    .table td {
        vertical-align: middle;
        padding: 1rem 0.75rem;
        line-height: 1.6;
    }
    .table td code {
        font-size: 0.9rem;
        padding: 0.25rem 0.5rem;
        background-color: #e9ecef;
        border-radius: 4px;
    }
    .badge {
        font-weight: 500;
        padding: 0.45em 0.85em;
        font-size: 0.85rem;
        white-space: nowrap;
    }
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }
    .form-control, .form-select {
        font-size: 0.95rem;
        padding: 0.6rem 0.75rem;
    }
    h1, h2, h3, h4, h5 {
        font-weight: 700;
        color: #212529;
    }
    .bg-light {
        background-color: #f8f9fa !important;
    }
    .text-muted {
        color: #6c757d !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('coordinator.groups.index') }}" class="btn btn-outline-primary btn-lg">
            <i class="bi bi-arrow-left me-2"></i>กลับไปรายการกลุ่ม
        </a>
    </div>

    <!-- Header with Project Info -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                     style="width: 60px; height: 60px;">
                    <h2 class="mb-0 fw-bold">{{ sprintf('%02d', $group->group_id) }}</h2>
                </div>
                <div>
                    <h1 class="mb-1 fw-bold">
                        กลุ่มที่ {{ $group->group_id }}
                        @if($group->project)
                            <span class="badge bg-success ms-2">มีโครงงาน</span>
                        @else
                            <span class="badge bg-warning ms-2">ยังไม่มีโครงงาน</span>
                        @endif
                    </h1>
                    @if($group->project)
                        <p class="mb-0 text-muted">
                            <i class="bi bi-code-square me-1"></i>
                            <code class="fs-5 text-primary">{{ $group->project->project_code }}</code>
                        </p>
                    @else
                        <p class="mb-0 text-muted">
                            <i class="bi bi-calendar3 me-1"></i>
                            ภาคเรียนที่ {{ $group->semester }}/{{ $group->year }} • วิชา {{ $group->subject_code }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
        <div class="text-end">
            <small class="text-muted d-block">สร้างเมื่อ</small>
            <strong>{{ $group->created_at->format('d/m/Y H:i') }}</strong>
        </div>
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

    <!-- ข้อมูลภาพรวม -->
    <div class="row mb-4 g-3">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                         style="width: 50px; height: 50px; background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                        <i class="bi bi-hash text-white fs-4"></i>
                    </div>
                    <h6 class="text-muted mb-1 small">กลุ่มที่</h6>
                    <h3 class="mb-0 fw-bold text-primary">{{ sprintf('%02d', $group->group_id) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                         style="width: 50px; height: 50px; background-color: #17a2b8;">
                        <i class="bi bi-people-fill text-white fs-4"></i>
                    </div>
                    <h6 class="text-muted mb-1 small">สมาชิก</h6>
                    <h3 class="mb-0 fw-bold text-info">{{ $group->members->count() }}</h3>
                    <small class="text-muted">คน</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                         style="width: 50px; height: 50px; background-color: #f6c23e;">
                        <i class="bi bi-calendar3 text-white fs-4"></i>
                    </div>
                    <h6 class="text-muted mb-1 small">ภาคเรียน</h6>
                    <h3 class="mb-0 fw-bold text-warning">{{ $group->semester }}/{{ substr($group->year, -2) }}</h3>
                    <small class="text-muted">{{ $group->subject_code }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    @php
                        $statusColors = [
                            'created' => ['bg' => 'success', 'icon' => 'check-circle-fill', 'text' => 'สร้างแล้ว'],
                            'not_created' => ['bg' => 'secondary', 'icon' => 'x-circle-fill', 'text' => 'ยังไม่สร้าง'],
                            'member_left' => ['bg' => 'warning', 'icon' => 'exclamation-triangle-fill', 'text' => 'สมาชิกออก'],
                            'member_added' => ['bg' => 'info', 'icon' => 'person-plus-fill', 'text' => 'เพิ่มสมาชิก'],
                            'disbanded' => ['bg' => 'danger', 'icon' => 'trash-fill', 'text' => 'ยุบกลุ่ม']
                        ];
                        $status = $statusColors[$group->status_group] ?? ['bg' => 'secondary', 'icon' => 'question-circle', 'text' => $group->status_group];
                    @endphp
                    @php
                        $bgColors = [
                            'success' => '#28a745',
                            'secondary' => '#6c757d',
                            'warning' => '#f6c23e',
                            'info' => '#17a2b8',
                            'danger' => '#dc3545'
                        ];
                        $bgColor = $bgColors[$status['bg']] ?? '#6c757d';
                    @endphp
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" 
                         style="width: 50px; height: 50px; background-color: {{ $bgColor }};">
                        <i class="bi bi-{{ $status['icon'] }} text-white fs-4"></i>
                    </div>
                    <h6 class="text-muted mb-1 small">สถานะกลุ่ม</h6>
                    <h6 class="mb-0 fw-bold">
                        <span class="badge bg-{{ $status['bg'] }} fs-6">{{ $status['text'] }}</span>
                    </h6>
                </div>
            </div>
        </div>
    </div>

    <!-- ตารางข้อมูลหลัก -->
    <div class="card mb-4 border-0 shadow">
        <div class="card-header text-white border-0" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0 text-white">
                    <i class="bi bi-table me-2"></i>ข้อมูลโครงงานและอาจารย์
                </h5>
                @if($group->project)
                    <span class="badge bg-white text-primary">
                        <i class="bi bi-check-circle-fill me-1"></i>มีข้อมูลแล้ว
                    </span>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            @if($group->project)
            <!-- มีโครงงานแล้ว - แสดงแบบตาราง -->
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead style="background-color: #000000ff;">
                        <tr>
                            <th width="5%" class="text-center text-white">ID</th>
                            <th width="10%" class="text-center text-white">ProjCode</th>
                            <th width="25%" class="text-white">ProjNameTH</th>
                            <th width="30%" class="text-white">สมาชิกในโครงงาน</th>
                            <th width="7%" class="text-center text-white">AdvId</th>
                            <th width="7%" class="text-center text-white">Comm1</th>
                            <th width="7%" class="text-center text-white">Comm2</th>
                            <th width="7%" class="text-center text-white">Comm3</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">
                                <div class="badge bg-primary rounded-circle p-2" style="width: 35px; height: 35px; line-height: 20px;">
                                    <strong>{{ $group->group_id }}</strong>
                                </div>
                            </td>
                            <td class="text-center">
                                <code class="bg-light px-2 py-1 rounded text-primary fw-bold">{{ $group->project->project_code }}</code>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-folder-fill text-warning me-2 fs-5"></i>
                                    <strong>{{ $group->project->project_name ?? '-' }}</strong>
                                </div>
                            </td>
                            <td>
                                @foreach($group->members as $index => $member)
                                    <div class="mb-1">
                                        <span class="badge rounded-pill bg-info me-1">{{ $index + 1 }}</span>
                                        <strong>{{ $member->student->firstname_std ?? 'N/A' }} {{ $member->student->lastname_std ?? '' }}</strong>
                                        <small class="text-muted">({{ $member->username_std }})</small>
                                    </div>
                                @endforeach
                            </td>
                            <td class="text-center">
                                @if($group->project->advisor_code)
                                    <span class="badge text-white" style="background: linear-gradient(135deg, #5a67d8 0%, #6610f2 100%);" 
                                          title="Advisor">
                                        {{ $group->project->advisor_code }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($group->project->committee1_code)
                                    <span class="badge bg-success" title="Committee">
                                        {{ $group->project->committee1_code }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($group->project->committee2_code)
                                    <span class="badge bg-success" title="Committee">
                                        {{ $group->project->committee2_code }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($group->project->committee3_code)
                                    <span class="badge bg-success" title="Committee">
                                        {{ $group->project->committee3_code }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ข้อมูลเพิ่มเติม -->
            <div class="p-4 bg-gradient" style="background: linear-gradient(135deg, #e2e6ea 0%, #adb5bd 100%);">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 bg-white rounded shadow-sm">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 50px; height: 50px; background-color: #dc3545;">
                                <i class="bi bi-calendar-event text-white fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">วันเวลาสอบ</small>
                                <strong class="text-danger fs-5">
                                    {{ $group->project->exam_datetime ? \Carbon\Carbon::parse($group->project->exam_datetime)->format('d/m/Y H:i น.') : 'ยังไม่กำหนด' }}
                                </strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 bg-white rounded shadow-sm">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 50px; height: 50px; background-color: #17a2b8;">
                                <i class="bi bi-tag text-white fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">ประเภทโครงงาน</small>
                                <strong class="text-dark fs-5">{{ $group->project->project_type ?? 'ยังไม่ระบุ' }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 bg-white rounded shadow-sm">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 50px; height: 50px; background-color: #f6c23e;">
                                <i class="bi bi-flag text-white fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">สถานะโครงงาน</small>
                                @php
                                    $projectStatusMap = [
                                        'not_proposed' => ['label' => 'ยังไม่เสนอ', 'color' => 'secondary'],
                                        'pending' => ['label' => 'รออนุมัติ', 'color' => 'warning'],
                                        'approved' => ['label' => 'อนุมัติแล้ว', 'color' => 'success'],
                                        'rejected' => ['label' => 'ปฏิเสธ', 'color' => 'danger'],
                                        'in_progress' => ['label' => 'กำลังดำเนินการ', 'color' => 'info'],
                                        'late_submission' => ['label' => 'ส่งช้า', 'color' => 'danger'],
                                        'submitted' => ['label' => 'ส่งแล้ว', 'color' => 'primary']
                                    ];
                                    $status = $projectStatusMap[$group->project->status_project] ?? ['label' => $group->project->status_project, 'color' => 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $status['color'] }} fs-6">{{ $status['label'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 bg-white rounded shadow-sm">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 50px; height: 50px; background-color: #28a745;">
                                <i class="bi bi-person-badge text-white fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">ประเภทนักศึกษา</small>
                                <strong class="text-dark fs-5">
                                    {{ $group->project->student_type == 'r' ? '🎓 ปกติ (Regular)' : '⭐ พิเศษ (Special)' }}
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @else
            <!-- ยังไม่มีโครงงาน -->
            <div class="p-4 text-center">
                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                <h4 class="mt-3 text-muted">ยังไม่มีโครงงาน</h4>
                <p class="text-muted">กรุณาสร้างโครงงานด้านล่าง</p>
            </div>
            @endif
        </div>
    </div>

    <!-- ฟอร์มแก้ไขข้อมูล (Coordinator/Admin only) -->
    @if($group->project && Auth::guard('web')->user()->canEdit())
    <div class="card border-0 shadow">
        <div class="card-header text-white border-0" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
            <h5 class="mb-0 text-white">
                <i class="bi bi-pencil-square me-2"></i>แก้ไขข้อมูลโครงงาน
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('coordinator.projects.update', $group->project->project_id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Column 1 -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-card-text text-primary me-1"></i>ชื่อโครงงาน
                            </label>
                            <input type="text" name="project_name" class="form-control" 
                                   value="{{ $group->project->project_name }}" 
                                   placeholder="ระบุชื่อโครงงาน">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-person-badge text-primary me-1"></i>อาจารย์ที่ปรึกษา (Advisor)
                            </label>
                            <select name="advisor_code" class="form-select">
                                <option value="">-- ไม่มี --</option>
                                @foreach($lecturers as $lecturer)
                                    <option value="{{ $lecturer->user_code }}" 
                                            {{ $group->project->advisor_code == $lecturer->user_code ? 'selected' : '' }}>
                                        {{ $lecturer->user_code }} - {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-person-check text-success me-1"></i>กรรมการคนที่ 1 (Committee 1)
                            </label>
                            <select name="committee1_code" class="form-select">
                                <option value="">-- ไม่มี --</option>
                                @foreach($lecturers as $lecturer)
                                    <option value="{{ $lecturer->user_code }}" 
                                            {{ $group->project->committee1_code == $lecturer->user_code ? 'selected' : '' }}>
                                        {{ $lecturer->user_code }} - {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-person-check text-success me-1"></i>กรรมการคนที่ 2 (Committee 2)
                            </label>
                            <select name="committee2_code" class="form-select">
                                <option value="">-- ไม่มี --</option>
                                @foreach($lecturers as $lecturer)
                                    <option value="{{ $lecturer->user_code }}" 
                                            {{ $group->project->committee2_code == $lecturer->user_code ? 'selected' : '' }}>
                                        {{ $lecturer->user_code }} - {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-person-check text-success me-1"></i>กรรมการคนที่ 3 (Committee 3)
                            </label>
                            <select name="committee3_code" class="form-select">
                                <option value="">-- ไม่มี --</option>
                                @foreach($lecturers as $lecturer)
                                    <option value="{{ $lecturer->user_code }}" 
                                            {{ $group->project->committee3_code == $lecturer->user_code ? 'selected' : '' }}>
                                        {{ $lecturer->user_code }} - {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Column 2 -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-calendar-event text-danger me-1"></i>วันเวลาสอบ
                            </label>
                            <input type="datetime-local" name="exam_datetime" class="form-control" 
                                   value="{{ $group->project->exam_datetime ? \Carbon\Carbon::parse($group->project->exam_datetime)->format('Y-m-d\TH:i') : '' }}">
                            <small class="text-muted">กำหนดวันและเวลาที่จะสอบโครงงาน</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-tag text-info me-1"></i>ประเภทโครงงาน
                            </label>
                            <input type="text" name="project_type" class="form-control" 
                                   value="{{ $group->project->project_type }}" 
                                   placeholder="เช่น soft-en, ai, network, datasci">
                            <small class="text-muted">คั่นหลายประเภทด้วย comma</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-flag text-warning me-1"></i>สถานะโครงงาน
                            </label>
                            <select name="status_project" class="form-select">
                                <option value="not_proposed" {{ $group->project->status_project == 'not_proposed' ? 'selected' : '' }}>ยังไม่เสนอ</option>
                                <option value="pending" {{ $group->project->status_project == 'pending' ? 'selected' : '' }}>รออนุมัติ</option>
                                <option value="approved" {{ $group->project->status_project == 'approved' ? 'selected' : '' }}>อนุมัติแล้ว</option>
                                <option value="rejected" {{ $group->project->status_project == 'rejected' ? 'selected' : '' }}>ปฏิเสธ</option>
                                <option value="in_progress" {{ $group->project->status_project == 'in_progress' ? 'selected' : '' }}>กำลังดำเนินการ</option>
                                <option value="late_submission" {{ $group->project->status_project == 'late_submission' ? 'selected' : '' }}>ส่งช้า</option>
                                <option value="submitted" {{ $group->project->status_project == 'submitted' ? 'selected' : '' }}>ส่งแล้ว</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-person-badge text-info me-1"></i>ประเภทนักศึกษา
                            </label>
                            <div class="form-text mb-2">{{ $group->project->student_type == 'r' ? 'ปกติ (Regular)' : 'พิเศษ (Special)' }}</div>
                            <small class="text-muted">ไม่สามารถแก้ไขได้หลังสร้าง</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-code-square text-secondary me-1"></i>รหัสโครงงาน
                            </label>
                            <input type="text" class="form-control" value="{{ $group->project->project_code }}" readonly>
                            <small class="text-muted">ไม่สามารถแก้ไขได้</small>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save me-2"></i>บันทึกการเปลี่ยนแปลง
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- กรณียังไม่มีโครงงาน -->
    @elseif(!$group->project && Auth::guard('web')->user()->canEdit())
    <div class="card">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);">
            <h5 class="mb-0 text-white"><i class="bi bi-plus-circle me-2"></i>สร้างโครงงานใหม่</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('coordinator.groups.approve', $group->group_id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold">ชื่อโครงงาน *</label>
                    <input type="text" name="project_name" class="form-control" required placeholder="ระบุชื่อโครงงาน">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">อาจารย์ที่ปรึกษา *</label>
                    <select name="advisor_code" class="form-select" required>
                        <option value="">-- เลือกอาจารย์ที่ปรึกษา --</option>
                        @foreach($lecturers as $lecturer)
                            <option value="{{ $lecturer->user_code }}">
                                {{ $lecturer->user_code }} - {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">ประเภทนักศึกษา *</label>
                    <select name="student_type" class="form-select" required>
                        <option value="r">ภาคปกติ (Regular)</option>
                        <option value="s">ภาคพิเศษ (Special)</option>
                    </select>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-check-circle me-2"></i>สร้างโครงงาน
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Staff - Read Only -->
    @elseif(!$group->project)
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-lock text-muted" style="font-size: 3rem;"></i>
            <h5 class="text-muted mt-3">กลุ่มนี้ยังไม่มีโครงงาน</h5>
            <p class="text-muted">คุณไม่มีสิทธิ์สร้างโครงงาน (Staff read-only)</p>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const advisorSelect = document.querySelector('select[name="advisor_code"]');
    const committee1Select = document.querySelector('select[name="committee1_code"]');
    const committee2Select = document.querySelector('select[name="committee2_code"]');
    const committee3Select = document.querySelector('select[name="committee3_code"]');
    
    if (!advisorSelect || !committee1Select || !committee2Select || !committee3Select) {
        return; // ถ้าไม่มี form อย่าทำอะไร
    }
    
    function validateCommitteeSelection() {
        const advisor = advisorSelect.value;
        const comm1 = committee1Select.value;
        const comm2 = committee2Select.value;
        const comm3 = committee3Select.value;
        
        const selectedLecturers = [];
        const warnings = [];
        
        // ตรวจสอบ Advisor
        if (advisor) {
            selectedLecturers.push({type: 'Advisor', code: advisor});
        }
        
        // ตรวจสอบ Committee 1
        if (comm1) {
            if (comm1 === advisor) {
                warnings.push('⚠️ Committee 1 ไม่สามารถเป็นคนเดียวกับ Advisor ได้');
            }
            selectedLecturers.push({type: 'Committee 1', code: comm1});
        }
        
        // ตรวจสอบ Committee 2
        if (comm2) {
            if (comm2 === advisor) {
                warnings.push('⚠️ Committee 2 ไม่สามารถเป็นคนเดียวกับ Advisor ได้');
            }
            if (comm2 === comm1) {
                warnings.push('⚠️ Committee 2 ไม่สามารถเป็นคนเดียวกับ Committee 1 ได้');
            }
            selectedLecturers.push({type: 'Committee 2', code: comm2});
        }
        
        // ตรวจสอบ Committee 3
        if (comm3) {
            if (comm3 === advisor) {
                warnings.push('⚠️ Committee 3 ไม่สามารถเป็นคนเดียวกับ Advisor ได้');
            }
            if (comm3 === comm1) {
                warnings.push('⚠️ Committee 3 ไม่สามารถเป็นคนเดียวกับ Committee 1 ได้');
            }
            if (comm3 === comm2) {
                warnings.push('⚠️ Committee 3 ไม่สามารถเป็นคนเดียวกับ Committee 2 ได้');
            }
            selectedLecturers.push({type: 'Committee 3', code: comm3});
        }
        
        // แสดง warning
        const existingAlert = document.querySelector('.lecturer-validation-alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        if (warnings.length > 0) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-warning alert-dismissible fade show lecturer-validation-alert';
            alertDiv.innerHTML = `
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>พบปัญหาการเลือกอาจารย์:</strong>
                <ul class="mb-0 mt-2">
                    ${warnings.map(w => `<li>${w}</li>`).join('')}
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            advisorSelect.closest('form').insertBefore(alertDiv, advisorSelect.closest('form').firstChild);
        }
    }
    
    // ฟังการเปลี่ยนแปลงของทุก select
    advisorSelect.addEventListener('change', validateCommitteeSelection);
    committee1Select.addEventListener('change', validateCommitteeSelection);
    committee2Select.addEventListener('change', validateCommitteeSelection);
    committee3Select.addEventListener('change', validateCommitteeSelection);
    
    // ตรวจสอบตอน submit form
    const form = advisorSelect.closest('form');
    form.addEventListener('submit', function(e) {
        const advisor = advisorSelect.value;
        const comm1 = committee1Select.value;
        const comm2 = committee2Select.value;
        const comm3 = committee3Select.value;
        
        // ตรวจสอบการซ้ำกัน
        const lecturers = [advisor, comm1, comm2, comm3].filter(v => v !== '');
        const uniqueLecturers = [...new Set(lecturers)];
        
        if (lecturers.length !== uniqueLecturers.length) {
            e.preventDefault();
            alert('❌ พบการเลือกอาจารย์ซ้ำกัน!\n\nกรุณาเลือกอาจารย์ที่แตกต่างกันสำหรับแต่ละตำแหน่ง');
            return false;
        }
    });
});
</script>
@endpush
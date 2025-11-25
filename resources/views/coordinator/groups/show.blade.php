@extends('layouts.app')

@section('title', 'รายละเอียดกลุ่ม')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('coordinator.groups.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>กลับไปรายการกลุ่ม
        </a>
    </div>

    <h1 class="mb-4">รายละเอียดกลุ่ม #{{ sprintf('%02d-%02d', $group->semester, $group->group_id) }}</h1>

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

    <div class="row">
        <!-- Group Information -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-info-circle me-1"></i>ข้อมูลกลุ่ม
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">ID กลุ่ม:</th>
                            <td><strong>{{ sprintf('%02d-%02d', $group->semester, $group->group_id) }}</strong></td>
                        </tr>
                        <tr>
                            <th>รหัสวิชา:</th>
                            <td>{{ $group->subject_code }}</td>
                        </tr>
                        <tr>
                            <th>ปีการศึกษา:</th>
                            <td>{{ $group->year }}</td>
                        </tr>
                        <tr>
                            <th>ภาคการศึกษา:</th>
                            <td>{{ $group->semester }}</td>
                        </tr>
                        <tr>
                            <th>สถานะกลุ่ม:</th>
                            <td>
                                @if($group->status_group === 'pending')
                                    <span class="badge bg-warning">รออนุมัติ</span>
                                @elseif($group->status_group === 'approved')
                                    <span class="badge bg-success">อนุมัติแล้ว</span>
                                @else
                                    <span class="badge bg-secondary">{{ $group->status_group }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>สร้างเมื่อ:</th>
                            <td>{{ $group->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Members -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-people me-1"></i>สมาชิกกลุ่ม ({{ $group->members->count() }}/2)
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($group->members as $index => $member)
                        <div class="list-group-item">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="badge bg-primary rounded-circle" style="width: 40px; height: 40px; line-height: 30px; font-size: 1.2rem;">
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
        <div class="col-lg-6">
            @if(!$group->project)
                <!-- Approve Group Form -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-white">
                        <i class="bi bi-exclamation-triangle me-1"></i>อนุมัติกลุ่มและสร้างโครงงาน
                    </div>
                    <div class="card-body">
                        <form action="{{ route('coordinator.groups.approve', $group->group_id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">ชื่อโครงงาน *</label>
                                <input type="text" name="project_name" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">อาจารย์ที่ปรึกษา *</label>
                                <select name="advisor_code" class="form-select" required>
                                    <option value="">เลือกอาจารย์ที่ปรึกษา</option>
                                    @foreach($lecturers as $lecturer)
                                        <option value="{{ $lecturer->user_code }}">
                                            {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }} ({{ $lecturer->user_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ประเภทนักศึกษา *</label>
                                <select name="student_type" class="form-select" required>
                                    <option value="r">ภาคปกติ (r)</option>
                                    <option value="s">ภาคพิเศษ (s)</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-circle"></i> อนุมัติและสร้างโครงงาน
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <!-- Project Details -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-folder me-1"></i>ข้อมูลโครงงาน
                    </div>
                    <div class="card-body">
                        <form action="{{ route('coordinator.projects.update', $group->group_id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">รหัสโครงงาน</label>
                                <input type="text" class="form-control" value="{{ $group->project->project_code }}" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ชื่อโครงงาน</label>
                                <input type="text" name="project_name" class="form-control" value="{{ $group->project->project_name }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">อาจารย์ที่ปรึกษา</label>
                                <select name="advisor_code" class="form-select">
                                    <option value="">ไม่มี</option>
                                    @foreach($lecturers as $lecturer)
                                        <option value="{{ $lecturer->user_code }}" {{ $group->project->advisor_code == $lecturer->user_code ? 'selected' : '' }}>
                                            {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }} ({{ $lecturer->user_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">กรรมการคนที่ 1</label>
                                <select name="committee1_code" class="form-select">
                                    <option value="">ไม่มี</option>
                                    @foreach($lecturers as $lecturer)
                                        <option value="{{ $lecturer->user_code }}" {{ $group->project->committee1_code == $lecturer->user_code ? 'selected' : '' }}>
                                            {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }} ({{ $lecturer->user_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">กรรมการคนที่ 2</label>
                                <select name="committee2_code" class="form-select">
                                    <option value="">ไม่มี</option>
                                    @foreach($lecturers as $lecturer)
                                        <option value="{{ $lecturer->user_code }}" {{ $group->project->committee2_code == $lecturer->user_code ? 'selected' : '' }}>
                                            {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }} ({{ $lecturer->user_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">กรรมการคนที่ 3</label>
                                <select name="committee3_code" class="form-select">
                                    <option value="">ไม่มี</option>
                                    @foreach($lecturers as $lecturer)
                                        <option value="{{ $lecturer->user_code }}" {{ $group->project->committee3_code == $lecturer->user_code ? 'selected' : '' }}>
                                            {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }} ({{ $lecturer->user_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">วันเวลาสอบ</label>
                                <input type="datetime-local" name="exam_datetime" class="form-control" 
                                       value="{{ $group->project->exam_datetime ? $group->project->exam_datetime->format('Y-m-d\TH:i') : '' }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ประเภทโครงงาน</label>
                                <input type="text" name="project_type" class="form-control" 
                                       value="{{ $group->project->project_type }}" 
                                       placeholder="soft-en,ai,network,datasci (คั่นด้วย comma)">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">สถานะโครงงาน</label>
                                <select name="status_project" class="form-select">
                                    <option value="pending" {{ $group->project->status_project == 'pending' ? 'selected' : '' }}>รอดำเนินการ</option>
                                    <option value="in_progress" {{ $group->project->status_project == 'in_progress' ? 'selected' : '' }}>กำลังดำเนินการ</option>
                                    <option value="submitted" {{ $group->project->status_project == 'submitted' ? 'selected' : '' }}>ส่งแล้ว</option>
                                    <option value="late" {{ $group->project->status_project == 'late' ? 'selected' : '' }}>ส่งช้า</option>
                                    <option value="completed" {{ $group->project->status_project == 'completed' ? 'selected' : '' }}>เสร็จสิ้น</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-save"></i> บันทึกการเปลี่ยนแปลง
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

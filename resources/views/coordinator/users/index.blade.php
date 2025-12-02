@extends('layouts.app')

@section('title', 'จัดการผู้ใช้งาน | ผู้ประสานงาน')

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-people-fill me-2"></i>
                    จัดการผู้ใช้งานระบบ
                </h2>
                <p class="mb-0 opacity-75">ดูข้อมูล นำเข้า และส่งออกข้อมูลผู้ใช้งาน (ผู้ประสานงาน - ดูได้อย่างเดียว)</p>
            </div>
            <a href="{{ route('menu') }}" class="btn modern-btn btn-light">
                <i class="bi bi-arrow-left"></i>
                <span>กลับเมนูหลัก</span>
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-modern alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-modern alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('import_errors'))
        <div class="alert alert-warning alert-modern alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Import Issues Found:</strong>
            <ul class="mb-0 mt-2">
                @foreach(session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Modern Tabs -->
    <div class="modern-tabs-container mb-4">
        <ul class="nav nav-pills nav-modern" id="userTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="user-tab" data-bs-toggle="tab" data-bs-target="#user-panel" type="button" role="tab">
                    <i class="bi bi-person-badge me-2"></i>ผู้ใช้งานระบบ
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="student-tab" data-bs-toggle="tab" data-bs-target="#student-panel" type="button" role="tab">
                    <i class="bi bi-mortarboard me-2"></i>นักศึกษา
                </button>
            </li>
        </ul>
    </div>

    <!-- Tab Content -->
    <div class="tab-content" id="userTabsContent">
        <!-- User Tab -->
        <div class="tab-pane fade show active" id="user-panel" role="tabpanel">
            <div class="management-section">
                <div class="section-header">
                    <h4><i class="bi bi-person-badge me-2"></i>ผู้ใช้งานระบบ</h4>
                    <div class="action-buttons">
                        <a href="{{ route('coordinator.users.export') }}" class="btn modern-btn btn-info">
                            <i class="bi bi-download"></i>
                            <span>ส่งออก CSV</span>
                        </a>
                        <a href="{{ route('coordinator.users.importForm') }}" class="btn modern-btn btn-success-modern">
                            <i class="bi bi-file-earmark-arrow-up"></i>
                            <span>นำเข้า CSV</span>
                        </a>
                    </div>
                </div>

                <div class="modern-card table-modern">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-header">
                                <tr>
                                    <th>รหัส</th>
                                    <th>Username</th>
                                    <th>ชื่อ-นามสกุล</th>
                                    <th>อีเมล</th>
                                    <th>Role</th>
                                    <th>User Code</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td><span class="badge bg-light text-dark">{{ $user->user_id }}</span></td>
                                    <td><strong>{{ $user->username_user }}</strong></td>
                                    <td>{{ $user->firstname_user }} {{ $user->lastname_user }}</td>
                                    <td>{{ $user->email_user }}</td>
                                    <td>
                                        @php
                                            $roles = [];
                                            if (($user->role & 32768) !== 0) $roles[] = '<span class="role-badge role-admin"><i class="bi bi-shield-fill"></i> Admin</span>';
                                            if (($user->role & 16384) !== 0) $roles[] = '<span class="role-badge role-coordinator"><i class="bi bi-person-gear"></i> Coordinator</span>';
                                            if (($user->role & 8192) !== 0) $roles[] = '<span class="role-badge role-advisor"><i class="bi bi-person-check"></i> Lecturer</span>';
                                            if (($user->role & 4096) !== 0) $roles[] = '<span class="role-badge role-staff"><i class="bi bi-briefcase"></i> Staff</span>';
                                            if (($user->role & 2048) !== 0) $roles[] = '<span class="role-badge role-student"><i class="bi bi-mortarboard"></i> Student</span>';
                                        @endphp
                                        
                                        @if(count($roles) > 0)
                                            <div class="d-flex flex-wrap gap-1">
                                                {!! implode(' ', $roles) !!}
                                            </div>
                                        @else
                                            <span class="role-badge role-other">Unknown ({{ $user->role }})</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->user_code)
                                            <code>{{ $user->user_code }}</code>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="bi bi-person-x text-muted fs-1"></i>
                                            <p class="text-muted mt-2 mb-0">ไม่พบข้อมูลผู้ใช้งาน</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($users->hasPages())
                    <div class="card-footer bg-white border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                แสดง {{ $users->firstItem() ?? 0 }} ถึง {{ $users->lastItem() ?? 0 }} จากทั้งหมด {{ $users->total() }} รายการ
                            </small>
                            {{ $users->appends(['student_page' => request('student_page')])->links() }}
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Information Note -->
                <div class="alert alert-info alert-modern mt-3">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>หมายเหตุ:</strong> Coordinator สามารถดูข้อมูล Import และ Export ได้เท่านั้น 
                    ไม่สามารถเพิ่ม แก้ไข หรือกำหนด Role ให้ผู้ใช้ได้ (เฉพาะ Admin เท่านั้น)
                </div>
            </div>
        </div>

        <!-- Student Tab -->
        <div class="tab-pane fade" id="student-panel" role="tabpanel">
            <div class="management-section">
                <div class="section-header">
                    <h4><i class="bi bi-mortarboard me-2"></i>นักศึกษา</h4>
                    <div class="action-buttons">
                        <a href="{{ route('coordinator.students.export') }}" class="btn modern-btn btn-info">
                            <i class="bi bi-download"></i>
                            <span>ส่งออก CSV</span>
                        </a>
                        <a href="{{ route('coordinator.students.importForm') }}" class="btn modern-btn btn-success-modern">
                            <i class="bi bi-file-earmark-arrow-up"></i>
                            <span>นำเข้า CSV</span>
                        </a>
                    </div>
                </div>

                <div class="modern-card table-modern">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-header">
                                <tr>
                                    <th>รหัส</th>
                                    <th>รหัสนักศึกษา</th>
                                    <th>ชื่อ-นามสกุล</th>
                                    <th>อีเมล</th>
                                    <th>รหัสวิชา</th>
                                    <th>เทอม/ปีการศึกษา</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                <tr>
                                    <td><span class="badge bg-light text-dark">{{ $student->student_id }}</span></td>
                                    <td><strong>{{ $student->username_std }}</strong></td>
                                    <td>{{ $student->firstname_std }} {{ $student->lastname_std }}</td>
                                    <td>{{ $student->email_std }}</td>
                                    <td>
                                        @if($student->course_code)
                                            <span class="badge bg-primary">{{ $student->course_code }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($student->semester && $student->year)
                                            <span class="badge bg-info">{{ $student->semester }}/{{ $student->year }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="bi bi-mortarboard text-muted fs-1"></i>
                                            <p class="text-muted mt-2 mb-0">ไม่พบข้อมูลนักศึกษา</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($students->hasPages())
                    <div class="card-footer bg-white border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                แสดง {{ $students->firstItem() ?? 0 }} ถึง {{ $students->lastItem() ?? 0 }} จากทั้งหมด {{ $students->total() }} รายการ
                            </small>
                            {{ $students->appends(['user_page' => request('user_page')])->links() }}
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Information Note -->
                <div class="alert alert-info alert-modern mt-3">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>หมายเหตุ:</strong> Coordinator สามารถดูข้อมูล Import และ Export นักศึกษาได้เท่านั้น 
                    ไม่สามารถเพิ่ม แก้ไข หรือลบข้อมูลได้
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .management-section {
        margin-bottom: 2rem;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding: 1.5rem;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-light);
    }

    .section-header h4 {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 0;
    }

    .action-buttons {
        display: flex;
        gap: 0.75rem;
    }

    .action-buttons-table {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
    }

    .modern-tabs-container {
        background: white;
        padding: 1rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-light);
    }

    .nav-modern .nav-link {
        border-radius: 50px;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        color: #6c757d;
        border: 2px solid transparent;
        transition: var(--transition);
        margin-right: 0.5rem;
    }

    .nav-modern .nav-link:hover {
        background: var(--gradient-primary);
        color: white;
        transform: translateY(-2px);
    }

    .nav-modern .nav-link.active {
        background: var(--gradient-primary);
        color: white;
        border-color: #667eea;
    }

    .table-header th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        font-weight: 600;
        color: #2c3e50;
        border: none;
        padding: 1rem 0.75rem;
    }

    .table-hover tbody tr {
        transition: var(--transition);
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }

    .role-badge {
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .role-admin {
        background: linear-gradient(45deg, #ff6b6b, #ee5a24);
        color: white;
    }

    .role-coordinator {
        background: linear-gradient(45deg, #4834d4, #686de0);
        color: white;
    }
    
    .role-advisor {
        background: linear-gradient(45deg, #0abde3, #006ba6);
        color: white;
    }

    .role-staff {
        background: linear-gradient(45deg, #f39c12, #e67e22);
        color: white;
    }
    
    .role-student {
        background: linear-gradient(45deg, #55a3ff, #003d82);
        color: white;
    }

    .role-other {
        background: linear-gradient(45deg, #95a5a6, #34495e);
        color: white;
    }

    .empty-state {
        padding: 2rem;
    }

    .btn-sm.modern-btn {
        padding: 0.5rem 1rem;
        border-radius: 25px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .section-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .action-buttons {
            flex-direction: column;
            width: 100%;
        }

        .action-buttons .btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('menu') }}" class="btn btn-outline-primary btn-lg">
            <i class="bi bi-arrow-left me-2"></i>กลับไปหน้าเมนูหลัก
        </a>
    </div>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-2">
                <i class="bi bi-people-fill me-3" style="color: #667eea;"></i>
                User & Student Management
            </h1>
            <p class="text-muted mb-0 fs-5">
                <i class="bi bi-info-circle me-2"></i>
                ดูข้อมูล Import และ Export (Coordinator - Read Only)
            </p>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('import_errors'))
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Import Issues Found:</strong>
            <ul class="mb-0 mt-2">
                @foreach(session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tabs -->
    <ul class="nav nav-pills mb-4" id="userTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="user-tab" data-bs-toggle="tab" data-bs-target="#user-panel" type="button" role="tab">
                <i class="bi bi-person-badge me-2"></i>Users
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="student-tab" data-bs-toggle="tab" data-bs-target="#student-panel" type="button" role="tab">
                <i class="bi bi-mortarboard me-2"></i>Students
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="userTabsContent">
        <!-- User Tab -->
        <div class="tab-pane fade show active" id="user-panel" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-person-badge me-2"></i>
                            System Users <span class="badge bg-light text-dark ms-2">{{ $users->total() }} รายการ</span>
                        </h5>
                        <div>
                            <a href="{{ route('coordinator.users.export') }}" class="btn btn-info me-2">
                                <i class="bi bi-download me-2"></i>Export CSV
                            </a>
                            <a href="{{ route('coordinator.users.importForm') }}" class="btn btn-success">
                                <i class="bi bi-file-earmark-arrow-up me-2"></i>Import CSV
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" width="8%">ID</th>
                                    <th width="15%">Username</th>
                                    <th width="25%">Name</th>
                                    <th width="25%">Email</th>
                                    <th class="text-center" width="12%">User Code</th>
                                    <th class="text-center" width="15%">Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td class="text-center">
                                        <div class="badge bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                                             style="width: 35px; height: 35px; font-size: 0.9rem;">
                                            {{ $user->user_id }}
                                        </div>
                                    </td>
                                    <td>
                                        <code class="text-primary fw-bold">{{ $user->username_user }}</code>
                                    </td>
                                    <td>
                                        <i class="bi bi-person-circle text-muted me-2"></i>
                                        <strong>{{ $user->firstname_user }} {{ $user->lastname_user }}</strong>
                                    </td>
                                    <td>
                                        <i class="bi bi-envelope text-muted me-2"></i>
                                        <small class="text-muted">{{ $user->email_user }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-dark">{{ $user->user_code }}</span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $roleColors = [
                                                'Admin' => 'danger',
                                                'Coordinator' => 'success',
                                                'Lecturer' => 'primary',
                                                'Staff' => 'info',
                                                'Student' => 'secondary'
                                            ];
                                            $roleName = $roleMap[$user->role] ?? 'Unknown';
                                            $badgeColor = $roleColors[$roleName] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $badgeColor }}">
                                            <i class="bi bi-shield-check me-1"></i>{{ $roleName }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-1 d-block mb-3" style="font-size: 4rem; opacity: 0.3;"></i>
                                        <h5 class="text-muted">No users found</h5>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($users->hasPages())
                <div class="card-footer bg-light border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} entries
                        </small>
                        {{ $users->appends(['student_page' => request('student_page')])->links() }}
                    </div>
                </div>
                @endif
            </div>

            <!-- Information Note -->
            <div class="alert alert-info border-0 shadow-sm">
                <div class="d-flex align-items-start">
                    <i class="bi bi-info-circle-fill me-3 fs-4" style="color: #0dcaf0;"></i>
                    <div>
                        <h6 class="alert-heading fw-bold mb-2">หมายเหตุสำหรับ Coordinator</h6>
                        <p class="mb-0">
                            Coordinator สามารถ <strong>ดูข้อมูล</strong>, <strong>Import</strong> และ <strong>Export</strong> ได้เท่านั้น<br>
                            <small class="text-muted">ไม่สามารถเพิ่ม แก้ไข หรือกำหนด Role ให้ผู้ใช้ได้ (เฉพาะ Admin เท่านั้น)</small>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Tab -->
        <div class="tab-pane fade" id="student-panel" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-mortarboard me-2"></i>
                            Students <span class="badge bg-light text-dark ms-2">{{ $students->total() }} รายการ</span>
                        </h5>
                        <div>
                            <a href="{{ route('coordinator.students.export') }}" class="btn btn-info me-2">
                                <i class="bi bi-download me-2"></i>Export CSV
                            </a>
                            <a href="{{ route('coordinator.students.importForm') }}" class="btn btn-success">
                                <i class="bi bi-file-earmark-arrow-up me-2"></i>Import CSV
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" width="8%">ID</th>
                                    <th width="20%">Username (Student ID)</th>
                                    <th width="32%">Name</th>
                                    <th width="30%">Email</th>
                                    <th class="text-center" width="10%">Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                <tr>
                                    <td class="text-center">
                                        <div class="badge bg-success rounded-circle d-inline-flex align-items-center justify-content-center" 
                                             style="width: 35px; height: 35px; font-size: 0.9rem;">
                                            {{ $student->student_id }}
                                        </div>
                                    </td>
                                    <td>
                                        <code class="text-success fw-bold">{{ $student->username_std }}</code>
                                    </td>
                                    <td>
                                        <i class="bi bi-person-circle text-muted me-2"></i>
                                        <strong>{{ $student->firstname_std }} {{ $student->lastname_std }}</strong>
                                    </td>
                                    <td>
                                        <i class="bi bi-envelope text-muted me-2"></i>
                                        <small class="text-muted">{{ $student->email_std }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-mortarboard-fill me-1"></i>Student
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-1 d-block mb-3" style="font-size: 4rem; opacity: 0.3;"></i>
                                        <h5 class="text-muted">No students found</h5>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($students->hasPages())
                <div class="card-footer bg-light border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Showing {{ $students->firstItem() ?? 0 }} to {{ $students->lastItem() ?? 0 }} of {{ $students->total() }} entries
                        </small>
                        {{ $students->appends(['user_page' => request('user_page')])->links() }}
                    </div>
                </div>
                @endif
            </div>

            <!-- Information Note -->
            <div class="alert alert-info border-0 shadow-sm">
                <div class="d-flex align-items-start">
                    <i class="bi bi-info-circle-fill me-3 fs-4" style="color: #0dcaf0;"></i>
                    <div>
                        <h6 class="alert-heading fw-bold mb-2">หมายเหตุสำหรับ Coordinator</h6>
                        <p class="mb-0">
                            Coordinator สามารถ <strong>ดูข้อมูล</strong>, <strong>Import</strong> และ <strong>Export</strong> นักศึกษาได้เท่านั้น<br>
                            <small class="text-muted">ไม่สามารถเพิ่ม แก้ไข หรือลบข้อมูลได้</small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

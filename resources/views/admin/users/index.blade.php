@extends('layouts.app')

@section('title', 'User Management | CSTU SPACE')

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-people-fill me-2"></i>
                    User Management
                </h2>
                <p class="mb-0 opacity-75">Manage users and students in the system</p>
            </div>
            <a href="{{ route('menu') }}" class="btn modern-btn btn-light">
                <i class="bi bi-arrow-left"></i>
                <span>Back to Menu</span>
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
                    <i class="bi bi-person-badge me-2"></i>Users
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="student-tab" data-bs-toggle="tab" data-bs-target="#student-panel" type="button" role="tab">
                    <i class="bi bi-mortarboard me-2"></i>Students
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
                    <h4><i class="bi bi-person-badge me-2"></i>System Users</h4>
                    <div class="action-buttons">
                        <a href="{{ route('users.exportAll') }}" class="btn modern-btn btn-info">
                            <i class="bi bi-download"></i>
                            <span>Export CSV</span>
                        </a>
                        @if($canEdit ?? true)
                        <a href="{{ route('users.importForm') }}" class="btn modern-btn btn-success-modern">
                            <i class="bi bi-file-earmark-arrow-up"></i>
                            <span>Import CSV</span>
                        </a>
                        <a href="{{ route('users.create') }}" class="btn modern-btn btn-primary-modern">
                            <i class="bi bi-plus-circle"></i>
                            <span>Add New User</span>
                        </a>
                        @endif
                    </div>
                </div>

                <div class="modern-card table-modern">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-header">
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>User Code</th>
                                    <th class="text-center">Actions</th>
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
                                    <td class="text-center">
                                        <div class="action-buttons-table">
                                            @if($canEdit ?? true)
                                            <a href="{{ route('users.edit', $user->user_id) }}" class="btn btn-sm modern-btn btn-warning-modern">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('users.destroy', $user->user_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirm delete this user?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm modern-btn btn-danger-modern">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            @else
                                            <span class="text-muted"><i class="bi bi-eye"></i> View Only</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="bi bi-person-x text-muted fs-1"></i>
                                            <p class="text-muted mt-2 mb-0">No users found</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Tab -->
        <div class="tab-pane fade" id="student-panel" role="tabpanel">
            <div class="management-section">
                <div class="section-header">
                    <h4><i class="bi bi-mortarboard me-2"></i>Students</h4>
                    <div class="action-buttons">
                        <a href="{{ route('students.exportAll') }}" class="btn modern-btn btn-info">
                            <i class="bi bi-download"></i>
                            <span>Export CSV</span>
                        </a>
                        @if($canEdit ?? true)
                        <a href="{{ route('students.importForm') }}" class="btn modern-btn btn-success-modern">
                            <i class="bi bi-file-earmark-arrow-up"></i>
                            <span>Import CSV</span>
                        </a>
                        <a href="{{ route('students.create') }}" class="btn modern-btn btn-primary-modern">
                            <i class="bi bi-plus-circle"></i>
                            <span>Add New Student</span>
                        </a>
                        @endif
                    </div>
                </div>

                <div class="modern-card table-modern">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-header">
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>รหัสวิชา</th>
                                    <th>เทอม/ปีการศึกษา</th>
                                    <th class="text-center">Actions</th>
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
                                    <td class="text-center">
                                        <div class="action-buttons-table">
                                            @if($canEdit ?? true)
                                            <a href="{{ route('students.edit', $student->student_id) }}" class="btn btn-sm modern-btn btn-warning-modern">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('students.destroy', $student->student_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirm delete this student?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm modern-btn btn-danger-modern">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            @else
                                            <span class="text-muted"><i class="bi bi-eye"></i> View Only</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="bi bi-mortarboard text-muted fs-1"></i>
                                            <p class="text-muted mt-2 mb-0">No students found</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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

@extends('layouts.app')

@section('title', 'จัดการผู้ใช้งาน - Admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>จัดการผู้ใช้งาน</h2>
        <a href="{{ route('menu') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> กลับ
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('import_errors'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>รายการที่มีปัญหา:</strong>
            <ul class="mb-0">
                @foreach(session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" id="userTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="user-tab" data-bs-toggle="tab" data-bs-target="#user-panel" type="button" role="tab">
                <i class="bi bi-person-badge"></i> ผู้ใช้ (User)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="student-tab" data-bs-toggle="tab" data-bs-target="#student-panel" type="button" role="tab">
                <i class="bi bi-mortarboard"></i> นักศึกษา (Student)
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="userTabsContent">
        <!-- User Tab -->
        <div class="tab-pane fade show active" id="user-panel" role="tabpanel">
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('users.importForm') }}" class="btn btn-success me-2">
                    <i class="bi bi-file-earmark-arrow-up"></i> Import CSV
                </a>
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> เพิ่มผู้ใช้ใหม่
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>ชื่อ-นามสกุล</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>User Code</th>
                                    <th class="text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->user_id }}</td>
                                    <td>{{ $user->username_user }}</td>
                                    <td>{{ $user->firstname_user }} {{ $user->lastname_user }}</td>
                                    <td>{{ $user->email_user }}</td>
                                    <td>
                                        @if($user->role === 'admin')
                                            <span class="badge bg-danger">Admin</span>
                                        @elseif($user->role === 'coordinator')
                                            <span class="badge bg-primary">Coordinator</span>
                                        @elseif($user->role === 'advisor')
                                            <span class="badge bg-info">Advisor</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $user->role }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->user_code ?? '-' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('users.edit', $user->user_id) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i> แก้ไข
                                        </a>
                                        <form action="{{ route('users.destroy', $user->user_id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบผู้ใช้นี้?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i> ลบ
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">ไม่มีข้อมูลผู้ใช้</td>
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
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('students.importForm') }}" class="btn btn-success me-2">
                    <i class="bi bi-file-earmark-arrow-up"></i> Import CSV
                </a>
                <a href="{{ route('students.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> เพิ่มนักศึกษาใหม่
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>ชื่อ-นามสกุล</th>
                                    <th>Email</th>
                                    <th class="text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                <tr>
                                    <td>{{ $student->student_id }}</td>
                                    <td>{{ $student->username_std }}</td>
                                    <td>{{ $student->firstname_std }} {{ $student->lastname_std }}</td>
                                    <td>{{ $student->email_std }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('students.edit', $student->student_id) }}" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i> แก้ไข
                                        </a>
                                        <form action="{{ route('students.destroy', $student->student_id) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบนักศึกษานี้?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i> ลบ
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">ไม่มีข้อมูลนักศึกษา</td>
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@endpush

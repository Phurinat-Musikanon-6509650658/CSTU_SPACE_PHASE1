@extends('layouts.app')

@section('title', 'Import Users | CSTU SPACE')

@section('content')
<div class="container">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-file-earmark-excel me-2"></i>
                    Import Users จาก Excel
                </h2>
                <p class="mb-0 opacity-75">นำเข้าข้อมูลผู้ใช้จากไฟล์ .xlsx</p>
            </div>
            <a href="{{ route('users.index') }}" class="btn modern-btn btn-light">
                <i class="bi bi-arrow-left"></i>
                <span>กลับ</span>
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-modern alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── Upload form (shown when no preview) ── --}}
    @if(empty($preview))
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="modern-card form-card mb-4">
                <div class="form-card-body">
                    <div class="alert alert-info alert-modern mb-4">
                        <h6 class="mb-2"><i class="bi bi-info-circle me-1"></i> รูปแบบไฟล์</h6>
                        <p class="mb-1 small">แถวแรก = header (ถูกข้ามอัตโนมัติ) · คอลัมน์ตามลำดับ:</p>
                        <code class="d-block small">prefix | firstname | lastname | username | email | user_code | role | password</code>
                        <hr class="my-2">
                        <p class="mb-0 small">
                            <strong>role</strong> รับค่า numeric (8192, 16384, 32768, 4096) หรือ text (advisor, coordinator, admin, staff)<br>
                            <strong>password</strong> หากว่างเปล่าจะใช้ <em>username</em> เป็นรหัสผ่านแทน
                        </p>
                    </div>

                    <form action="{{ route('users.importPreview') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-semibold">เลือกไฟล์ Excel <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror"
                                   name="file" accept=".xlsx,.xls" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">.xlsx / .xls · ขนาดไม่เกิน 10 MB</small>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('users.index') }}" class="btn modern-btn btn-light">
                                <i class="bi bi-arrow-left"></i> ยกเลิก
                            </a>
                            <button type="submit" class="btn modern-btn btn-primary-modern">
                                <i class="bi bi-eye"></i> Preview ข้อมูล
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Preview table ── --}}
    @else
    <div class="stats-bar mb-4">
        @if($isLecturersSheet ?? false)
        <div class="stat-chip stat-lecturers">
            <i class="bi bi-file-earmark-excel"></i>
            <span>Sheet: Lecturers (อาจารย์) · role = Lecturer</span>
        </div>
        @endif
        <div class="stat-chip stat-total">
            <i class="bi bi-people"></i>
            <span>ทั้งหมด {{ count($preview) }} คน</span>
        </div>
        <div class="stat-chip stat-new">
            <i class="bi bi-plus-circle"></i>
            <span>เพิ่มใหม่ {{ $newCount }}</span>
        </div>
        <div class="stat-chip stat-exists">
            <i class="bi bi-skip-forward"></i>
            <span>มีอยู่แล้ว {{ $existsCount }}</span>
        </div>
    </div>

    <div class="modern-card mb-4">
        <div class="table-responsive">
            <table class="table table-hover mb-0 preview-table">
                <thead class="table-header">
                    <tr>
                        <th>#</th>
                        <th>สถานะ</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>User Code</th>
                        <th>Role</th>
                        <th>Password</th>
                        <th>หมายเหตุ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($preview as $i => $row)
                    <tr class="{{ $row['exists'] ? 'row-exists' : 'row-new' }}">
                        <td><span class="badge bg-light text-dark">{{ $i + 1 }}</span></td>
                        <td>
                            @if($row['exists'])
                                <span class="badge-status exists"><i class="bi bi-dash-circle"></i> มีอยู่แล้ว</span>
                            @else
                                <span class="badge-status new"><i class="bi bi-plus-circle"></i> เพิ่มใหม่</span>
                            @endif
                        </td>
                        <td>
                            @if($row['prefix'])<small class="text-muted">{{ $row['prefix'] }}</small> @endif
                            <strong>{{ $row['firstname'] }} {{ $row['lastname'] }}</strong>
                        </td>
                        <td><code>{{ $row['username'] }}</code></td>
                        <td class="text-muted small">{{ $row['email'] ?: '-' }}</td>
                        <td>
                            @if($row['user_code'])
                                <code>{{ $row['user_code'] }}</code>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $r = $row['role'];
                                if ($r & 32768)      echo '<span class="role-badge role-admin"><i class="bi bi-shield-fill"></i> Admin</span>';
                                elseif ($r & 16384)  echo '<span class="role-badge role-coordinator"><i class="bi bi-person-gear"></i> Coordinator</span>';
                                elseif ($r & 8192)   echo '<span class="role-badge role-advisor"><i class="bi bi-person-check"></i> Lecturer</span>';
                                elseif ($r & 4096)   echo '<span class="role-badge role-staff"><i class="bi bi-briefcase"></i> Staff</span>';
                                else                 echo '<span class="role-badge role-other">' . $r . '</span>';
                            @endphp
                        </td>
                        <td>
                            @if(!$row['exists'])
                                <small class="text-muted font-monospace">{{ str_repeat('•', min(8, strlen($row['password']))) }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($row['warnings']))
                                @foreach($row['warnings'] as $w)
                                    <small class="text-warning d-block"><i class="bi bi-exclamation-triangle"></i> {{ $w }}</small>
                                @endforeach
                            @else
                                <span class="text-success small"><i class="bi bi-check-circle"></i></span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <a href="{{ route('users.importForm') }}" class="btn modern-btn btn-light">
            <i class="bi bi-arrow-left"></i> อัปโหลดใหม่
        </a>
        @if($newCount > 0)
        <form action="{{ route('users.importConfirm') }}" method="POST">
            @csrf
            <button type="submit" class="btn modern-btn btn-success-modern">
                <i class="bi bi-check-circle"></i>
                ยืนยัน Import {{ $newCount }} คน
            </button>
        </form>
        @else
        <span class="text-muted"><i class="bi bi-info-circle"></i> ไม่มีข้อมูลใหม่</span>
        @endif
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .form-card-body { padding: 2rem; }
    .form-card:hover { transform: none; }

    .stats-bar { display: flex; gap: 1rem; flex-wrap: wrap; }
    .stat-chip {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.5rem 1.2rem; border-radius: 50px;
        font-weight: 600; font-size: 0.9rem;
    }
    .stat-lecturers { background: #fef3c7; color: #92400e; }
    .stat-total  { background: #e3e8ff; color: #3730a3; }
    .stat-new    { background: #d1fae5; color: #065f46; }
    .stat-exists { background: #f3f4f6; color: #6b7280; }

    .badge-status {
        display: inline-flex; align-items: center; gap: 0.3rem;
        padding: 0.3rem 0.7rem; border-radius: 50px;
        font-size: 0.78rem; font-weight: 600;
    }
    .badge-status.new    { background: #d1fae5; color: #065f46; }
    .badge-status.exists { background: #f3f4f6; color: #6b7280; }

    .row-exists td { opacity: 0.55; }

    .preview-table th { font-size: 0.82rem; }
    .preview-table td { font-size: 0.85rem; vertical-align: middle; }

    .role-badge {
        padding: 0.3rem 0.7rem; border-radius: 50px;
        font-size: 0.78rem; font-weight: 500;
        display: inline-flex; align-items: center; gap: 0.3rem;
    }
    .role-admin       { background: linear-gradient(45deg,#ff6b6b,#ee5a24); color: white; }
    .role-coordinator { background: linear-gradient(45deg,#4834d4,#686de0); color: white; }
    .role-advisor     { background: linear-gradient(45deg,#0abde3,#006ba6); color: white; }
    .role-staff       { background: linear-gradient(45deg,#f39c12,#e67e22); color: white; }
    .role-other       { background: linear-gradient(45deg,#95a5a6,#34495e); color: white; }
</style>
@endpush

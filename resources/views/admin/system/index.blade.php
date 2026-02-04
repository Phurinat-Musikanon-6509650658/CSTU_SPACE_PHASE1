@extends('layouts.app')

@section('title', 'System Settings | CSTU SPACE')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="page-title">
                        <i class="bi bi-gear-fill me-2"></i>ตั้งค่าระบบ
                    </h2>
                    <p class="text-muted">จัดการการตั้งค่าและบำรุงรักษาระบบ</p>
                </div>
                <a href="{{ route('menu') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>กลับสู่หน้าหลัก
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- System Status Control -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-{{ $systemStatus === 'open' ? 'success' : 'danger' }}">
                <div class="card-header bg-{{ $systemStatus === 'open' ? 'success' : 'danger' }} text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-power me-2"></i>การควบคุมสถานะระบบ
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="mb-0">
                                    <span class="badge bg-{{ $systemStatus === 'open' ? 'success' : 'danger' }} fs-4" id="system-status-badge">
                                        <i class="bi bi-{{ $systemStatus === 'open' ? 'unlock' : 'lock' }}-fill me-2"></i>
                                        {{ strtoupper($systemStatus) }}
                                    </span>
                                </h3>
                                <small class="text-muted">สถานะปัจจุบัน</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>ปีการศึกษา:</strong> {{ $currentYear }} 
                                <span class="mx-2">|</span>
                                <strong>ภาคเรียน:</strong> {{ $currentSemester }}
                            </p>
                            <p class="text-muted mb-0 small">
                                {{ $systemStatus === 'open' ? 'ระบบเปิดให้นักศึกษาส่งข้อมูลและดำเนินการได้' : 'ระบบปิดอยู่ นักศึกษาไม่สามารถส่งหรือแก้ไขข้อมูลได้' }}
                            </p>
                        </div>
                        <div class="col-md-3 text-end">
                            <button type="button" class="btn btn-{{ $systemStatus === 'open' ? 'danger' : 'success' }} btn-lg" id="toggle-system-btn">
                                <i class="bi bi-{{ $systemStatus === 'open' ? 'lock' : 'unlock' }}-fill me-2"></i>
                                {{ $systemStatus === 'open' ? 'ปิดระบบ' : 'เปิดระบบ' }}
                            </button>
                        </div>
                    </div>

                    <!-- Year/Semester Settings Form -->
                    <hr class="my-3">
                    <form method="POST" action="{{ route('admin.system.settings.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <div class="col-md-4">
                            <label for="current_year" class="form-label">ปีการศึกษา</label>
                            <input type="number" class="form-control" id="current_year" name="current_year" 
                                   value="{{ $currentYear }}" min="2560" max="2600" required>
                        </div>
                        <div class="col-md-4">
                            <label for="current_semester" class="form-label">ภาคเรียน</label>
                            <select class="form-select" id="current_semester" name="current_semester" required>
                                <option value="1" {{ $currentSemester == '1' ? 'selected' : '' }}>1</option>
                                <option value="2" {{ $currentSemester == '2' ? 'selected' : '' }}>2</option>
                                <option value="3" {{ $currentSemester == '3' ? 'selected' : '' }}>3 (ฤดูร้อน)</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-save me-2"></i>บันทึกการตั้งค่า
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- System Information Cards -->
    <div class="row mb-4">
        <!-- System Info Card -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-server me-2"></i>ข้อมูลระบบ
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-12">
                            <small class="text-muted">PHP Version</small>
                            <div class="fw-bold">{{ $systemInfo['php_version'] }}</div>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Laravel Version</small>
                            <div class="fw-bold">{{ $systemInfo['laravel_version'] }}</div>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Environment</small>
                            <span class="badge {{ $systemInfo['environment'] === 'production' ? 'bg-success' : 'bg-warning' }}">
                                {{ ucfirst($systemInfo['environment']) }}
                            </span>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Debug Mode</small>
                            <span class="badge {{ $systemInfo['debug_mode'] ? 'bg-danger' : 'bg-success' }}">
                                {{ $systemInfo['debug_mode'] ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Database Info Card -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-database me-2"></i>ข้อมูลฐานข้อมูล
                    </h5>
                </div>
                <div class="card-body">
                    @if($databaseInfo['status'] === 'Connected')
                        <div class="row g-2">
                            <div class="col-12">
                                <small class="text-muted">Database</small>
                                <div class="fw-bold">{{ $databaseInfo['database_name'] }}</div>
                            </div>
                            <div class="col-12">
                                <small class="text-muted">Users</small>
                                <div class="fw-bold">{{ number_format($databaseInfo['user_count']) }}</div>
                            </div>
                            <div class="col-12">
                                <small class="text-muted">Students</small>
                                <div class="fw-bold">{{ number_format($databaseInfo['student_count']) }}</div>
                            </div>
                            <div class="col-12">
                                <small class="text-muted">Login Logs</small>
                                <div class="fw-bold">{{ number_format($databaseInfo['login_log_count']) }}</div>
                            </div>
                        </div>
                    @else
                        <div class="text-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            {{ $databaseInfo['error'] ?? 'Connection Error' }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Cache Info Card -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightning-charge me-2"></i>ข้อมูล Cache
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-12">
                            <small class="text-muted">Default Store</small>
                            <div class="fw-bold">{{ $cacheInfo['default_store'] }}</div>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Status</small>
                            <span class="badge {{ $cacheInfo['status'] === 'Active' ? 'bg-success' : 'bg-danger' }}">
                                {{ $cacheInfo['status'] }}
                            </span>
                        </div>
                        <div class="col-12">
                            <small class="text-muted">Available Stores</small>
                            <div class="small">
                                @foreach($cacheInfo['stores'] ?? [] as $store)
                                    <span class="badge bg-secondary me-1">{{ $store }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

@if(session('migration_output'))
    <div class="modal fade" id="migrationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Migration Output</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <pre class="bg-dark text-light p-3 rounded">{{ session('migration_output') }}</pre>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new bootstrap.Modal(document.getElementById('migrationModal')).show();
        });
    </script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // System Status Toggle
    const toggleBtn = document.getElementById('toggle-system-btn');
    const statusBadge = document.getElementById('system-status-badge');
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const currentStatus = '{{ $systemStatus }}';
            const confirmMessage = currentStatus === 'open' 
                ? 'คุณแน่ใจหรือไม่ที่จะปิดระบบ? นักศึกษาจะไม่สามารถส่งหรือแก้ไขข้อมูลได้' 
                : 'คุณแน่ใจหรือไม่ที่จะเปิดระบบ? นักศึกษาจะสามารถส่งและแก้ไขข้อมูลได้';
            
            if (confirm(confirmMessage)) {
                toggleBtn.disabled = true;
                toggleBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
                
                fetch('{{ route('admin.system.toggle-status') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('ไม่สามารถเปลี่ยนสถานะระบบได้');
                        toggleBtn.disabled = false;
                        toggleBtn.innerHTML = currentStatus === 'open' 
                            ? '<i class="bi bi-lock-fill me-2"></i>ปิดระบบ' 
                            : '<i class="bi bi-unlock-fill me-2"></i>เปิดระบบ';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('เกิดข้อผิดพลาดในการเปลี่ยนสถานะระบบ');
                    toggleBtn.disabled = false;
                    toggleBtn.innerHTML = currentStatus === 'open' 
                        ? '<i class="bi bi-lock-fill me-2"></i>ปิดระบบ' 
                        : '<i class="bi bi-unlock-fill me-2"></i>เปิดระบบ';
                });
            }
        });
    }
});
</script>
@endsection
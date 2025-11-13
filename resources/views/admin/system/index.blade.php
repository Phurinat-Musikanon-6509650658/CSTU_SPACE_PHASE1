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
                        <i class="bi bi-gear-fill me-2"></i>System Settings
                    </h2>
                    <p class="text-muted">Manage system configuration and maintenance</p>
                </div>
                <a href="{{ route('menu') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
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

    <!-- System Information Cards -->
    <div class="row mb-4">
        <!-- System Info Card -->
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-server me-2"></i>System Information
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
                        <i class="bi bi-database me-2"></i>Database Information
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
                        <i class="bi bi-lightning-charge me-2"></i>Cache Information
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

    <!-- Action Panels -->
    <div class="row">
        <!-- Cache Management -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="bi bi-arrow-clockwise me-2"></i>Cache Management
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Clear various types of application cache</p>
                    
                    <form method="POST" action="{{ route('admin.system.clear-cache') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="cache_type" value="all">
                        <button type="submit" class="btn btn-danger me-2 mb-2">
                            <i class="bi bi-trash me-1"></i>Clear All Cache
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.system.clear-cache') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="cache_type" value="application">
                        <button type="submit" class="btn btn-warning me-2 mb-2">
                            <i class="bi bi-app me-1"></i>Clear App Cache
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.system.clear-cache') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="cache_type" value="config">
                        <button type="submit" class="btn btn-info me-2 mb-2">
                            <i class="bi bi-gear me-1"></i>Clear Config Cache
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.system.optimize') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success mb-2">
                            <i class="bi bi-speedometer2 me-1"></i>Optimize Application
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Database Management -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="bi bi-database-gear me-2"></i>Database Management
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Database maintenance operations</p>
                    
                    <form method="POST" action="{{ route('admin.system.migrate') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary me-2 mb-2" 
                                onclick="return confirm('Are you sure you want to run migrations?')">
                            <i class="bi bi-arrow-up-square me-1"></i>Run Migrations
                        </button>
                    </form>

                    <a href="{{ route('admin.system.config') }}" class="btn btn-outline-secondary me-2 mb-2">
                        <i class="bi bi-gear-wide-connected me-1"></i>View Configuration
                    </a>

                    <a href="{{ route('admin.system.logs') }}" class="btn btn-outline-info mb-2">
                        <i class="bi bi-file-text me-1"></i>View System Logs
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('users.index') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-people me-1"></i>Manage Users
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('admin.logs.index') }}" class="btn btn-outline-warning w-100">
                                <i class="bi bi-shield-lock me-1"></i>Login Logs
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('statistics.index') }}" class="btn btn-outline-success w-100">
                                <i class="bi bi-graph-up me-1"></i>Statistics
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('menu') }}" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-house me-1"></i>Dashboard
                            </a>
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
@endsection
@extends('layouts.app')

@section('title', 'Menu - CSTU SPACE')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- User Info Card -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">ยินดีต้อนรับ, {{ $displayname }}</h5>
            </div>
            <div class="card-body">
                <p><strong>Role:</strong> <span class="badge bg-info">{{ ucfirst($role) }}</span></p>
            </div>
        </div>

        <!-- Menu Content based on Role -->
        <div class="row">
            {{-- Admin Menu - ทำได้ทุกอย่าง --}}
            @if($role === 'admin')
                <div class="col-md-3 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-gear-fill fs-1 text-danger"></i>
                            <h5 class="card-title mt-3">จัดการระบบ</h5>
                            <p class="card-text">จัดการผู้ใช้ และตั้งค่าระบบ</p>
                            <a href="#" class="btn btn-danger">เข้าสู่ระบบ</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-people-fill fs-1 text-primary"></i>
                            <h5 class="card-title mt-3">จัดการผู้ใช้</h5>
                            <p class="card-text">เพิ่ม/ลบ/แก้ไข ผู้ใช้</p>
                            <a href="{{ route('users.index') }}" class="btn btn-primary">จัดการ</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-file-earmark-text-fill fs-1 text-success"></i>
                            <h5 class="card-title mt-3">รายงานทั้งหมด</h5>
                            <p class="card-text">ดูรายงานทั้งระบบ</p>
                            <a href="#" class="btn btn-success">ดูรายงาน</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-shield-lock fs-1 text-warning"></i>
                            <h5 class="card-title mt-3">Login Logs</h5>
                            <p class="card-text">ติดตามการเข้าระบบ</p>
                            <a href="{{ route('admin.logs.index') }}" class="btn btn-warning">ดู Logs</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-graph-up fs-1 text-warning"></i>
                            <h5 class="card-title mt-3">สถิติ</h5>
                            <p class="card-text">ดูสถิติการใช้งาน</p>
                            <a href="#" class="btn btn-warning">ดูสถิติ</a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Coordinator Menu - ดูและติดตามสถานะโครงงานทั้งหมด --}}
            @if($role === 'coordinator' || $role === 'admin')
                <div class="col-md-4 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-clipboard-check fs-1 text-primary"></i>
                            <h5 class="card-title mt-3">ติดตามโครงงานทั้งหมด</h5>
                            <p class="card-text">ดูและติดตามสถานะโครงงานทั้งหมด</p>
                            <a href="#" class="btn btn-primary">ดูโครงงาน</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-person-badge fs-1 text-info"></i>
                            <h5 class="card-title mt-3">จัดการอาจารย์ที่ปรึกษา</h5>
                            <p class="card-text">ดูรายชื่อและโครงงานของอาจารย์</p>
                            <a href="#" class="btn btn-info">จัดการ</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-mortarboard-fill fs-1 text-success"></i>
                            <h5 class="card-title mt-3">จัดการนักศึกษา</h5>
                            <p class="card-text">ดูรายชื่อนักศึกษาทั้งหมด</p>
                            <a href="#" class="btn btn-success">จัดการ</a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Advisor Menu - ดูโครงงานของตัวเองและนักศึกษา --}}
            @if($role === 'advisor' || $role === 'coordinator' || $role === 'admin')
                <div class="col-md-6 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-folder-fill fs-1 text-primary"></i>
                            <h5 class="card-title mt-3">โครงงานของฉัน</h5>
                            <p class="card-text">ดูโครงงานที่เป็นที่ปรึกษา</p>
                            <a href="#" class="btn btn-primary">ดูโครงงาน</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-people fs-1 text-info"></i>
                            <h5 class="card-title mt-3">นักศึกษาของฉัน</h5>
                            <p class="card-text">ดูรายชื่อนักศึกษาในโครงงาน</p>
                            <a href="#" class="btn btn-info">ดูรายชื่อ</a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Student Menu - ดูกลุ่มของตัวเอง --}}
            @if($role === 'student' || $role === 'advisor' || $role === 'coordinator' || $role === 'admin')
                <div class="col-md-6 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-journal-text fs-1 text-success"></i>
                            <h5 class="card-title mt-3">โครงงานของกลุ่ม</h5>
                            <p class="card-text">ดูและจัดการโครงงานของกลุ่ม</p>
                            <a href="#" class="btn btn-success">ดูโครงงาน</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-person-lines-fill fs-1 text-warning"></i>
                            <h5 class="card-title mt-3">สมาชิกในกลุ่ม</h5>
                            <p class="card-text">ดูสมาชิกในกลุ่มของฉัน</p>
                            <a href="#" class="btn btn-warning">ดูสมาชิก</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Logout Button -->
        <div class="text-center mt-4">
            <a href="{{ route('logout') }}" class="btn btn-secondary">
                <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
            </a>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
</style>
@endpush

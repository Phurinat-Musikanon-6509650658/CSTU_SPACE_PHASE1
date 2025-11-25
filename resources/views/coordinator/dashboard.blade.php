@extends('layouts.app')

@section('title', 'Coordinator Dashboard')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Back to Menu Button -->
    <div class="mb-4">
        <a href="{{ route('menu') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>กลับไปหน้าเมนูหลัก
        </a>
    </div>

    <h1 class="mb-4">Coordinator Dashboard</h1>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">กลุ่มทั้งหมด</div>
                            <div class="h2 mb-0">{{ $stats['total_groups'] }}</div>
                        </div>
                        <i class="bi bi-people-fill" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('coordinator.groups.index') }}">ดูรายละเอียด</a>
                    <div class="small text-white"><i class="bi bi-arrow-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">รออนุมัติ</div>
                            <div class="h2 mb-0">{{ $stats['pending_groups'] }}</div>
                        </div>
                        <i class="bi bi-clock-fill" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('coordinator.groups.index', ['status' => 'pending']) }}">ดูรายละเอียด</a>
                    <div class="small text-white"><i class="bi bi-arrow-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">อนุมัติแล้ว</div>
                            <div class="h2 mb-0">{{ $stats['approved_groups'] }}</div>
                        </div>
                        <i class="bi bi-check-circle-fill" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('coordinator.groups.index', ['status' => 'approved']) }}">ดูรายละเอียด</a>
                    <div class="small text-white"><i class="bi bi-arrow-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">โครงงานทั้งหมด</div>
                            <div class="h2 mb-0">{{ $stats['total_projects'] }}</div>
                        </div>
                        <i class="bi bi-folder-fill" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('coordinator.groups.index') }}">ดูรายละเอียด</a>
                    <div class="small text-white"><i class="bi bi-arrow-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Groups Table -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-clock me-1"></i>
            กลุ่มที่รออนุมัติ
        </div>
        <div class="card-body">
            @if($pendingGroups->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>รหัสวิชา</th>
                                <th>ปี/เทอม</th>
                                <th>สมาชิก</th>
                                <th>สถานะ</th>
                                <th>สร้างเมื่อ</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingGroups as $group)
                            <tr>
                                <td>
                                    <strong>{{ sprintf('%02d-%02d', $group->semester, $group->group_id) }}</strong>
                                </td>
                                <td>{{ $group->subject_code }}</td>
                                <td>{{ $group->year }}/{{ $group->semester }}</td>
                                <td>
                                    {{ $group->members->count() }}/2 คน
                                    <br>
                                    <small class="text-muted">
                                        @foreach($group->members as $member)
                                            {{ $member->student->firstname_std ?? 'N/A' }}{{ !$loop->last ? ', ' : '' }}
                                        @endforeach
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-warning">รออนุมัติ</span>
                                </td>
                                <td>{{ $group->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('coordinator.groups.show', $group->group_id) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> ดูรายละเอียด
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>ไม่มีกลุ่มที่รออนุมัติ
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'จัดการตารางสอบ | Staff')

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-calendar-event me-2"></i>
                    จัดการตารางสอบโครงงาน
                </h2>
                <p class="mb-0 opacity-75">จัดการและดูตารางสอบโครงงานทั้งหมด</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('staff.exam-schedules.calendar') }}" class="btn btn-info">
                    <i class="bi bi-calendar3 me-2"></i>ดูปฏิทิน
                </a>
                <a href="{{ route('staff.exam-schedules.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-circle me-2"></i>เพิ่มตารางสอบ
                </a>
                <a href="{{ route('menu') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>กลับ
                </a>
            </div>
        </div>
    </div>

    <!-- Alerts -->
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

    <!-- Exam Schedules Table -->
    <div class="card">
        <div class="card-body">
            @if($examSchedules->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
                    <h5 class="text-muted mt-3">ยังไม่มีตารางสอบ</h5>
                    <p class="text-muted">เริ่มต้นโดยการเพิ่มตารางสอบใหม่</p>
                    <a href="{{ route('staff.exam-schedules.create') }}" class="btn btn-primary mt-2">
                        <i class="bi bi-plus-circle me-2"></i>เพิ่มตารางสอบ
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>รหัสโครงงาน</th>
                                <th>ชื่อโครงงาน</th>
                                <th>วันที่สอบ</th>
                                <th>เวลา</th>
                                <th>สถานที่</th>
                                <th>หมายเหตุ</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($examSchedules as $schedule)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">{{ $schedule->project->project_id }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $schedule->project->project_name_th }}</strong>
                                        @if($schedule->project->project_name_en)
                                            <br><small class="text-muted">{{ $schedule->project->project_name_en }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <i class="bi bi-calendar3 me-1"></i>
                                        {{ $schedule->ex_start_time->locale('th')->isoFormat('D MMMM YYYY') }}
                                    </td>
                                    <td>
                                        <i class="bi bi-clock me-1"></i>
                                        {{ $schedule->ex_start_time->format('H:i') }} - {{ $schedule->ex_end_time->format('H:i') }}
                                    </td>
                                    <td>
                                        <i class="bi bi-geo-alt me-1"></i>
                                        {{ $schedule->location }}
                                    </td>
                                    <td>
                                        @if($schedule->notes)
                                            <small>{{ Str::limit($schedule->notes, 50) }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('staff.exam-schedules.edit', $schedule->ex_id) }}" 
                                               class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal{{ $schedule->ex_id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal{{ $schedule->ex_id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title">ยืนยันการลบ</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>คุณต้องการลบตารางสอบนี้หรือไม่?</p>
                                                        <p><strong>โครงงาน:</strong> {{ $schedule->project->project_name_th }}</p>
                                                        <p><strong>วันที่:</strong> {{ $schedule->ex_start_time->locale('th')->isoFormat('D MMMM YYYY') }}</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                                        <form action="{{ route('staff.exam-schedules.destroy', $schedule->ex_id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">ยืนยันการลบ</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($examSchedules->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $examSchedules->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection

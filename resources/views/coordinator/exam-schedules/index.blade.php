@extends('layouts.app')

@section('title', 'จัดการตารางสอบ | CSTU SPACE')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2><i class="bi bi-calendar-event-fill"></i> จัดการตารางสอบ</h2>
                <div>
                    <a href="{{ route('coordinator.exam-schedules.create') }}" class="btn btn-primary me-2">
                        <i class="bi bi-plus-circle"></i> เพิ่มตารางสอบ
                    </a>
                    <a href="{{ route('coordinator.exam-schedules.calendar') }}" class="btn btn-success me-2">
                        <i class="bi bi-calendar3"></i> มุมมองปฏิทิน
                    </a>
                    <a href="{{ route('menu') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> กลับ
                    </a>
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

            <!-- Exam Schedules Table -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-table"></i> ตารางสอบทั้งหมด ({{ $examSchedules->total() }} รายการ)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>โครงงาน</th>
                                    <th>เวลาเริ่ม</th>
                                    <th>เวลาสิ้นสุด</th>
                                    <th>สถานที่</th>
                                    <th>ระยะเวลา</th>
                                    <th>หมายเหตุ</th>
                                    <th class="text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($examSchedules as $schedule)
                                    <tr>
                                        <td>{{ $schedule->ex_id }}</td>
                                        <td>
                                            <strong>{{ $schedule->project->project_name ?? 'N/A' }}</strong><br>
                                            <small class="text-muted">{{ $schedule->project->project_code ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <i class="bi bi-calendar3 me-1"></i>{{ $schedule->ex_start_time->format('d/m/Y') }}<br>
                                            <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ $schedule->ex_start_time->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            <i class="bi bi-calendar3 me-1"></i>{{ $schedule->ex_end_time->format('d/m/Y') }}<br>
                                            <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ $schedule->ex_end_time->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            @if($schedule->location)
                                                <i class="bi bi-geo-alt-fill me-1"></i>{{ $schedule->location }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $duration = $schedule->ex_start_time->diff($schedule->ex_end_time);
                                                $hours = $duration->h;
                                                $minutes = $duration->i;
                                            @endphp
                                            <span class="badge bg-info">
                                                @if($hours > 0)
                                                    {{ $hours }} ชม. 
                                                @endif
                                                {{ $minutes }} นาที
                                            </span>
                                        </td>
                                        <td>
                                            @if($schedule->notes)
                                                {{ Str::limit($schedule->notes, 50) }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('coordinator.exam-schedules.edit', $schedule->ex_id) }}" class="btn btn-sm btn-warning me-1">
                                                <i class="bi bi-pencil"></i> แก้ไข
                                            </a>
                                            <button class="btn btn-sm btn-danger" onclick="deleteSchedule({{ $schedule->ex_id }})">
                                                <i class="bi bi-trash"></i> ลบ
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                            <p class="text-muted mt-2">ยังไม่มีตารางสอบ</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($examSchedules->hasPages())
                    <div class="card-footer">
                        <div class="d-flex justify-content-center">
                            {{ $examSchedules->links() }}
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteSchedule(scheduleId) {
    if (!confirm('คุณต้องการลบตารางสอบนี้หรือไม่?')) {
        return;
    }

    fetch(`/coordinator/exam-schedules/${scheduleId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('เกิดข้อผิดพลาด: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาดในการลบตารางสอบ');
    });
}
</script>
@endpush

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
                                    <th>คณะกรรมการ</th>
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
                                                $committees = [
                                                    'advisor' => $schedule->project->advisor,
                                                    'committee1' => $schedule->project->committee1,
                                                    'committee2' => $schedule->project->committee2,
                                                    'committee3' => $schedule->project->committee3
                                                ];
                                            @endphp
                                            <div style="font-size: 0.85rem;">
                                                @php $count = 0; @endphp
                                                @forelse($committees as $label => $member)
                                                    @if($member)
                                                        <span class="badge bg-primary">{{ $member->user_code }}</span>
                                                        @php $count++; @endphp
                                                    @endif
                                                @empty
                                                @endforelse
                                                @if($count == 0)
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center" style="white-space: nowrap;">
                                            <a href="{{ route('coordinator.exam-schedules.edit', $schedule->ex_id) }}" class="btn btn-sm btn-warning me-1">
                                                <i class="bi bi-pencil"></i> เวลา
                                            </a>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#committeeModal{{ $schedule->project->project_id }}">
                                                <i class="bi bi-people"></i> คณะ
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteSchedule({{ $schedule->ex_id }})">
                                                <i class="bi bi-trash"></i> ลบ
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Committee Edit Modal -->
                                    <div class="modal fade" id="committeeModal{{ $schedule->project->project_id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-primary text-white">
                                                    <h5 class="modal-title">
                                                        <i class="bi bi-people-fill me-2"></i>แก้ไขคณะกรรมการ - {{ $schedule->project->project_code }}
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('coordinator.schedules.update', $schedule->project->project_id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label"><i class="bi bi-star-fill text-warning me-1"></i>อาจารย์ที่ปรึกษา</label>
                                                            <select name="advisor_code" class="form-select">
                                                                <option value="">-- เลือก --</option>
                                                                @foreach($lecturers as $lecturer)
                                                                    <option value="{{ $lecturer->user_code }}" {{ $schedule->project->advisor?->user_code === $lecturer->user_code ? 'selected' : '' }}>
                                                                        {{ $lecturer->user_code }} - {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label"><i class="bi bi-person-fill text-primary me-1"></i>คณะกรรมการที่ 1</label>
                                                            <select name="committee1_code" class="form-select">
                                                                <option value="">-- เลือก --</option>
                                                                @foreach($lecturers as $lecturer)
                                                                    <option value="{{ $lecturer->user_code }}" {{ $schedule->project->committee1?->user_code === $lecturer->user_code ? 'selected' : '' }}>
                                                                        {{ $lecturer->user_code }} - {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label"><i class="bi bi-person-fill text-info me-1"></i>คณะกรรมการที่ 2</label>
                                                            <select name="committee2_code" class="form-select">
                                                                <option value="">-- เลือก --</option>
                                                                @foreach($lecturers as $lecturer)
                                                                    <option value="{{ $lecturer->user_code }}" {{ $schedule->project->committee2?->user_code === $lecturer->user_code ? 'selected' : '' }}>
                                                                        {{ $lecturer->user_code }} - {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label"><i class="bi bi-person-fill text-success me-1"></i>คณะกรรมการที่ 3</label>
                                                            <select name="committee3_code" class="form-select">
                                                                <option value="">-- เลือก --</option>
                                                                @foreach($lecturers as $lecturer)
                                                                    <option value="{{ $lecturer->user_code }}" {{ $schedule->project->committee3?->user_code === $lecturer->user_code ? 'selected' : '' }}>
                                                                        {{ $lecturer->user_code }} - {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="bi bi-check-circle me-1"></i>บันทึก
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
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

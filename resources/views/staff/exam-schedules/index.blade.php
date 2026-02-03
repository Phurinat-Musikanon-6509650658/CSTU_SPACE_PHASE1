@extends('layouts.app')

@section('title', 'ตารางสอบโครงงาน | CSTU SPACE')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2><i class="bi bi-calendar-event-fill"></i> ตารางสอบโครงงาน</h2>
                <div>
                    <a href="{{ route('staff.exam-schedules.calendar') }}" class="btn btn-success me-2">
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
                                    <th class="text-center">สถานะโครงงาน</th>
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
                                            @if($schedule->project)
                                                @if($schedule->project->status == 'active')
                                                    <span class="badge bg-success">ดำเนินการ</span>
                                                @elseif($schedule->project->status == 'completed')
                                                    <span class="badge bg-primary">เสร็จสิ้น</span>
                                                @elseif($schedule->project->status == 'cancelled')
                                                    <span class="badge bg-danger">ยกเลิก</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $schedule->project->status }}</span>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">-</span>
                                            @endif
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

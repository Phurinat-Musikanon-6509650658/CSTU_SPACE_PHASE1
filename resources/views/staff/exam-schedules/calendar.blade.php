@extends('layouts.app')

@section('title', 'ตารางสอบโครงงาน - มุมมองปฏิทิน | CSTU SPACE')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">ตารางสอบโครงงาน - มุมมองปฏิทิน</h2>
                <div>
                    <a href="{{ route('staff.exam-schedules') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-list-ul"></i> มุมมองรายการ
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <form method="GET" action="{{ route('staff.exam-schedules.calendar') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">สถานะโครงงาน</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">ทั้งหมด</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>ดำเนินการ</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>เสร็จสิ้น</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="location" class="form-label">สถานที่</label>
                            <input type="text" name="location" id="location" class="form-control" 
                                   value="{{ request('location') }}" placeholder="ค้นหาสถานที่...">
                        </div>
                        <div class="col-md-4">
                            <label for="search" class="form-label">ค้นหา</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   value="{{ request('search') }}" placeholder="ค้นหาชื่อโครงงาน หรือหมายเหตุ...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> ค้นหา
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Calendar View -->
            @if($schedulesByDate->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
                    <h4 class="text-muted mt-3">ไม่พบตารางสอบ</h4>
                    <p class="text-muted">ยังไม่มีตารางสอบที่ตรงกับเงื่อนไขการค้นหา</p>
                </div>
            @else
                <div class="timeline">
                    @foreach($schedulesByDate as $date => $schedules)
                        <div class="mb-4">
                            <!-- Date Header -->
                            <div class="date-header bg-primary text-white p-3 rounded mb-3">
                                <h4 class="mb-0">
                                    <i class="bi bi-calendar-date me-2"></i>
                                    {{ \Carbon\Carbon::parse($date)->locale('th')->translatedFormat('l, j F Y') }}
                                </h4>
                            </div>

                            <!-- Schedules for this date -->
                            <div class="ms-4">
                                @foreach($schedules as $schedule)
                                    <div class="card mb-3 border-start border-primary border-4 shadow-sm">
                                        <div class="card-body">
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1">{{ $schedule->project->project_name }}</h5>
                                                <div class="text-muted small mb-2">
                                                    <i class="bi bi-clock"></i> 
                                                    {{ $schedule->ex_start_time->format('H:i') }} - 
                                                    {{ $schedule->ex_end_time->format('H:i') }} น.
                                                    @if($schedule->location)
                                                        | <i class="bi bi-geo-alt"></i> {{ $schedule->location }}
                                                    @endif
                                                </div>
                                                @if($schedule->notes)
                                                    <p class="mb-1 small">{{ $schedule->notes }}</p>
                                                @endif
                                                <div class="mt-2">
                                                    @if($schedule->project->status == 'active')
                                                        <span class="badge bg-success">ดำเนินการ</span>
                                                    @elseif($schedule->project->status == 'completed')
                                                        <span class="badge bg-primary">เสร็จสิ้น</span>
                                                    @elseif($schedule->project->status == 'cancelled')
                                                        <span class="badge bg-danger">ยกเลิก</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ $schedule->project->status }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
}

.date-header {
    animation: slideInLeft 0.5s ease-out;
}

.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>
@endsection

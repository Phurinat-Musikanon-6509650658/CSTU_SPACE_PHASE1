@extends('layouts.app')

@section('title', 'ปฏิทินตารางสอบ | CSTU SPACE')

@push('styles')
<style>
    .exam-item {
        transition: all 0.3s ease;
    }
    .exam-item:hover {
        transform: translateY(-5px);
    }
    .exam-date-group {
        animation: fadeIn 0.5s;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .form-check-input:checked + .form-check-label {
        font-weight: 600;
        color: #0d6efd;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="page-title">
                        <i class="bi bi-calendar3 me-2"></i>ปฏิทินตารางสอบ
                    </h2>
                    <p class="text-muted">แสดงตารางสอบทั้งหมดในรูปแบบปฏิทิน</p>
                </div>
                <div>
                    <a href="{{ route('coordinator.exam-schedules.create') }}" class="btn btn-primary me-2">
                        <i class="bi bi-plus-circle me-2"></i>เพิ่มตารางสอบ
                    </a>
                    <a href="{{ route('coordinator.exam-schedules.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-list-ul me-2"></i>มุมมองรายการ
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">ตัวกรอง</label>
                    <select class="form-select" id="filter-status">
                        <option value="all">ทั้งหมด</option>
                        <option value="upcoming">กำลังจะถึง</option>
                        <option value="today">วันนี้</option>
                        <option value="past">ผ่านไปแล้ว</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">สถานที่</label>
                    <select class="form-select" id="filter-location">
                        <option value="all">ทั้งหมด</option>
                        @foreach($locations as $location)
                            @if($location)
                                <option value="{{ $location }}">{{ $location }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">ค้นหา</label>
                    <input type="text" class="form-control" id="search-exam" placeholder="ค้นหาโครงงาน...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-outline-danger w-100" id="clear-filters">
                        <i class="bi bi-x-circle me-1"></i>ล้างตัวกรอง
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">ตารางสอบทั้งหมด</h6>
                    <h2 class="mb-0">{{ $totalExams }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">กำลังจะถึง</h6>
                    <h2 class="mb-0">{{ $upcomingExams }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">วันนี้</h6>
                    <h2 class="mb-0">{{ $todayExams }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h6 class="card-title">สอบแล้ว</h6>
                    <h2 class="mb-0">{{ $pastExams }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline View -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bi bi-calendar-week me-2"></i>ตารางสอบจัดเรียงตามวันที่
            </h5>
        </div>
        <div class="card-body">
            @if($examsByDate->count() > 0)
                @foreach($examsByDate as $date => $exams)
                    <div class="exam-date-group mb-4" data-date="{{ $date }}">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="bi bi-calendar-event me-2"></i>
                            {{ \Carbon\Carbon::parse($date)->locale('th')->translatedFormat('วันl ที่ j F Y') }}
                            <span class="badge bg-primary ms-2">{{ $exams->count() }} รายการ</span>
                        </h5>
                        
                        <div class="row g-3">
                            @foreach($exams as $schedule)
                                @php
                                    $isToday = \Carbon\Carbon::parse($schedule->ex_start_time)->isToday();
                                    $isPast = \Carbon\Carbon::parse($schedule->ex_start_time)->isPast();
                                    $isUpcoming = \Carbon\Carbon::parse($schedule->ex_start_time)->isFuture();
                                    
                                    $cardClass = 'border-start border-4 ';
                                    if ($isToday) $cardClass .= 'border-warning';
                                    elseif ($isPast) $cardClass .= 'border-secondary';
                                    else $cardClass .= 'border-success';
                                @endphp
                                
                                <div class="col-md-6 exam-item" 
                                     data-status="{{ $isToday ? 'today' : ($isPast ? 'past' : 'upcoming') }}"
                                     data-location="{{ $schedule->location ?? '' }}"
                                     data-search="{{ strtolower($schedule->project->project_code . ' ' . $schedule->project->project_name) }}">
                                    <div class="card {{ $cardClass }} h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title mb-0">
                                                    <strong>{{ $schedule->project->project_code }}</strong>
                                                </h6>
                                                @if($isToday)
                                                    <span class="badge bg-warning">วันนี้</span>
                                                @elseif($isPast)
                                                    <span class="badge bg-secondary">สอบแล้ว</span>
                                                @else
                                                    <span class="badge bg-success">กำลังจะถึง</span>
                                                @endif
                                            </div>
                                            
                                            <p class="text-muted small mb-2">{{ $schedule->project->project_name }}</p>
                                            
                                            <div class="mb-2">
                                                <i class="bi bi-clock text-primary me-2"></i>
                                                <strong>{{ $schedule->ex_start_time->format('H:i') }}</strong>
                                                - 
                                                <strong>{{ $schedule->ex_end_time->format('H:i') }}</strong>
                                                @php
                                                    $duration = $schedule->ex_start_time->diff($schedule->ex_end_time);
                                                    $hours = $duration->h + ($duration->days * 24);
                                                    $minutes = $duration->i;
                                                @endphp
                                                <span class="text-muted">({{ $hours }}h {{ $minutes }}m)</span>
                                            </div>
                                            
                                            @if($schedule->location)
                                                <div class="mb-2">
                                                    <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                                                    {{ $schedule->location }}
                                                </div>
                                            @endif
                                            
                                            @if($schedule->notes)
                                                <div class="mb-2">
                                                    <i class="bi bi-info-circle text-info me-2"></i>
                                                    <small>{{ Str::limit($schedule->notes, 80) }}</small>
                                                </div>
                                            @endif
                                            
                                            <div class="mt-3 d-flex gap-2">
                                                <a href="{{ route('coordinator.exam-schedules.edit', $schedule->ex_id) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i> แก้ไข
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger delete-schedule" 
                                                        data-id="{{ $schedule->ex_id }}"
                                                        data-project="{{ $schedule->project->project_name }}">
                                                    <i class="bi bi-trash"></i> ลบ
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x display-1 text-muted"></i>
                    <h5 class="mt-3 text-muted">ไม่มีตารางสอบ</h5>
                    <p class="text-muted">คลิก "เพิ่มตารางสอบ" เพื่อสร้างตารางสอบใหม่</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterStatus = document.getElementById('filter-status');
    const filterLocation = document.getElementById('filter-location');
    const searchInput = document.getElementById('search-exam');
    const clearBtn = document.getElementById('clear-filters');
    const examItems = document.querySelectorAll('.exam-item');
    const examDateGroups = document.querySelectorAll('.exam-date-group');
    
    // Filter function
    function applyFilters() {
        const statusFilter = filterStatus.value;
        const locationFilter = filterLocation.value;
        const searchTerm = searchInput.value.toLowerCase();
        
        examItems.forEach(item => {
            const status = item.getAttribute('data-status');
            const location = item.getAttribute('data-location');
            const searchText = item.getAttribute('data-search');
            
            let showItem = true;
            
            // Status filter
            if (statusFilter !== 'all' && status !== statusFilter) {
                showItem = false;
            }
            
            // Location filter
            if (locationFilter !== 'all' && location !== locationFilter) {
                showItem = false;
            }
            
            // Search filter
            if (searchTerm && !searchText.includes(searchTerm)) {
                showItem = false;
            }
            
            item.style.display = showItem ? '' : 'none';
        });
        
        // Hide date groups with no visible items
        examDateGroups.forEach(group => {
            const visibleItems = group.querySelectorAll('.exam-item:not([style*="display: none"])');
            group.style.display = visibleItems.length > 0 ? '' : 'none';
        });
    }
    
    // Event listeners
    filterStatus.addEventListener('change', applyFilters);
    filterLocation.addEventListener('change', applyFilters);
    searchInput.addEventListener('input', applyFilters);
    
    // Clear filters
    clearBtn.addEventListener('click', function() {
        filterStatus.value = 'all';
        filterLocation.value = 'all';
        searchInput.value = '';
        applyFilters();
    });
    
    // Delete exam schedule
    document.querySelectorAll('.delete-schedule').forEach(button => {
        button.addEventListener('click', function() {
            const scheduleId = this.dataset.id;
            const projectName = this.dataset.project;
            
            if (confirm(`คุณแน่ใจหรือไม่ที่จะลบตารางสอบของ "${projectName}"?`)) {
                fetch(`{{ url('coordinator/exam-schedules') }}/${scheduleId}`, {
                    method: 'DELETE',
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
                        alert('ไม่สามารถลบตารางสอบได้');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('เกิดข้อผิดพลาดในการลบตารางสอบ');
                });
            }
        });
    });
});
</script>
@endsection

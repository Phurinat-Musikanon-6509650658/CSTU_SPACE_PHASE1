@extends('layouts.app')

@section('title', 'Create Exam Schedule | CSTU SPACE')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="page-title">
                        <i class="bi bi-plus-circle-fill me-2"></i>สร้างตารางสอบ
                    </h2>
                    <p class="text-muted">กำหนดตารางสอบโครงงานใหม่</p>
                </div>
                <a href="{{ route('coordinator.exam-schedules.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>กลับสู่ตารางสอบ
                </a>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-calendar-event me-2"></i>ข้อมูลตารางสอบ
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('coordinator.exam-schedules.store') }}">
                        @csrf

                        <!-- Project Selection -->
                        <div class="mb-3">
                            <label class="form-label">
                                เลือกโครงงาน <span class="text-danger">*</span>
                            </label>
                            
                            <!-- Search and Select All -->
                            <div class="row mb-2">
                                <div class="col-md-8">
                                    <input type="text" 
                                           class="form-control" 
                                           id="project-search" 
                                           placeholder="ค้นหาโครงงาน...">
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-outline-primary w-100" id="select-all-btn">
                                        <i class="bi bi-check-all me-1"></i>เลือกทั้งหมด
                                    </button>
                                </div>
                            </div>

                            <!-- Project List with Checkboxes -->
                            <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                                @foreach($projects as $project)
                                    <div class="form-check project-item" data-project-name="{{ strtolower($project->project_code . ' ' . $project->project_name) }}">
                                        <input class="form-check-input project-checkbox" 
                                               type="checkbox" 
                                               name="project_ids[]" 
                                               value="{{ $project->project_id }}" 
                                               id="project_{{ $project->project_id }}"
                                               {{ (is_array(old('project_ids')) && in_array($project->project_id, old('project_ids'))) ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="project_{{ $project->project_id }}">
                                            <strong>{{ $project->project_code }}</strong> - {{ $project->project_name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            
                            @error('project_ids')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            
                            <div class="mt-2">
                                <span class="badge bg-primary" id="selected-count">เลือกแล้ว 0 โครงงาน</span>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Start Date & Time -->
                            <div class="col-md-6 mb-3">
                                <label for="ex_start_time" class="form-label">
                                    วันและเวลาเริ่ม <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" 
                                       class="form-control @error('ex_start_time') is-invalid @enderror" 
                                       id="ex_start_time" 
                                       name="ex_start_time" 
                                       value="{{ old('ex_start_time') }}" 
                                       required>
                                @error('ex_start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- End Date & Time -->
                            <div class="col-md-6 mb-3">
                                <label for="ex_end_time" class="form-label">
                                    วันและเวลาสิ้นสุด <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" 
                                       class="form-control @error('ex_end_time') is-invalid @enderror" 
                                       id="ex_end_time" 
                                       name="ex_end_time" 
                                       value="{{ old('ex_end_time') }}" 
                                       required>
                                @error('ex_end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="mb-3">
                            <label for="location" class="form-label">สถานที่</label>
                            <input type="text" 
                                   class="form-control @error('location') is-invalid @enderror" 
                                   id="location" 
                                   name="location" 
                                   value="{{ old('location') }}" 
                                   placeholder="เช่น ห้อง 301 อาคาร 3">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">สถานที่สอบ (ไม่บังคับ)</div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">หมายเหตุ</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="4" 
                                      placeholder="หมายเหตุหรือคำแนะนำเพิ่มเติม...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">ข้อมูลเพิ่มเติมเกี่ยวกับการสอบ (ไม่บังคับ)</div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('coordinator.exam-schedules.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>ยกเลิก
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>สร้างตารางสอบ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startTimeInput = document.getElementById('ex_start_time');
    const endTimeInput = document.getElementById('ex_end_time');
    const projectCheckboxes = document.querySelectorAll('.project-checkbox');
    const selectedCount = document.getElementById('selected-count');
    const searchInput = document.getElementById('project-search');
    const selectAllBtn = document.getElementById('select-all-btn');
    const projectItems = document.querySelectorAll('.project-item');
    
    // Auto-set end time to 2 hours after start time when start time changes
    startTimeInput.addEventListener('change', function() {
        if (!endTimeInput.value && startTimeInput.value) {
            const startDate = new Date(startTimeInput.value);
            startDate.setHours(startDate.getHours() + 2);
            
            const year = startDate.getFullYear();
            const month = String(startDate.getMonth() + 1).padStart(2, '0');
            const day = String(startDate.getDate()).padStart(2, '0');
            const hours = String(startDate.getHours()).padStart(2, '0');
            const minutes = String(startDate.getMinutes()).padStart(2, '0');
            
            endTimeInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
        }
    });
    
    // Update selected project count
    function updateSelectedCount() {
        const count = document.querySelectorAll('.project-checkbox:checked').length;
        selectedCount.textContent = `เลือกแล้ว ${count} โครงงาน`;
        selectedCount.className = count > 0 ? 'badge bg-success' : 'badge bg-primary';
    }
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        projectItems.forEach(item => {
            const projectName = item.getAttribute('data-project-name');
            if (projectName.includes(searchTerm)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });
    
    // Select All / Deselect All
    selectAllBtn.addEventListener('click', function() {
        const visibleCheckboxes = Array.from(projectCheckboxes).filter(cb => {
            return cb.closest('.project-item').style.display !== 'none';
        });
        
        const allChecked = visibleCheckboxes.every(cb => cb.checked);
        
        visibleCheckboxes.forEach(cb => {
            cb.checked = !allChecked;
        });
        
        this.innerHTML = allChecked 
            ? '<i class="bi bi-check-all me-1"></i>เลือกทั้งหมด' 
            : '<i class="bi bi-x-circle me-1"></i>ยกเลิกทั้งหมด';
        
        updateSelectedCount();
    });
    
    // Update count on checkbox change
    projectCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
    
    updateSelectedCount(); // Initial count
});
</script>
@endsection

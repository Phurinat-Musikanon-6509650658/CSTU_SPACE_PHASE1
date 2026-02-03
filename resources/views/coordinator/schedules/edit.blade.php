@extends('layouts.app')

@section('title', 'แก้ไขตารางสอบและคณะกรรมการ')

@push('styles')
<style>
    body {
        background-color: #f8f9fa;
    }
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
    }
    .card-header {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: white;
        font-weight: 600;
        border-radius: 12px 12px 0 0 !important;
    }
    .form-label {
        font-weight: 600;
        color: #495057;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="mb-4">
        <a href="{{ route('coordinator.schedules.index') }}" class="btn btn-outline-primary mb-3">
            <i class="bi bi-arrow-left me-2"></i>กลับรายการ
        </a>
        <h1 class="h2 fw-bold">
            <i class="bi bi-calendar-check me-2 text-primary"></i>แก้ไขตารางสอบและคณะกรรมการ
        </h1>
    </div>

    <!-- Project Info Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0 text-white">ข้อมูลโครงงาน</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2"><strong>รหัสโครงงาน:</strong> <code class="text-primary">{{ $project->project_code }}</code></p>
                    <p class="mb-2"><strong>ชื่อโครงงาน:</strong> {{ $project->project_name ?? 'ยังไม่ระบุ' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><strong>กลุ่ม:</strong> {{ sprintf('%02d', $project->group_id) }}</p>
                    <p class="mb-2"><strong>สมาชิก:</strong> 
                        @foreach($project->group->members as $member)
                            {{ $member->student->firstname_std ?? '' }} {{ $member->student->lastname_std ?? '' }}@if(!$loop->last), @endif
                        @endforeach
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 text-white">กำหนดวันเวลาสอบและคณะกรรมการ</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('coordinator.schedules.update', $project->project_id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Exam DateTime -->
                    <div class="col-md-6 mb-4">
                        <label class="form-label">
                            <i class="bi bi-calendar-event text-danger me-1"></i>วันเวลาสอบ
                        </label>
                        <input type="datetime-local" 
                               name="exam_datetime" 
                               class="form-control" 
                               value="{{ $project->exam_datetime ? $project->exam_datetime->format('Y-m-d\TH:i') : '' }}">
                        <small class="text-muted">กำหนดวันและเวลาที่จะสอบโครงงาน</small>
                    </div>
                </div>

                <hr class="my-4">

                <h6 class="fw-bold mb-3">
                    <i class="bi bi-people me-2 text-success"></i>คณะกรรมการสอบ
                </h6>

                <div class="row">
                    <!-- Advisor -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            <i class="bi bi-person-badge text-primary me-1"></i>อาจารย์ที่ปรึกษา (Advisor)
                        </label>
                        <select name="advisor_code" class="form-select">
                            <option value="">-- ไม่มี --</option>
                            @foreach($lecturers as $lecturer)
                                <option value="{{ $lecturer->user_code }}" 
                                        {{ $project->advisor_code == $lecturer->user_code ? 'selected' : '' }}>
                                    {{ $lecturer->user_code }} - {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Committee 1 -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            <i class="bi bi-person-check text-success me-1"></i>กรรมการคนที่ 1
                        </label>
                        <select name="committee1_code" class="form-select">
                            <option value="">-- ไม่มี --</option>
                            @foreach($lecturers as $lecturer)
                                <option value="{{ $lecturer->user_code }}" 
                                        {{ $project->committee1_code == $lecturer->user_code ? 'selected' : '' }}>
                                    {{ $lecturer->user_code }} - {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Committee 2 -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            <i class="bi bi-person-check text-success me-1"></i>กรรมการคนที่ 2
                        </label>
                        <select name="committee2_code" class="form-select">
                            <option value="">-- ไม่มี --</option>
                            @foreach($lecturers as $lecturer)
                                <option value="{{ $lecturer->user_code }}" 
                                        {{ $project->committee2_code == $lecturer->user_code ? 'selected' : '' }}>
                                    {{ $lecturer->user_code }} - {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Committee 3 -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            <i class="bi bi-person-check text-success me-1"></i>กรรมการคนที่ 3
                        </label>
                        <select name="committee3_code" class="form-select">
                            <option value="">-- ไม่มี --</option>
                            @foreach($lecturers as $lecturer)
                                <option value="{{ $lecturer->user_code }}" 
                                        {{ $project->committee3_code == $lecturer->user_code ? 'selected' : '' }}>
                                    {{ $lecturer->user_code }} - {{ $lecturer->firstname_user }} {{ $lecturer->lastname_user }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save me-2"></i>บันทึกการเปลี่ยนแปลง
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const advisorSelect = document.querySelector('select[name="advisor_code"]');
    const committee1Select = document.querySelector('select[name="committee1_code"]');
    const committee2Select = document.querySelector('select[name="committee2_code"]');
    const committee3Select = document.querySelector('select[name="committee3_code"]');
    const form = advisorSelect.closest('form');
    
    // Validate on submit
    form.addEventListener('submit', function(e) {
        const advisor = advisorSelect.value;
        const comm1 = committee1Select.value;
        const comm2 = committee2Select.value;
        const comm3 = committee3Select.value;
        
        const lecturers = [advisor, comm1, comm2, comm3].filter(v => v !== '');
        const uniqueLecturers = [...new Set(lecturers)];
        
        if (lecturers.length !== uniqueLecturers.length) {
            e.preventDefault();
            alert('❌ พบการเลือกอาจารย์ซ้ำกัน!\n\nกรุณาเลือกอาจารย์ที่แตกต่างกันสำหรับแต่ละตำแหน่ง');
            return false;
        }
    });
});
</script>
@endpush
@endsection

@extends('layouts.app')

@section('title', 'แก้ไขรายวิชา | CSTU SPACE')

@push('styles')
<style>
    body {
        background-color: #f8f9fa;
    }

    .form-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }

    .form-card-header {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 12px 12px 0 0;
    }

    .form-card-body {
        padding: 2rem;
    }

    .timing-section {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        border-left: 4px solid #4e73df;
    }

    .timing-section-title {
        font-weight: 700;
        color: #212529;
        margin-bottom: 1rem;
        font-size: 1.1rem;
    }

    .timing-section-description {
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 1rem;
        font-style: italic;
    }

    .date-time-wrapper {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .btn-group-custom {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    .btn-group-custom button,
    .btn-group-custom a {
        flex: 1;
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        border-radius: 6px;
    }

    .required::after {
        content: " *";
        color: red;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="mb-4">
        <a href="{{ route('admin.subjects.index') }}" class="btn btn-link text-decoration-none">
            <i class="bi bi-chevron-left me-2"></i>กลับไป
        </a>
        <h1 class="h2 fw-bold mt-2">
            <i class="bi bi-pencil-square me-2 text-primary"></i>แก้ไขรายวิชา {{ $subject->subject_name }}
        </h1>
    </div>

    <!-- Error Messages -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>เกิดข้อผิดพลาด:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Form Card -->
    <form action="{{ route('admin.subjects.update', $subject) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card form-card">
            <div class="form-card-header">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>ข้อมูลทั่วไป
                </h5>
            </div>
            <div class="form-card-body">
                <!-- Subject Code & Name -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="subject_code" class="form-label required">รหัสวิชา</label>
                        <input type="text" class="form-control @error('subject_code') is-invalid @enderror" 
                               id="subject_code" name="subject_code" 
                               value="{{ old('subject_code', $subject->subject_code) }}" required>
                        @error('subject_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="subject_name" class="form-label required">ชื่อวิชา</label>
                        <input type="text" class="form-control @error('subject_name') is-invalid @enderror" 
                               id="subject_name" name="subject_name" 
                               value="{{ old('subject_name', $subject->subject_name) }}" required>
                        @error('subject_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label for="description" class="form-label">รายละเอียด</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="3">{{ old('description', $subject->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Semester & Year -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="year" class="form-label required">ปีการศึกษา</label>
                        <input type="number" class="form-control @error('year') is-invalid @enderror" 
                               id="year" name="year" 
                               value="{{ old('year', $subject->year) }}" required>
                        @error('year')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="semester" class="form-label required">ภาคเรียน</label>
                        <select class="form-select @error('semester') is-invalid @enderror" 
                                id="semester" name="semester" required>
                            <option value="1" {{ old('semester', $subject->semester) == 1 ? 'selected' : '' }}>ภาคเรียน 1</option>
                            <option value="2" {{ old('semester', $subject->semester) == 2 ? 'selected' : '' }}>ภาคเรียน 2</option>
                        </select>
                        @error('semester')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Status -->
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_enabled" name="is_enabled" 
                               value="1" {{ old('is_enabled', $subject->is_enabled) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_enabled">
                            เปิดใช้งานรายวิชานี้
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- General Period Dates -->
        <div class="card form-card">
            <div class="form-card-header">
                <h5 class="mb-0">
                    <i class="bi bi-calendar me-2"></i>ช่วงเวลาทั่วไป
                </h5>
            </div>
            <div class="form-card-body">
                <div class="timing-section">
                    <div class="timing-section-title">วันเริ่มและสิ้นสุดของรายวิชา</div>
                    <div class="timing-section-description">
                        ระบุช่วงเวลาโดยทั่วไปของรายวิชา (เพื่อการอ้างอิง)
                    </div>
                    <div class="date-time-wrapper">
                        <div>
                            <label for="open_date" class="form-label">วันเปิด</label>
                            <input type="datetime-local" class="form-control @error('open_date') is-invalid @enderror" 
                                   id="open_date" name="open_date" 
                                   value="{{ old('open_date', $subject->open_date?->format('Y-m-d\TH:i')) }}">
                            @error('open_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label for="close_date" class="form-label">วันปิด</label>
                            <input type="datetime-local" class="form-control @error('close_date') is-invalid @enderror" 
                                   id="close_date" name="close_date" 
                                   value="{{ old('close_date', $subject->close_date?->format('Y-m-d\TH:i')) }}">
                            @error('close_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Access Period (For Students/Users) -->
        <div class="card form-card">
            <div class="form-card-header">
                <h5 class="mb-0">
                    <i class="bi bi-person-lock me-2"></i>ช่วงเวลาเข้าใช้งาน (สำหรับ นักศึกษา/ผู้ใช้)
                </h5>
            </div>
            <div class="form-card-body">
                <div class="timing-section">
                    <div class="timing-section-title">
                        <i class="bi bi-exclamation-circle me-2 text-warning"></i>
                        ควบคุมการเข้าใช้งาน
                    </div>
                    <div class="timing-section-description">
                        เมื่อหลังจากวันปิด นักศึกษาและผู้ใช้ทั่วไปจะไม่สามารถเข้าใช้งานได้ (Lock) นอกจาก Admin/Staff
                    </div>
                    <div class="date-time-wrapper">
                        <div>
                            <label for="access_open_date" class="form-label">วันเปิดการเข้าใช้</label>
                            <input type="datetime-local" class="form-control @error('access_open_date') is-invalid @enderror" 
                                   id="access_open_date" name="access_open_date" 
                                   value="{{ old('access_open_date', $subject->access_open_date?->format('Y-m-d\TH:i')) }}">
                            <small class="form-text text-muted">ระบุวันเวลาที่ยอมให้นักศึกษาเข้าใช้งาน</small>
                            @error('access_open_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label for="access_close_date" class="form-label">วันปิดการเข้าใช้</label>
                            <input type="datetime-local" class="form-control @error('access_close_date') is-invalid @enderror" 
                                   id="access_close_date" name="access_close_date" 
                                   value="{{ old('access_close_date', $subject->access_close_date?->format('Y-m-d\TH:i')) }}">
                            <small class="form-text text-muted">หลังวันนี้ นักศึกษาจะถูก Lock ไม่สามารถเข้าได้</small>
                            @error('access_close_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="alert alert-info" role="alert">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>หมายเหตุ:</strong> ช่วงเวลานี้ใช้สำหรับการควบคุมการเข้าถึงทั่วไปของรายวิชา ถ้าปล่อยว่างจะอนุญาตให้เข้าได้ตลอด
                </div>
            </div>
        </div>

        <!-- Evaluation Period -->
        <div class="card form-card">
            <div class="form-card-header">
                <h5 class="mb-0">
                    <i class="bi bi-star me-2"></i>ช่วงเวลาประเมินคะแนน (สำหรับ อาจารย์)
                </h5>
            </div>
            <div class="form-card-body">
                <div class="timing-section">
                    <div class="timing-section-title">
                        <i class="bi bi-calendar-check me-2 text-success"></i>
                        ควบคุมการประเมินคะแนน
                    </div>
                    <div class="timing-section-description">
                        ระบุช่วงเวลาที่เปิดให้อาจารย์ประเมินคะแนน หลังจากวันปิดจะไม่สามารถส่งคะแนนได้
                    </div>
                    <div class="date-time-wrapper">
                        <div>
                            <label for="evaluation_open_date" class="form-label">วันเปิดการประเมิน</label>
                            <input type="datetime-local" class="form-control @error('evaluation_open_date') is-invalid @enderror" 
                                   id="evaluation_open_date" name="evaluation_open_date" 
                                   value="{{ old('evaluation_open_date', $subject->evaluation_open_date?->format('Y-m-d\TH:i')) }}">
                            <small class="form-text text-muted">วันเวลาที่เปิดให้อาจารย์เริ่มประเมินคะแนน</small>
                            @error('evaluation_open_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label for="evaluation_close_date" class="form-label">วันปิดการประเมิน</label>
                            <input type="datetime-local" class="form-control @error('evaluation_close_date') is-invalid @enderror" 
                                   id="evaluation_close_date" name="evaluation_close_date" 
                                   value="{{ old('evaluation_close_date', $subject->evaluation_close_date?->format('Y-m-d\TH:i')) }}">
                            <small class="form-text text-muted">หลังวันนี้ อาจารย์จะไม่สามารถส่งคะแนนได้</small>
                            @error('evaluation_close_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grade Edit Period -->
        <div class="card form-card">
            <div class="form-card-header">
                <h5 class="mb-0">
                    <i class="bi bi-pencil me-2"></i>ช่วงเวลาแก้ไขคะแนน (สำหรับ อาจารย์)
                </h5>
            </div>
            <div class="form-card-body">
                <div class="timing-section">
                    <div class="timing-section-title">
                        <i class="bi bi-calendar-event me-2 text-info"></i>
                        ควบคุมการแก้ไขคะแนน
                    </div>
                    <div class="timing-section-description">
                        ระบุช่วงเวลาที่เปิดให้อาจารย์แก้ไขคะแนนที่ส่งไปแล้ว หลังจากวันปิดจะไม่สามารถแก้ไขได้
                    </div>
                    <div class="date-time-wrapper">
                        <div>
                            <label for="grade_edit_open_date" class="form-label">วันเปิดแก้ไขคะแนน</label>
                            <input type="datetime-local" class="form-control @error('grade_edit_open_date') is-invalid @enderror" 
                                   id="grade_edit_open_date" name="grade_edit_open_date" 
                                   value="{{ old('grade_edit_open_date', $subject->grade_edit_open_date?->format('Y-m-d\TH:i')) }}">
                            <small class="form-text text-muted">วันเวลาที่เปิดให้อาจารย์เริ่มแก้ไขคะแนน</small>
                            @error('grade_edit_open_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label for="grade_edit_close_date" class="form-label">วันปิดแก้ไขคะแนน</label>
                            <input type="datetime-local" class="form-control @error('grade_edit_close_date') is-invalid @enderror" 
                                   id="grade_edit_close_date" name="grade_edit_close_date" 
                                   value="{{ old('grade_edit_close_date', $subject->grade_edit_close_date?->format('Y-m-d\TH:i')) }}">
                            <small class="form-text text-muted">หลังวันนี้ อาจารย์จะไม่สามารถแก้ไขคะแนนได้</small>
                            @error('grade_edit_close_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="btn-group-custom">
            <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary">
                <i class="bi bi-x-circle me-2"></i>ยกเลิก
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-2"></i>บันทึกการเปลี่ยนแปลง
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Validate that close_date is after open_date
    document.getElementById('close_date')?.addEventListener('change', function() {
        const openDate = new Date(document.getElementById('open_date').value);
        const closeDate = new Date(this.value);
        if (closeDate <= openDate) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Same for access dates
    document.getElementById('access_close_date')?.addEventListener('change', function() {
        const openDate = new Date(document.getElementById('access_open_date').value);
        const closeDate = new Date(this.value);
        if (closeDate <= openDate && document.getElementById('access_open_date').value) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Same for evaluation dates
    document.getElementById('evaluation_close_date')?.addEventListener('change', function() {
        const openDate = new Date(document.getElementById('evaluation_open_date').value);
        const closeDate = new Date(this.value);
        if (closeDate <= openDate && document.getElementById('evaluation_open_date').value) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Same for grade edit dates
    document.getElementById('grade_edit_close_date')?.addEventListener('change', function() {
        const openDate = new Date(document.getElementById('grade_edit_open_date').value);
        const closeDate = new Date(this.value);
        if (closeDate <= openDate && document.getElementById('grade_edit_open_date').value) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });
</script>
@endpush
@endsection

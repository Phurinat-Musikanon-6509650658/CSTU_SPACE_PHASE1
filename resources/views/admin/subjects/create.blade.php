@extends('layouts.app')

@section('title', 'เพิ่มวิชาใหม่ | CSTU SPACE')

@push('styles')
<style>
    body { background-color: #f8f9fa; }
    .form-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,.1);
        margin-bottom: 2rem;
    }
    .form-card-header {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: white;
        padding: 1.25rem 1.5rem;
        border-radius: 12px 12px 0 0;
    }
    .form-card-body { padding: 2rem; }
    .timing-section {
        background: #f8f9fa;
        padding: 1.25rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        border-left: 4px solid #4e73df;
    }
    .timing-section.access  { border-color: #0d6efd; }
    .timing-section.eval    { border-color: #198754; }
    .timing-section.grade   { border-color: #0dcaf0; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <a href="{{ route('admin.subjects.index') }}" class="btn btn-link text-decoration-none">
            <i class="bi bi-chevron-left me-2"></i>กลับไป
        </a>
        <h1 class="h2 fw-bold mt-2">
            <i class="bi bi-plus-circle me-2 text-primary"></i>เพิ่มวิชาใหม่
        </h1>
    </div>

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

    <form action="{{ route('admin.subjects.store') }}" method="POST">
        @csrf

        {{-- ข้อมูลทั่วไป --}}
        <div class="card form-card">
            <div class="form-card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>ข้อมูลวิชา</h5>
            </div>
            <div class="form-card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="subject_code" class="form-label">รหัสวิชา <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('subject_code') is-invalid @enderror"
                               id="subject_code" name="subject_code" placeholder="เช่น CS303"
                               value="{{ old('subject_code') }}" required>
                        @error('subject_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="subject_name" class="form-label">ชื่อวิชา <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('subject_name') is-invalid @enderror"
                               id="subject_name" name="subject_name" placeholder="ชื่อวิชา"
                               value="{{ old('subject_name') }}" required>
                        @error('subject_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">รายละเอียด</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="semester" class="form-label">ภาคเรียน <span class="text-danger">*</span></label>
                        <select class="form-select @error('semester') is-invalid @enderror" id="semester" name="semester" required>
                            <option value="">-- เลือก --</option>
                            <option value="1" {{ old('semester') == '1' ? 'selected' : '' }}>ภาคเรียน 1</option>
                            <option value="2" {{ old('semester') == '2' ? 'selected' : '' }}>ภาคเรียน 2</option>
                            <option value="3" {{ old('semester') == '3' ? 'selected' : '' }}>ภาคเรียน 3 (ฤดูร้อน)</option>
                        </select>
                        @error('semester')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="year" class="form-label">ปีการศึกษา <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('year') is-invalid @enderror"
                               id="year" name="year" placeholder="เช่น 2568"
                               value="{{ old('year', $currentYear) }}" min="2560" max="2600" required>
                        @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3 d-flex align-items-end">
                        <div class="form-check form-switch pb-1">
                            <input class="form-check-input" type="checkbox" id="is_enabled"
                                   name="is_enabled" value="1" checked>
                            <label class="form-check-label" for="is_enabled">เปิดใช้งาน</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ช่วงเวลาเข้าใช้งาน --}}
        <div class="card form-card">
            <div class="form-card-header" style="background: linear-gradient(135deg,#0d6efd 0%,#0a58ca 100%);">
                <h5 class="mb-0"><i class="bi bi-person-lock me-2"></i>ช่วงเวลาเข้าใช้งานระบบ</h5>
            </div>
            <div class="form-card-body">
                <div class="timing-section access">
                    <p class="text-muted small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        นักศึกษาและผู้ใช้ทั่วไปจะถูก Lock ไม่ให้เข้าระบบนอกช่วงเวลานี้ (Admin/Staff เข้าได้เสมอ) หากไม่ระบุ จะเปิดตลอดเวลา
                    </p>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="access_open_date" class="form-label">วันเปิดการเข้าใช้</label>
                            <input type="datetime-local" class="form-control @error('access_open_date') is-invalid @enderror"
                                   id="access_open_date" name="access_open_date" value="{{ old('access_open_date') }}">
                            @error('access_open_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="access_close_date" class="form-label">วันปิดการเข้าใช้</label>
                            <input type="datetime-local" class="form-control @error('access_close_date') is-invalid @enderror"
                                   id="access_close_date" name="access_close_date" value="{{ old('access_close_date') }}">
                            @error('access_close_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ช่วงเวลาประเมินคะแนน --}}
        <div class="card form-card">
            <div class="form-card-header" style="background: linear-gradient(135deg,#198754 0%,#146c43 100%);">
                <h5 class="mb-0"><i class="bi bi-star me-2"></i>ช่วงเวลาประเมินคะแนน (อาจารย์)</h5>
            </div>
            <div class="form-card-body">
                <div class="timing-section eval">
                    <p class="text-muted small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        อาจารย์จะส่งคะแนนได้เฉพาะในช่วงนี้เท่านั้น หากไม่ระบุ จะส่งได้ตลอดเวลา
                    </p>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="evaluation_open_date" class="form-label">วันเปิดการประเมิน</label>
                            <input type="datetime-local" class="form-control @error('evaluation_open_date') is-invalid @enderror"
                                   id="evaluation_open_date" name="evaluation_open_date" value="{{ old('evaluation_open_date') }}">
                            @error('evaluation_open_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="evaluation_close_date" class="form-label">วันปิดการประเมิน</label>
                            <input type="datetime-local" class="form-control @error('evaluation_close_date') is-invalid @enderror"
                                   id="evaluation_close_date" name="evaluation_close_date" value="{{ old('evaluation_close_date') }}">
                            @error('evaluation_close_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ช่วงเวลาแก้ไขคะแนน --}}
        <div class="card form-card">
            <div class="form-card-header" style="background: linear-gradient(135deg,#0dcaf0 0%,#0aa2c0 100%);">
                <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>ช่วงเวลาแก้ไขคะแนน (อาจารย์)</h5>
            </div>
            <div class="form-card-body">
                <div class="timing-section grade">
                    <p class="text-muted small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        อาจารย์จะแก้ไขคะแนนที่ส่งไปแล้วได้ในช่วงนี้ หลังจากนี้จะล็อคไม่ให้แก้ไข หากไม่ระบุ จะแก้ได้ตลอดเวลา
                    </p>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="grade_edit_open_date" class="form-label">วันเปิดแก้ไขคะแนน</label>
                            <input type="datetime-local" class="form-control @error('grade_edit_open_date') is-invalid @enderror"
                                   id="grade_edit_open_date" name="grade_edit_open_date" value="{{ old('grade_edit_open_date') }}">
                            @error('grade_edit_open_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="grade_edit_close_date" class="form-label">วันปิดแก้ไขคะแนน</label>
                            <input type="datetime-local" class="form-control @error('grade_edit_close_date') is-invalid @enderror"
                                   id="grade_edit_close_date" name="grade_edit_close_date" value="{{ old('grade_edit_close_date') }}">
                            @error('grade_edit_close_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-2 mb-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-save me-2"></i>บันทึก
            </button>
            <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary btn-lg">
                <i class="bi bi-x-circle me-2"></i>ยกเลิก
            </a>
        </div>
    </form>
</div>
@endsection

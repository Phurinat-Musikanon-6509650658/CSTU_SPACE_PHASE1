@extends('layouts.app')

@section('title', 'เพิ่มวิชาใหม่ | CSTU SPACE')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="mb-4">
                <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-primary mb-3">
                    <i class="bi bi-arrow-left me-2"></i>กลับรายการ
                </a>
                <h1 class="h2 fw-bold">
                    <i class="bi bi-plus-circle me-2 text-primary"></i>เพิ่มวิชาใหม่
                </h1>
            </div>

            <!-- Form Card -->
            <div class="card border-0 shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">ข้อมูลวิชา</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.subjects.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="subject_code" class="form-label">รหัสวิชา <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('subject_code') is-invalid @enderror"
                                           id="subject_code" name="subject_code" placeholder="เช่น 01417311"
                                           value="{{ old('subject_code') }}" required>
                                    @error('subject_code')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="subject_name" class="form-label">ชื่อวิชา (ไทย) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('subject_name') is-invalid @enderror"
                                           id="subject_name" name="subject_name" placeholder="ชื่อวิชา"
                                           value="{{ old('subject_name') }}" required>
                                    @error('subject_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="subject_name_en" class="form-label">ชื่อวิชา (อังกฤษ)</label>
                            <input type="text" class="form-control @error('subject_name_en') is-invalid @enderror"
                                   id="subject_name_en" name="subject_name_en" placeholder="English Subject Name"
                                   value="{{ old('subject_name_en') }}">
                            @error('subject_name_en')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">รายละเอียด</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3"
                                      placeholder="รายละเอียดวิชา">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="semester" class="form-label">ภาคเรียน <span class="text-danger">*</span></label>
                                    <select class="form-select @error('semester') is-invalid @enderror"
                                            id="semester" name="semester" required>
                                        <option value="">-- เลือก --</option>
                                        <option value="1" {{ old('semester') == '1' ? 'selected' : '' }}>ภาคเรียน 1</option>
                                        <option value="2" {{ old('semester') == '2' ? 'selected' : '' }}>ภาคเรียน 2</option>
                                        <option value="3" {{ old('semester') == '3' ? 'selected' : '' }}>ภาคเรียน 3 (ฤดูร้อน)</option>
                                    </select>
                                    @error('semester')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="year" class="form-label">ปีการศึกษา <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('year') is-invalid @enderror"
                                           id="year" name="year" placeholder="เช่น 2568"
                                           value="{{ old('year', 2568) }}" min="2560" max="2600" required>
                                    @error('year')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="is_enabled" class="form-label">&nbsp;</label>
                                    <div class="form-check form-switch pt-2">
                                        <input class="form-check-input" type="checkbox" id="is_enabled"
                                               name="is_enabled" value="1" {{ old('is_enabled') ? 'checked' : '' }} checked>
                                        <label class="form-check-label" for="is_enabled">
                                            เปิดใช้งาน
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3">ช่วงเวลาเปิด-ปิด</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="open_date" class="form-label">วันเริ่ม <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('open_date') is-invalid @enderror"
                                           id="open_date" name="open_date"
                                           value="{{ old('open_date') }}" required>
                                    @error('open_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted d-block mt-2">
                                        <i class="bi bi-info-circle me-1"></i>เช่น 1 มกราคม 2569 08:00
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="close_date" class="form-label">วันสิ้นสุด <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('close_date') is-invalid @enderror"
                                           id="close_date" name="close_date"
                                           value="{{ old('close_date') }}" required>
                                    @error('close_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted d-block mt-2">
                                        <i class="bi bi-info-circle me-1"></i>เช่น 31 มีนาคม 2569 17:00
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save me-2"></i>บันทึก
                            </button>
                            <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-x-circle me-2"></i>ยกเลิก
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

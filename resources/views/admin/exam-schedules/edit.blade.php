@extends('layouts.app')

@section('title', 'Edit Exam Schedule | CSTU SPACE')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="page-title">
                        <i class="bi bi-pencil-square me-2"></i>แก้ไขตารางสอบ
                    </h2>
                    <p class="text-muted">แก้ไขรายละเอียดตารางสอบ</p>
                </div>
                <a href="{{ route('admin.exam-schedules.index') }}" class="btn btn-outline-secondary">
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
                    <form method="POST" action="{{ route('admin.exam-schedules.update', $examSchedule->ex_id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Project Selection -->
                        <div class="mb-3">
                            <label for="project_id" class="form-label">
                                โครงงาน <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('project_id') is-invalid @enderror" 
                                    id="project_id" name="project_id" required>
                                <option value="">เลือกโครงงาน...</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->project_id }}" 
                                            {{ (old('project_id', $examSchedule->project_id) == $project->project_id) ? 'selected' : '' }}>
                                        {{ $project->project_code }} - {{ $project->project_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('project_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">เลือกโครงงานสำหรับตารางสอบนี้</div>
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
                                       value="{{ old('ex_start_time', $examSchedule->ex_start_time->format('Y-m-d\TH:i')) }}" 
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
                                       value="{{ old('ex_end_time', $examSchedule->ex_end_time->format('Y-m-d\TH:i')) }}" 
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
                                   value="{{ old('location', $examSchedule->location) }}" 
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
                                      placeholder="หมายเหตุหรือคำแนะนำเพิ่มเติม...">{{ old('notes', $examSchedule->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">ข้อมูลเพิ่มเติมเกี่ยวกับการสอบ (ไม่บังคับ)</div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.exam-schedules.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>ยกเลิก
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>อัปเดตตารางสอบ
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Schedule Information Card -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i>ข้อมูลตารางสอบปัจจุบัน
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>สร้างเมื่อ:</strong> {{ $examSchedule->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>แก้ไขล่าสุด:</strong> {{ $examSchedule->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

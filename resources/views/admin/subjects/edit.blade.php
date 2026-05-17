@extends('layouts.app')

@section('title', 'แก้ไขรายวิชา | CSTU SPACE')

@section('content')
<div class="container-fluid px-4 py-4" style="max-width:860px;">

    <div class="mb-4">
        <a href="{{ route('admin.subjects.index') }}" class="btn btn-link text-decoration-none ps-0 text-muted">
            <i class="bi bi-chevron-left me-1"></i>กลับรายการ
        </a>
        <h1 class="h3 fw-bold mt-1">
            <i class="bi bi-pencil-square me-2 text-primary"></i>แก้ไขรายวิชา
            <span class="text-primary">{{ $subject->subject_code }}</span>
        </h1>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm">
            <i class="bi bi-exclamation-triangle me-2"></i><strong>เกิดข้อผิดพลาด:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.subjects.update', $subject) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- ── ข้อมูลทั่วไป ── --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header fw-semibold bg-white border-bottom">
                <i class="bi bi-info-circle me-2 text-primary"></i>ข้อมูลทั่วไป
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">รหัสวิชา <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('subject_code') is-invalid @enderror"
                               name="subject_code" value="{{ old('subject_code', $subject->subject_code) }}" required>
                        @error('subject_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">ชื่อวิชา <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('subject_name') is-invalid @enderror"
                               name="subject_name" value="{{ old('subject_name', $subject->subject_name) }}" required>
                        @error('subject_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">ปีการศึกษา <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('year') is-invalid @enderror"
                               name="year" value="{{ old('year', $subject->year) }}" required>
                        @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">เทอม <span class="text-danger">*</span></label>
                        <select class="form-select @error('semester') is-invalid @enderror" name="semester" required>
                            <option value="1" {{ old('semester', $subject->semester) == 1 ? 'selected' : '' }}>เทอม 1</option>
                            <option value="2" {{ old('semester', $subject->semester) == 2 ? 'selected' : '' }}>เทอม 2</option>
                        </select>
                        @error('semester')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_enabled" name="is_enabled"
                                   value="1" {{ old('is_enabled', $subject->is_enabled) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="is_enabled">เปิดใช้งานรายวิชา</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── ช่วงเวลา ── --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header fw-semibold bg-white border-bottom">
                <i class="bi bi-calendar-range me-2 text-primary"></i>ช่วงเวลา
            </div>
            <div class="card-body">

                @php
                    $sections = [
                        [
                            'icon'  => 'bi-calendar2-week',
                            'color' => 'primary',
                            'title' => 'เปิด-ปิด รายวิชา',
                            'desc'  => 'ช่วงเวลาของรายวิชาโดยรวม',
                            'open'  => ['open_date',            'วันเปิดรายวิชา'],
                            'close' => ['close_date',           'วันปิดรายวิชา'],
                        ],
                        [
                            'icon'  => 'bi-star',
                            'color' => 'success',
                            'title' => 'ช่วงประเมินคะแนน',
                            'desc'  => 'อาจารย์สามารถให้คะแนนได้ในช่วงนี้',
                            'open'  => ['evaluation_open_date',  'วันเปิดการประเมิน'],
                            'close' => ['evaluation_close_date', 'วันปิดการประเมิน'],
                        ],
                        [
                            'icon'  => 'bi-pencil',
                            'color' => 'info',
                            'title' => 'ช่วงแก้ไขคะแนน',
                            'desc'  => 'อาจารย์สามารถแก้ไขคะแนนที่ส่งแล้วได้ในช่วงนี้',
                            'open'  => ['grade_edit_open_date',  'วันเปิดแก้ไขคะแนน'],
                            'close' => ['grade_edit_close_date', 'วันปิดแก้ไขคะแนน'],
                        ],
                    ];
                @endphp

                @foreach($sections as $s)
                <div class="p-3 rounded-3 mb-3" style="background:#f8f9fa;border-left:4px solid var(--bs-{{ $s['color'] }});">
                    <div class="fw-semibold mb-1">
                        <i class="bi {{ $s['icon'] }} me-1 text-{{ $s['color'] }}"></i>{{ $s['title'] }}
                    </div>
                    <div class="text-muted small mb-2">{{ $s['desc'] }}</div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">{{ $s['open'][1] }}</label>
                            <input type="datetime-local"
                                   class="form-control form-control-sm @error($s['open'][0]) is-invalid @enderror"
                                   name="{{ $s['open'][0] }}"
                                   value="{{ old($s['open'][0], $subject->{$s['open'][0]}?->format('Y-m-d\TH:i')) }}">
                            @error($s['open'][0])<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">{{ $s['close'][1] }}</label>
                            <input type="datetime-local"
                                   class="form-control form-control-sm @error($s['close'][0]) is-invalid @enderror"
                                   name="{{ $s['close'][0] }}"
                                   value="{{ old($s['close'][0], $subject->{$s['close'][0]}?->format('Y-m-d\TH:i')) }}">
                            @error($s['close'][0])<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                @endforeach

            </div>
        </div>

        {{-- ── Buttons ── --}}
        <div class="d-flex gap-2">
            <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary px-4">
                <i class="bi bi-x-circle me-2"></i>ยกเลิก
            </a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-circle me-2"></i>บันทึก
            </button>
        </div>

    </form>
</div>
@endsection

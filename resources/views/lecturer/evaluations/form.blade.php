@extends('layouts.app')

@section('title', 'ให้คะแนนโครงงาน')

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
        font-weight: 600;
        border-radius: 12px 12px 0 0 !important;
    }
    .score-input {
        font-size: 1.2rem;
        font-weight: 600;
        text-align: center;
    }
    .total-score {
        font-size: 3rem;
        font-weight: 700;
        color: #4e73df;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="mb-4">
        <a href="{{ route('lecturer.evaluations.index') }}" class="btn btn-outline-primary mb-3">
            <i class="bi bi-arrow-left me-2"></i>กลับรายการ
        </a>
        <h1 class="h2 fw-bold">
            <i class="bi bi-clipboard-check me-2 text-primary"></i>ให้คะแนนโครงงาน
        </h1>
    </div>

    <!-- Project Info -->
    <div class="card mb-4">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
            <h5 class="mb-0 text-white">ข้อมูลโครงงาน</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <p class="mb-2"><strong>รหัสโครงงาน:</strong> <code class="text-primary fs-5">{{ $project->project_code }}</code></p>
                    <p class="mb-2"><strong>ชื่อโครงงาน:</strong> {{ $project->project_name ?? 'ยังไม่ระบุ' }}</p>
                    <p class="mb-2"><strong>สมาชิก:</strong> 
                        @foreach($project->group->members as $member)
                            {{ $member->student->firstname_std ?? '' }} {{ $member->student->lastname_std ?? '' }}@if(!$loop->last), @endif
                        @endforeach
                    </p>
                    <p class="mb-0"><strong>วันเวลาสอบ:</strong> 
                        @if($project->exam_datetime)
                            <span class="text-danger">{{ $project->exam_datetime->format('d/m/Y H:i น.') }}</span>
                        @else
                            <span class="text-muted">ยังไม่กำหนด</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3 bg-light rounded">
                        <small class="text-muted d-block mb-2">ตำแหน่งของคุณ</small>
                        @php
                            $roleLabels = [
                                'advisor' => 'อาจารย์ที่ปรึกษา',
                                'committee1' => 'กรรมการคนที่ 1',
                                'committee2' => 'กรรมการคนที่ 2',
                                'committee3' => 'กรรมการคนที่ 3'
                            ];
                            $roleColors = [
                                'advisor' => 'primary',
                                'committee1' => 'success',
                                'committee2' => 'success',
                                'committee3' => 'success'
                            ];
                        @endphp
                        <h5 class="mb-0">
                            <span class="badge bg-{{ $roleColors[$role] ?? 'secondary' }}">
                                {{ $roleLabels[$role] ?? $role }}
                            </span>
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Evaluation Form -->
    <div class="card">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
            <h5 class="mb-0 text-white">
                <i class="bi bi-pencil-square me-2"></i>{{ $evaluation ? 'แก้ไขคะแนน' : 'ให้คะแนน' }}
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('lecturer.evaluations.submit', $project->project_id) }}" method="POST" id="evaluationForm">
                @csrf

                <!-- Student Selection -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            <i class="bi bi-person-check text-primary me-2"></i>เลือกนักศึกษา
                        </label>
                        <select name="student_id" id="student_id" class="form-select form-select-lg" required>
                            @foreach($project->group->members as $member)
                                <option value="{{ $member->student->student_id }}" 
                                    @if($selectedStudent && $selectedStudent->student_id == $member->student->student_id) selected @endif>
                                    {{ $member->student->firstname_std }} {{ $member->student->lastname_std }}
                                    ({{ $member->student->student_code }})
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- ส่วนที่ 1: Advisor Only -->
                @if($role === 'advisor')
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <div class="alert alert-info" role="alert">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>ส่วนที่ 1 | คะแนนความก้าวหน้าของโครงงาน</strong> - เฉพาะอาจารย์ที่ปรึกษา
                        </div>
                        <label class="form-label fw-bold">
                            <i class="bi bi-book text-primary me-2"></i>คะแนนส่วนที่ 1 (เต็ม 10 คะแนน)
                        </label>
                        <input type="number" 
                               name="part1_score" 
                               id="part1_score"
                               class="form-control score-input" 
                               min="0" 
                               max="10" 
                               step="0.01"
                               value="{{ $evaluation ? $evaluation->part1_score : 0 }}"
                               required>
                        <div class="form-text">
                            ให้คะแนนตามความเข้าใจโครงงาน (0-10 คะแนน)
                        </div>
                        @error('part1_score')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                @endif

                <!-- ส่วนที่ 2-3: Advisor + Committee -->
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <div class="alert alert-success" role="alert">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>ส่วนที่ 2 และ 3 | คุณภาพของงานนำเสนอ + การนำเสนอโครงงาน</strong> - ทั้ง Advisor และ Committee
                        </div>
                    </div>

                    <!-- Part 2 Score -->
                    <div class="col-md-6 mb-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-file-earmark-text text-info me-2"></i>ส่วนที่ 2: คุณภาพของงาน (เต็ม 30 คะแนน)
                        </label>
                        <input type="number" 
                               name="part2_score" 
                               id="part2_score"
                               class="form-control score-input" 
                               min="0" 
                               max="30" 
                               step="0.01"
                               value="{{ $evaluation ? $evaluation->part2_score : 0 }}"
                               required>
                        <div class="form-text">
                            ให้คะแนนตามคุณภาพของรูปเล่มรายงาน (0-30 คะแนน)
                        </div>
                        @error('part2_score')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Part 3 Score -->
                    <div class="col-md-6 mb-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-megaphone text-warning me-2"></i>ส่วนที่ 3: การนำเสนอโครงงาน (เต็ม 60 คะแนน)
                        </label>
                        <input type="number" 
                               name="part3_score" 
                               id="part3_score"
                               class="form-control score-input" 
                               min="0" 
                               max="60" 
                               step="0.01"
                               value="{{ $evaluation ? $evaluation->part3_score : 0 }}"
                               required>
                        <div class="form-text">
                            ให้คะแนนตามการนำเสนอและการตอบคำถาม (0-60 คะแนน)
                        </div>
                        @error('part3_score')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Total Score Display -->
                <div class="text-center mb-4 p-4 bg-light rounded">
                    <small class="text-muted d-block mb-2">คะแนนรวมที่ให้</small>
                    <div class="total-score" id="totalScore">0.00</div>
                    <small class="text-muted">
                        @if($role === 'advisor')
                            / 100 คะแนน (ส่วนที่ 1+2+3)
                        @else
                            / 90 คะแนน (ส่วนที่ 2+3)
                        @endif
                    </small>
                </div>

                <!-- Comments -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        <i class="bi bi-chat-left-text text-secondary me-2"></i>ความเห็นเพิ่มเติม (ถ้ามี)
                    </label>
                    <textarea name="comments" 
                              class="form-control" 
                              rows="4" 
                              placeholder="แสดงความคิดเห็นหรือข้อเสนอแนะเพิ่มเติม (ไม่บังคับ)">{{ $evaluation ? $evaluation->comments : '' }}</textarea>
                    @error('comments')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-save me-2"></i>{{ $evaluation ? 'บันทึกการแก้ไข' : 'ส่งคะแนน' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const part1Input = document.getElementById('part1_score');
    const part2Input = document.getElementById('part2_score');
    const part3Input = document.getElementById('part3_score');
    const totalScoreDisplay = document.getElementById('totalScore');
    const isAdvisor = {{ $role === 'advisor' ? 'true' : 'false' }};

    function updateTotalScore() {
        const part1 = (part1Input && parseFloat(part1Input.value)) || 0;
        const part2 = (part2Input && parseFloat(part2Input.value)) || 0;
        const part3 = (part3Input && parseFloat(part3Input.value)) || 0;
        
        const total = part1 + part2 + part3;
        totalScoreDisplay.textContent = total.toFixed(2);
        
        // Change color based on score
        if (total >= 90) {
            totalScoreDisplay.style.color = '#28a745'; // Green (A)
        } else if (total >= 85) {
            totalScoreDisplay.style.color = '#17a2b8'; // Blue (B+)
        } else if (total >= 80) {
            totalScoreDisplay.style.color = '#17a2b8'; // Blue (B)
        } else if (total >= 70) {
            totalScoreDisplay.style.color = '#ffc107'; // Yellow (C+)
        } else if (total >= 50) {
            totalScoreDisplay.style.color = '#fd7e14'; // Orange (C/D+/D)
        } else {
            totalScoreDisplay.style.color = '#dc3545'; // Red (F)
        }
    }

    // Add event listeners
    if (part1Input) part1Input.addEventListener('input', updateTotalScore);
    if (part2Input) part2Input.addEventListener('input', updateTotalScore);
    if (part3Input) part3Input.addEventListener('input', updateTotalScore);

    // Validate on input
    if (part1Input) {
        part1Input.addEventListener('input', function() {
            const value = parseFloat(this.value);
            if (value < 0) this.value = 0;
            if (value > 10) this.value = 10;
        });
    }

    if (part2Input) {
        part2Input.addEventListener('input', function() {
            const value = parseFloat(this.value);
            if (value < 0) this.value = 0;
            if (value > 30) this.value = 30;
        });
    }

    if (part3Input) {
        part3Input.addEventListener('input', function() {
            const value = parseFloat(this.value);
            if (value < 0) this.value = 0;
            if (value > 60) this.value = 60;
        });
    }

    // Initial calculation
    updateTotalScore();

    // Add event listener for student selection change
    const studentSelect = document.getElementById('student_id');
    if (studentSelect) {
        studentSelect.addEventListener('change', function() {
            if (this.value) {
                // Reload form with selected student_id as query parameter
                window.location.href = `{{ route('lecturer.evaluations.form', $project->project_id) }}?student_id=${this.value}`;
            }
        });
    }
});
</script>
@endpush
@endsection

@extends('layouts.app')

@section('title', 'ให้คะแนนโครงงาน')

@push('styles')
<style>
    body { background-color: #f8f9fa; }
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
        width: 100%;
        text-align: center;
        font-weight: 600;
        font-size: 1rem;
        border: 2px solid #e3e6f0;
        border-radius: 6px;
        padding: 8px 4px;
    }
    .score-input:focus {
        border-color: #4e73df;
        outline: none;
        box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.25);
    }
    .eval-table thead th {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: white;
        text-align: center;
        vertical-align: middle;
        border-color: #3a5fc8;
    }
    .eval-table thead th.criteria-th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        text-align: left;
    }
    .eval-table td { vertical-align: middle; }
    .eval-table .max-col { text-align: center; color: #888; font-size: 0.9rem; }
    .part3-section-header td {
        background-color: #e8f4fd;
        font-weight: 700;
        color: #1a3a8a;
        border-top: 2px solid #4e73df;
    }
    .sub-criteria td:first-child {
        padding-left: 2.5rem;
        color: #555;
        font-size: 0.95rem;
    }
    .part3-subtotal-row td {
        background-color: #dbeafe;
        font-weight: 600;
        border-top: 1px solid #93c5fd;
        border-bottom: 2px solid #4e73df;
    }
    .grand-total-row td {
        background: linear-gradient(135deg, #e7f3ff 0%, #d0e8ff 100%);
        font-weight: 700;
        border-top: 2px solid #4e73df;
    }
    .total-score-display {
        font-size: 1.4rem;
        font-weight: 700;
        color: #4e73df;
    }
    .part3-subtotal-display {
        font-size: 1.1rem;
        font-weight: 700;
        color: #224abe;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    @php
        $roleLabels = [
            'advisor'    => 'อาจารย์ที่ปรึกษา',
            'committee1' => 'กรรมการคนที่ 1',
            'committee2' => 'กรรมการคนที่ 2',
            'committee3' => 'กรรมการคนที่ 3',
        ];
        $roleColors = [
            'advisor'    => 'primary',
            'committee1' => 'success',
            'committee2' => 'success',
            'committee3' => 'success',
        ];
        $maxScore = $role === 'advisor' ? 100 : 90;
        $students = $project->group->members->pluck('student')->take(2);

        // Pre-compute saved scores per student
        $scores = [];
        foreach ($students as $idx => $student) {
            $eval = $project->evaluations
                ->where('student_id', $student->student_id)
                ->where('evaluator_code', Auth::user()->user_code)
                ->where('evaluator_role', $role)
                ->first();
            $scores[$idx] = [
                'part1'  => $eval ? ($eval->part1_score  ?? 0) : 0,
                'part2'  => $eval ? ($eval->part2_score  ?? 0) : 0,
                'part3a' => $eval ? ($eval->part3a_score ?? 0) : 0,
                'part3b' => $eval ? ($eval->part3b_score ?? 0) : 0,
                'part3c' => $eval ? ($eval->part3c_score ?? 0) : 0,
            ];
        }
    @endphp

    <!-- Header -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <a href="{{ route('lecturer.evaluations.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>กลับรายการ
            </a>
            <a href="{{ route('lecturer.evaluations.export', $project->project_id) }}" target="_blank"
               class="btn btn-outline-secondary">
                <i class="bi bi-printer me-2"></i>Export ใบประเมิน (ลายเซ็น)
            </a>
        </div>
        <h1 class="h2 fw-bold">
            <i class="bi bi-clipboard-check me-2 text-primary"></i>ให้คะแนนโครงงาน
        </h1>
        <small class="text-muted">
            <span class="badge bg-{{ $roleColors[$role] ?? 'secondary' }}">{{ $roleLabels[$role] ?? $role }}</span>
            — คะแนนเต็ม: {{ $maxScore }} คะแนน
        </small>
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

    @if($subjectLockMessage ?? null)
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-lock-fill fs-5"></i>
            <div><strong>ไม่สามารถบันทึกคะแนนได้:</strong> {{ $subjectLockMessage }}</div>
        </div>
    @endif

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

                <div class="table-responsive">
                    <table class="table table-bordered eval-table">
                        <thead>
                            <tr>
                                <th class="criteria-th" style="width: 42%">เกณฑ์การประเมิน</th>
                                <th style="width: 8%">คะแนนเต็ม</th>
                                @foreach($students as $idx => $student)
                                    <th>
                                        {{ $student->firstname_std }} {{ $student->lastname_std }}<br>
                                        <small class="fw-normal opacity-75">{{ $student->student_code }}</small>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>

                            {{-- ส่วนที่ 1: advisor เท่านั้น --}}
                            @if($role === 'advisor')
                            <tr>
                                <td>
                                    <strong>ส่วนที่ 1:</strong> ความก้าวหน้าโครงงาน
                                    <small class="text-muted">(ให้โดยอาจารย์ที่ปรึกษา)</small>
                                </td>
                                <td class="max-col">10</td>
                                @foreach($students as $idx => $student)
                                <td>
                                    <input type="number"
                                           name="student_{{ $idx }}_part1_score"
                                           class="score-input part1-score-{{ $idx }}"
                                           min="0" max="10" step="0.01"
                                           placeholder="0.00"
                                           value="{{ $scores[$idx]['part1'] }}">
                                </td>
                                @endforeach
                            </tr>
                            @endif

                            {{-- ส่วนที่ 2 --}}
                            <tr>
                                <td><strong>ส่วนที่ 2:</strong> คุณภาพของรายงาน</td>
                                <td class="max-col">30</td>
                                @foreach($students as $idx => $student)
                                <td>
                                    <input type="number"
                                           name="student_{{ $idx }}_part2_score"
                                           class="score-input part2-score-{{ $idx }}"
                                           min="0" max="30" step="0.01"
                                           placeholder="0.00"
                                           value="{{ $scores[$idx]['part2'] }}">
                                </td>
                                @endforeach
                            </tr>

                            {{-- ส่วนที่ 3 header --}}
                            <tr class="part3-section-header">
                                <td colspan="{{ 2 + $students->count() }}">
                                    <i class="bi bi-card-checklist me-1"></i>
                                    ส่วนที่ 3: การนำเสนอโครงงาน
                                    <span class="fw-normal ms-1">(รวม 60 คะแนน)</span>
                                </td>
                            </tr>

                            {{-- 3.1 --}}
                            <tr class="sub-criteria">
                                <td>3.1 ความเข้าใจในงานที่ทำ</td>
                                <td class="max-col">20</td>
                                @foreach($students as $idx => $student)
                                <td>
                                    <input type="number"
                                           name="student_{{ $idx }}_part3a_score"
                                           class="score-input part3a-score-{{ $idx }}"
                                           min="0" max="20" step="0.01"
                                           placeholder="0.00"
                                           value="{{ $scores[$idx]['part3a'] }}">
                                </td>
                                @endforeach
                            </tr>

                            {{-- 3.2 --}}
                            <tr class="sub-criteria">
                                <td>3.2 คุณภาพการนำเสนอและการตอบคำถาม</td>
                                <td class="max-col">20</td>
                                @foreach($students as $idx => $student)
                                <td>
                                    <input type="number"
                                           name="student_{{ $idx }}_part3b_score"
                                           class="score-input part3b-score-{{ $idx }}"
                                           min="0" max="20" step="0.01"
                                           placeholder="0.00"
                                           value="{{ $scores[$idx]['part3b'] }}">
                                </td>
                                @endforeach
                            </tr>

                            {{-- 3.3 --}}
                            <tr class="sub-criteria">
                                <td>3.3 การประยุกต์ใช้ความรู้ทางวิทยาการคอมพิวเตอร์อย่างเหมาะสมในการนำเสนอโครงงาน</td>
                                <td class="max-col">20</td>
                                @foreach($students as $idx => $student)
                                <td>
                                    <input type="number"
                                           name="student_{{ $idx }}_part3c_score"
                                           class="score-input part3c-score-{{ $idx }}"
                                           min="0" max="20" step="0.01"
                                           placeholder="0.00"
                                           value="{{ $scores[$idx]['part3c'] }}">
                                </td>
                                @endforeach
                            </tr>

                            {{-- รวมส่วนที่ 3 --}}
                            <tr class="part3-subtotal-row">
                                <td>รวมส่วนที่ 3</td>
                                <td class="text-center">60</td>
                                @foreach($students as $idx => $student)
                                <td class="text-center">
                                    <span class="part3-subtotal-display part3-sub-{{ $idx }}">0.00</span>
                                    <span class="text-muted small"> / 60</span>
                                </td>
                                @endforeach
                            </tr>

                            {{-- คะแนนรวมทั้งหมด --}}
                            <tr class="grand-total-row">
                                <td><strong>คะแนนรวมทั้งหมด</strong></td>
                                <td class="text-center"><strong>{{ $maxScore }}</strong></td>
                                @foreach($students as $idx => $student)
                                <td class="text-center">
                                    <span class="total-score-display total-score-{{ $idx }}">0.00</span>
                                    <span class="text-muted"> / {{ $maxScore }}</span>
                                </td>
                                @endforeach
                            </tr>

                        </tbody>
                    </table>
                </div>

                <div class="d-grid gap-2 mt-4">
                    @php
                        $isLocked = ($subjectLockMessage ?? null) !== null;
                    @endphp
                    <button type="submit" class="btn btn-success btn-lg" {{ $isLocked ? 'disabled' : '' }}>
                        <i class="bi bi-{{ $isLocked ? 'lock' : 'save' }} me-2"></i>
                        {{ $isLocked ? 'ปิดการบันทึกคะแนน' : ($evaluation ? 'บันทึกการแก้ไข' : 'บันทึกคะแนน') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isAdvisor = {{ $role === 'advisor' ? 'true' : 'false' }};
    const studentCount = {{ $students->count() }};

    function updateTotals() {
        for (let i = 0; i < studentCount; i++) {
            const part1  = isAdvisor ? (parseFloat(document.querySelector(`.part1-score-${i}`)?.value)  || 0) : 0;
            const part2  = parseFloat(document.querySelector(`.part2-score-${i}`)?.value)  || 0;
            const part3a = parseFloat(document.querySelector(`.part3a-score-${i}`)?.value) || 0;
            const part3b = parseFloat(document.querySelector(`.part3b-score-${i}`)?.value) || 0;
            const part3c = parseFloat(document.querySelector(`.part3c-score-${i}`)?.value) || 0;

            const part3Total = part3a + part3b + part3c;
            const grandTotal = part1 + part2 + part3Total;

            const sub = document.querySelector(`.part3-sub-${i}`);
            const tot = document.querySelector(`.total-score-${i}`);

            if (sub) sub.textContent = part3Total.toFixed(2);
            if (tot) {
                tot.textContent = grandTotal.toFixed(2);
                if      (grandTotal >= 90) tot.style.color = '#28a745';
                else if (grandTotal >= 80) tot.style.color = '#17a2b8';
                else if (grandTotal >= 70) tot.style.color = '#ffc107';
                else if (grandTotal >= 50) tot.style.color = '#fd7e14';
                else                       tot.style.color = '#dc3545';
            }
        }
    }

    document.querySelectorAll('.score-input').forEach(input => {
        input.addEventListener('input', function () {
            const max = parseFloat(this.getAttribute('max'));
            if (parseFloat(this.value) < 0)   this.value = 0;
            if (parseFloat(this.value) > max)  this.value = max;
            updateTotals();
        });
    });

    document.getElementById('evaluationForm').addEventListener('submit', function (e) {
        for (let i = 0; i < studentCount; i++) {
            const part2  = parseFloat(document.querySelector(`.part2-score-${i}`)?.value)  || 0;
            const part3a = parseFloat(document.querySelector(`.part3a-score-${i}`)?.value) || 0;
            const part3b = parseFloat(document.querySelector(`.part3b-score-${i}`)?.value) || 0;
            const part3c = parseFloat(document.querySelector(`.part3c-score-${i}`)?.value) || 0;
            const part1  = isAdvisor ? (parseFloat(document.querySelector(`.part1-score-${i}`)?.value) || 0) : 1;

            if (!isAdvisor && part2 === 0 && part3a === 0 && part3b === 0 && part3c === 0) {
                e.preventDefault();
                alert('กรุณากรอกคะแนนสำหรับนักศึกษาทุกคน');
                return;
            }
            if (isAdvisor && part1 === 0 && part2 === 0 && part3a === 0 && part3b === 0 && part3c === 0) {
                e.preventDefault();
                alert('กรุณากรอกคะแนนสำหรับนักศึกษาทุกคน');
                return;
            }
        }
    });

    updateTotals();
});
</script>
@endpush
@endsection

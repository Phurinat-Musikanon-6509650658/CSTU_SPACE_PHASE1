@extends('layouts.app')

@section('title', 'คะแนนประเมินโครงงาน')

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

    /* Score Table */
    .score-table thead th {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: white;
        text-align: center;
        vertical-align: middle;
        border-color: #3a5fc8;
        white-space: nowrap;
    }
    .score-table thead th.criteria-th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        text-align: left;
    }
    .score-table td { vertical-align: middle; }
    .score-table .max-col {
        text-align: center;
        color: #888;
        font-size: 0.88rem;
        white-space: nowrap;
    }
    .score-table .score-cell {
        text-align: center;
        font-weight: 600;
        font-size: 1rem;
    }
    .score-table .score-cell.empty { color: #bbb; font-weight: 400; }

    .part3-header-row td {
        background-color: #e8f4fd;
        font-weight: 700;
        color: #1a3a8a;
        border-top: 2px solid #4e73df;
    }
    .sub-criteria td:first-child {
        padding-left: 2.5rem;
        color: #555;
        font-size: 0.93rem;
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
        font-size: 1.05rem;
    }
    .avg-col {
        background-color: #f0f7ff !important;
        border-left: 2px solid #93c5fd !important;
    }

    /* Evaluator status */
    .evaluator-row {
        padding: 0.6rem 1rem;
        border-radius: 8px;
        margin-bottom: 0.5rem;
    }
    .evaluator-row.done    { background-color: #d1fae5; border-left: 4px solid #10b981; }
    .evaluator-row.pending { background-color: #f3f4f6; border-left: 4px solid #d1d5db; }

    /* Summary badges */
    .summary-score {
        font-size: 1.6rem;
        font-weight: 800;
        line-height: 1;
    }
    .grade-badge {
        font-size: 1.1rem;
        font-weight: 700;
        padding: 0.4rem 1rem;
        border-radius: 8px;
    }
    .student-tab-header {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: white;
        padding: 0.75rem 1.25rem;
        border-radius: 10px 10px 0 0;
        font-weight: 700;
        font-size: 1rem;
    }
    .empty-state { text-align: center; padding: 3rem 1rem; color: #6c757d; }
    .empty-state i { font-size: 3.5rem; margin-bottom: 1rem; opacity: 0.3; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    @php
        $roleLabels = [
            'advisor'    => 'อาจารย์ที่ปรึกษา',
            'committee1' => 'กรรมการ 1',
            'committee2' => 'กรรมการ 2',
            'committee3' => 'กรรมการ 3',
        ];
        $roleColors = [
            'advisor'    => 'primary',
            'committee1' => 'success',
            'committee2' => 'success',
            'committee3' => 'success',
        ];

        $students    = $project->group->members->pluck('student')->take(2);
        $activeRoles = [];
        if ($project->advisor_code)    $activeRoles[] = 'advisor';
        if ($project->committee1_code) $activeRoles[] = 'committee1';
        if ($project->committee2_code) $activeRoles[] = 'committee2';
        if ($project->committee3_code) $activeRoles[] = 'committee3';

        // ตรวจสอบว่าแต่ละนักศึกษาได้รับคะแนนครบจากทุก evaluator หรือยัง
        $studentComplete = [];
        foreach ($students as $idx => $student) {
            $submitted = $project->evaluations
                ->where('student_id', $student->student_id)
                ->pluck('evaluator_role')->unique()->values()->toArray();
            $studentComplete[$idx] = count($activeRoles) > 0
                && count(array_intersect($activeRoles, $submitted)) === count($activeRoles);
        }
        $allComplete = count($students) > 0 && !in_array(false, $studentComplete, true);

        // Evaluation matrix: [student_idx][role] = evaluation record
        $evalMatrix = [];
        foreach ($students as $idx => $student) {
            foreach ($activeRoles as $role) {
                $evalMatrix[$idx][$role] = $project->evaluations
                    ->where('student_id', $student->student_id)
                    ->where('evaluator_role', $role)
                    ->first();
            }
        }

        // Per-student summary averages
        $studentSummary = [];
        foreach ($students as $idx => $student) {
            $evals       = $project->evaluations->where('student_id', $student->student_id);
            $advisorEval = $evals->where('evaluator_role', 'advisor')->first();

            $p1    = $advisorEval ? (float)($advisorEval->part1_score  ?? 0) : 0;
            $p2avg = $evals->count() ? round($evals->avg('part2_score'),  2) : 0;
            $p3a   = $evals->count() ? round($evals->avg('part3a_score'), 2) : 0;
            $p3b   = $evals->count() ? round($evals->avg('part3b_score'), 2) : 0;
            $p3c   = $evals->count() ? round($evals->avg('part3c_score'), 2) : 0;
            $p3tot = $p3a + $p3b + $p3c;
            $final = $p1 + $p2avg + $p3tot;

            $studentSummary[$idx] = [
                'part1'    => $p1,
                'part2avg' => $p2avg,
                'part3a'   => $p3a,
                'part3b'   => $p3b,
                'part3c'   => $p3c,
                'part3tot' => $p3tot,
                'final'    => $final,
            ];
        }

        // Grade color helper
        $gradeColor = function($score) {
            if ($score >= 90) return 'success';
            if ($score >= 85) return 'info';
            if ($score >= 80) return 'info';
            if ($score >= 75) return 'primary';
            if ($score >= 70) return 'warning';
            if ($score >= 60) return 'orange';
            if ($score >= 50) return 'danger';
            return 'dark';
        };
    @endphp

    <!-- Header -->
    <div class="mb-4">
        <a href="{{ route('coordinator.evaluations.index') }}" class="btn btn-outline-primary mb-3">
            <i class="bi bi-arrow-left me-2"></i>กลับรายการ
        </a>
        <h1 class="h2 fw-bold">
            <i class="bi bi-clipboard-data me-2 text-primary"></i>คะแนนประเมินโครงงาน
        </h1>
    </div>

    {{-- Status banner --}}
    @if($allComplete)
        <div class="alert alert-success d-flex align-items-center gap-3 rounded-3 mb-4">
            <i class="bi bi-check-circle-fill fs-4"></i>
            <div>
                <strong>คะแนนสมบูรณ์</strong> — ผู้ประเมินทุกคน ({{ count($activeRoles) }} คน) ส่งคะแนนครบแล้ว
                คะแนนเฉลี่ยและเกรดด้านล่างคือคะแนนจริง
            </div>
        </div>
    @elseif($project->evaluations->count() > 0)
        @php
            $doneCount = $project->evaluations->pluck('evaluator_role')->unique()->count();
        @endphp
        <div class="alert alert-warning d-flex align-items-center gap-3 rounded-3 mb-4">
            <i class="bi bi-hourglass-split fs-4"></i>
            <div>
                <strong>รอคะแนนเพิ่มเติม</strong> — ส่งคะแนนแล้ว {{ $doneCount }} / {{ count($activeRoles) }} คน
                คะแนนที่แสดงเป็นค่าประมาณเบื้องต้น
            </div>
        </div>
    @else
        <div class="alert alert-secondary d-flex align-items-center gap-3 rounded-3 mb-4">
            <i class="bi bi-clock fs-4"></i>
            <div><strong>ยังไม่มีคะแนน</strong> — รอให้อาจารย์และคณะกรรมการส่งคะแนน</div>
        </div>
    @endif

    <!-- Project Info -->
    <div class="card mb-4">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
            <h5 class="mb-0 text-white"><i class="bi bi-folder2-open me-2"></i>ข้อมูลโครงงาน</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2"><strong>รหัสโครงงาน:</strong> <code class="text-primary fs-5">{{ $project->project_code }}</code></p>
                    <p class="mb-2"><strong>ชื่อโครงงาน:</strong> {{ $project->project_name ?? 'ยังไม่ระบุ' }}</p>
                    <p class="mb-0"><strong>สมาชิก:</strong>
                        @foreach($project->group->members as $member)
                            {{ $member->student->firstname_std ?? '' }} {{ $member->student->lastname_std ?? '' }}@if(!$loop->last), @endif
                        @endforeach
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><strong>อาจารย์ที่ปรึกษา:</strong>
                        @if($project->advisor)
                            <span class="badge bg-primary">{{ $project->advisor_code }} — {{ $project->advisor->firstname_user }} {{ $project->advisor->lastname_user }}</span>
                        @else
                            <span class="text-muted">ยังไม่กำหนด</span>
                        @endif
                    </p>
                    <p class="mb-1"><strong>คณะกรรมการ:</strong></p>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach(['committee1','committee2','committee3'] as $cr)
                            @if($project->{$cr})
                                <span class="badge bg-success">{{ $project->{$cr.'_code'} }} — {{ $project->{$cr}->firstname_user }}</span>
                            @endif
                        @endforeach
                        @if(!$project->committee1 && !$project->committee2 && !$project->committee3)
                            <span class="text-muted">ยังไม่กำหนด</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($project->evaluations->count() > 0)

        {{-- ===== Score Table Per Student ===== --}}
        @foreach($students as $idx => $student)
            <div class="mb-4">
                <div class="student-tab-header">
                    <i class="bi bi-person-circle me-2"></i>
                    {{ $student->firstname_std }} {{ $student->lastname_std }}
                    <small class="opacity-75 ms-2">{{ $student->student_code }}</small>
                </div>
                <div class="card" style="border-radius: 0 0 12px 12px; margin-bottom: 0;">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered score-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="criteria-th" style="width: 35%">เกณฑ์การประเมิน</th>
                                        <th style="width: 7%">คะแนนเต็ม</th>
                                        @foreach($activeRoles as $role)
                                            <th>
                                                {{ $roleLabels[$role] }}<br>
                                                <small class="fw-normal opacity-80">
                                                    @if($project->{$role})
                                                        {{ $project->{$role}->firstname_user }}
                                                    @else
                                                        {{ $project->{$role.'_code'} }}
                                                    @endif
                                                </small>
                                            </th>
                                        @endforeach
                                        <th class="avg-col">เฉลี่ย</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Part 1: advisor only --}}
                                    <tr>
                                        <td>
                                            <strong>ส่วนที่ 1:</strong> ความก้าวหน้าโครงงาน
                                            <small class="text-muted">(ให้โดยอาจารย์ที่ปรึกษา)</small>
                                        </td>
                                        <td class="max-col">10</td>
                                        @foreach($activeRoles as $role)
                                            @php $ev = $evalMatrix[$idx][$role] ?? null; @endphp
                                            <td class="score-cell {{ ($role !== 'advisor') ? 'text-muted' : '' }}">
                                                @if($role === 'advisor')
                                                    @if($ev)
                                                        <span class="text-info">{{ number_format($ev->part1_score ?? 0, 2) }}</span>
                                                        <small class="text-muted">/10</small>
                                                    @else
                                                        <span class="empty">รอ</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted" style="font-size:1.1rem">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="score-cell avg-col text-info">
                                            {{ number_format($studentSummary[$idx]['part1'], 2) }}
                                            <small class="text-muted">/10</small>
                                        </td>
                                    </tr>

                                    {{-- Part 2 --}}
                                    <tr>
                                        <td><strong>ส่วนที่ 2:</strong> คุณภาพของรายงาน</td>
                                        <td class="max-col">30</td>
                                        @foreach($activeRoles as $role)
                                            @php $ev = $evalMatrix[$idx][$role] ?? null; @endphp
                                            <td class="score-cell">
                                                @if($ev)
                                                    <span class="text-primary">{{ number_format($ev->part2_score ?? 0, 2) }}</span>
                                                    <small class="text-muted">/30</small>
                                                @else
                                                    <span class="empty text-muted">รอ</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="score-cell avg-col text-primary">
                                            {{ number_format($studentSummary[$idx]['part2avg'], 2) }}
                                            <small class="text-muted">/30</small>
                                        </td>
                                    </tr>

                                    {{-- Part 3 section header --}}
                                    <tr class="part3-header-row">
                                        <td colspan="{{ 2 + count($activeRoles) + 1 }}">
                                            <i class="bi bi-card-checklist me-1"></i>
                                            ส่วนที่ 3: การนำเสนอโครงงาน
                                            <span class="fw-normal ms-1">(รวม 60 คะแนน)</span>
                                        </td>
                                    </tr>

                                    {{-- Part 3a --}}
                                    <tr class="sub-criteria">
                                        <td>3.1 ความเข้าใจในงานที่ทำ</td>
                                        <td class="max-col">20</td>
                                        @foreach($activeRoles as $role)
                                            @php $ev = $evalMatrix[$idx][$role] ?? null; @endphp
                                            <td class="score-cell">
                                                @if($ev)
                                                    <span class="text-warning">{{ number_format($ev->part3a_score ?? 0, 2) }}</span>
                                                    <small class="text-muted">/20</small>
                                                @else
                                                    <span class="empty text-muted">รอ</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="score-cell avg-col text-warning">
                                            {{ number_format($studentSummary[$idx]['part3a'], 2) }}
                                            <small class="text-muted">/20</small>
                                        </td>
                                    </tr>

                                    {{-- Part 3b --}}
                                    <tr class="sub-criteria">
                                        <td>3.2 คุณภาพการนำเสนอและการตอบคำถาม</td>
                                        <td class="max-col">20</td>
                                        @foreach($activeRoles as $role)
                                            @php $ev = $evalMatrix[$idx][$role] ?? null; @endphp
                                            <td class="score-cell">
                                                @if($ev)
                                                    <span class="text-warning">{{ number_format($ev->part3b_score ?? 0, 2) }}</span>
                                                    <small class="text-muted">/20</small>
                                                @else
                                                    <span class="empty text-muted">รอ</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="score-cell avg-col text-warning">
                                            {{ number_format($studentSummary[$idx]['part3b'], 2) }}
                                            <small class="text-muted">/20</small>
                                        </td>
                                    </tr>

                                    {{-- Part 3c --}}
                                    <tr class="sub-criteria">
                                        <td>3.3 การประยุกต์ใช้ความรู้ทางวิทยาการคอมพิวเตอร์อย่างเหมาะสมในการนำเสนอโครงงาน</td>
                                        <td class="max-col">20</td>
                                        @foreach($activeRoles as $role)
                                            @php $ev = $evalMatrix[$idx][$role] ?? null; @endphp
                                            <td class="score-cell">
                                                @if($ev)
                                                    <span class="text-warning">{{ number_format($ev->part3c_score ?? 0, 2) }}</span>
                                                    <small class="text-muted">/20</small>
                                                @else
                                                    <span class="empty text-muted">รอ</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="score-cell avg-col text-warning">
                                            {{ number_format($studentSummary[$idx]['part3c'], 2) }}
                                            <small class="text-muted">/20</small>
                                        </td>
                                    </tr>

                                    {{-- Part 3 subtotal --}}
                                    <tr class="part3-subtotal-row">
                                        <td>รวมส่วนที่ 3</td>
                                        <td class="text-center fw-bold">60</td>
                                        @foreach($activeRoles as $role)
                                            @php $ev = $evalMatrix[$idx][$role] ?? null; @endphp
                                            <td class="score-cell">
                                                @if($ev)
                                                    <span style="color:#224abe">{{ number_format($ev->part3_score ?? 0, 2) }}</span>
                                                    <small class="text-muted">/60</small>
                                                @else
                                                    <span class="text-muted">รอ</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="score-cell avg-col" style="color:#224abe">
                                            {{ number_format($studentSummary[$idx]['part3tot'], 2) }}
                                            <small class="text-muted">/60</small>
                                        </td>
                                    </tr>

                                    {{-- Grand total --}}
                                    <tr class="grand-total-row">
                                        <td><strong>คะแนนรวมทั้งสิ้น</strong></td>
                                        <td class="text-center">100</td>
                                        @foreach($activeRoles as $role)
                                            @php $ev = $evalMatrix[$idx][$role] ?? null; @endphp
                                            <td class="score-cell">
                                                @if($ev)
                                                    @php
                                                        $tot = $ev->total_score ?? 0;
                                                        $maxR = ($role === 'advisor') ? 100 : 90;
                                                    @endphp
                                                    <span class="text-primary">{{ number_format($tot, 2) }}</span>
                                                    <small class="text-muted">/{{ $maxR }}</small>
                                                @else
                                                    <span class="text-muted">รอ</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="score-cell avg-col">
                                            @php
                                                $fs = $studentSummary[$idx]['final'];
                                                $fc = $gradeColor($fs);
                                            @endphp
                                            <span class="text-{{ $fc }}" style="font-size:1.15rem">
                                                {{ number_format($fs, 2) }}
                                            </span>
                                            <small class="text-muted">/100</small>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Per-student summary bar --}}
                @php
                    $fs   = $studentSummary[$idx]['final'];
                    $isDone = $studentComplete[$idx] ?? false;
                    $gc   = 'primary';
                @endphp
                <div class="card mt-2 {{ $isDone ? 'border-success' : '' }}" style="{{ $isDone ? 'border-width:2px !important;' : '' }}">
                    @if($isDone)
                        <div class="card-header py-2 text-white" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); border-radius: 0 !important;">
                            <small><i class="bi bi-check-circle-fill me-1"></i>คะแนนสมบูรณ์ — ประเมินครบ {{ count($activeRoles) }} คนแล้ว</small>
                        </div>
                    @else
                        <div class="card-header py-2 bg-warning bg-opacity-25" style="border-radius: 0 !important;">
                            <small class="text-warning-emphasis"><i class="bi bi-hourglass-split me-1"></i>คะแนนเบื้องต้น — รอผู้ประเมินบางคน</small>
                        </div>
                    @endif
                    <div class="card-body py-3">
                        <div class="row align-items-center text-center g-3">
                            <div class="col">
                                <div class="text-muted small mb-1">ส่วนที่ 1</div>
                                <div class="fw-bold text-info">{{ number_format($studentSummary[$idx]['part1'], 2) }}</div>
                                <div class="text-muted" style="font-size:0.78rem">/10</div>
                            </div>
                            <div class="col">
                                <div class="text-muted small mb-1">ส่วนที่ 2</div>
                                <div class="fw-bold text-primary">{{ number_format($studentSummary[$idx]['part2avg'], 2) }}</div>
                                <div class="text-muted" style="font-size:0.78rem">/30</div>
                            </div>
                            <div class="col">
                                <div class="text-muted small mb-1">3.1 ความเข้าใจ</div>
                                <div class="fw-bold text-warning">{{ number_format($studentSummary[$idx]['part3a'], 2) }}</div>
                                <div class="text-muted" style="font-size:0.78rem">/20</div>
                            </div>
                            <div class="col">
                                <div class="text-muted small mb-1">3.2 นำเสนอ</div>
                                <div class="fw-bold text-warning">{{ number_format($studentSummary[$idx]['part3b'], 2) }}</div>
                                <div class="text-muted" style="font-size:0.78rem">/20</div>
                            </div>
                            <div class="col">
                                <div class="text-muted small mb-1">3.3 ประยุกต์</div>
                                <div class="fw-bold text-warning">{{ number_format($studentSummary[$idx]['part3c'], 2) }}</div>
                                <div class="text-muted" style="font-size:0.78rem">/20</div>
                            </div>
                            <div class="col border-start">
                                <div class="text-muted small mb-1">คะแนนรวม</div>
                                <div class="fw-bold text-{{ $gc }}" style="font-size:1.4rem">{{ number_format($fs, 2) }}</div>
                                <div class="text-muted" style="font-size:0.78rem">/100</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

    @else
        <div class="empty-state">
            <i class="bi bi-clipboard-x d-block"></i>
            <h5 class="text-muted">ยังไม่มีการให้คะแนน</h5>
            <p class="text-muted">รอให้อาจารย์และคณะกรรมการให้คะแนนโครงงาน</p>
        </div>
    @endif

    {{-- ===== Evaluator Status ===== --}}
    <div class="card mt-2">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-bold">
                <i class="bi bi-person-lines-fill me-2"></i>สถานะผู้ประเมิน
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-2">
                @if($project->advisor_code)
                    @php
                        $submitted = $project->evaluations
                            ->where('evaluator_role', 'advisor')
                            ->where('evaluator_code', $project->advisor_code)
                            ->isNotEmpty();
                    @endphp
                    <div class="col-md-6">
                        <div class="evaluator-row {{ $submitted ? 'done' : 'pending' }} d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-primary me-2">อาจารย์ที่ปรึกษา</span>
                                <strong>{{ $project->advisor_code }}</strong>
                                @if($project->advisor)
                                    — {{ $project->advisor->firstname_user }} {{ $project->advisor->lastname_user }}
                                @endif
                            </div>
                            @if($submitted)
                                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            @else
                                <i class="bi bi-clock text-warning fs-5"></i>
                            @endif
                        </div>
                    </div>
                @endif

                @foreach(['committee1','committee2','committee3'] as $cr)
                    @if($project->{$cr.'_code'})
                        @php
                            $submitted = $project->evaluations
                                ->where('evaluator_role', $cr)
                                ->where('evaluator_code', $project->{$cr.'_code'})
                                ->isNotEmpty();
                            $label = $roleLabels[$cr];
                        @endphp
                        <div class="col-md-6">
                            <div class="evaluator-row {{ $submitted ? 'done' : 'pending' }} d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-success me-2">{{ $label }}</span>
                                    <strong>{{ $project->{$cr.'_code'} }}</strong>
                                    @if($project->{$cr})
                                        — {{ $project->{$cr}->firstname_user }} {{ $project->{$cr}->lastname_user }}
                                    @endif
                                </div>
                                @if($submitted)
                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                @else
                                    <i class="bi bi-clock text-warning fs-5"></i>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection

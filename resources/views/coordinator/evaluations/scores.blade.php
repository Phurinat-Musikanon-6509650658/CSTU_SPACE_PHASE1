@extends('layouts.app')

@section('title', 'คะแนนประเมิน – ' . ($project->project_code ?? '') . ' | CSTU SPACE')

@push('styles')
<style>
/* Hero */
.scores-hero {
    border: none; border-radius: 16px;
    background: linear-gradient(135deg, #fff 0%, #f4f6ff 100%);
    border-top: 5px solid #4e73df;
    box-shadow: 0 4px 20px rgba(78,115,223,.12);
}
/* Section cards */
.section-card { border: none; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,.07); overflow: hidden; }
.section-card .card-header {
    background: #fff; border-bottom: 1px solid #f0f1f5;
    padding: 14px 20px; font-size: .88rem;
}
/* Evaluator status rows */
.evaluator-row {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 20px; border-bottom: 1px solid #f4f5f7;
    transition: background .12s;
}
.evaluator-row:last-child { border-bottom: none; }
.evaluator-row:hover { background: #fafbff; }
.person-avatar {
    width: 38px; height: 38px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .82rem; font-weight: 700; color: #fff; flex-shrink: 0;
}
/* Score table */
.score-table { font-size: .86rem; }
.score-table thead th {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    color: #fff; text-align: center; vertical-align: middle;
    border-color: #3a5fc8; white-space: nowrap;
    font-size: .76rem; font-weight: 600; padding: .8rem .6rem;
    text-transform: uppercase; letter-spacing: .04em;
}
.score-table thead th.th-criteria {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    text-align: left; font-size: .8rem;
}
.score-table thead th.avg-col-th {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    border-left: 3px solid #a78bfa !important;
    font-size: .76rem; letter-spacing: .06em;
}
.score-table td { vertical-align: middle; padding: .65rem .75rem; }
.score-table .score-cell { text-align: center; font-weight: 600; font-size: .9rem; }
.score-table .avg-col {
    background: linear-gradient(180deg, #f5f0ff 0%, #ede9fe 100%) !important;
    border-left: 3px solid #a78bfa !important;
    text-align: center; font-weight: 700;
}
.score-table .max-col { text-align: center; color: #9ca3af; font-size: .8rem; }
/* "รอ" pending badge */
.badge-pending {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: .68rem; font-weight: 600; padding: 3px 7px;
    border-radius: 20px;
    background: #fef9c3; color: #92400e;
    border: 1px solid #fde68a;
    white-space: nowrap;
}
.row-part3-header { background: #eff3ff; }
.row-part3-header td { font-weight: 700; color: #3b4fd0; border-top: 2px solid #c7d2fe !important; font-size: .85rem; }
.row-sub-criteria td:first-child { padding-left: 2.2rem; color: #555; }
.row-subtotal { background: #e8efff; }
.row-subtotal td { font-weight: 600; border-top: 1px solid #a5b4fc !important; border-bottom: 2px solid #818cf8 !important; }
.row-total { background: linear-gradient(135deg, #e8f0ff 0%, #dce9ff 100%); }
.row-total td { font-weight: 700; font-size: .92rem; border-top: 2px solid #818cf8 !important; }
/* Summary bar */
.score-summary-col { text-align: center; padding: .8rem .5rem; }
.score-summary-col .label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #9ca3af; margin-bottom: 5px; }
.score-summary-col .value { font-size: 1.2rem; font-weight: 800; line-height: 1; }
.score-summary-col .max  { font-size: .7rem; color: #9ca3af; margin-top: 3px; }
.score-summary-col.final-col { background: linear-gradient(135deg, #f5f0ff 0%, #ede9fe 100%); border-radius: 0 0 14px 0; }
.score-summary-col.final-col .label { color: #7c3aed; }
.score-summary-col.final-col .value { font-size: 1.8rem; }
/* Student tab header */
.student-tab-header {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    color: #fff; padding: .75rem 1.25rem;
    border-radius: 14px 14px 0 0;
    font-weight: 700; font-size: .92rem;
    display: flex; align-items: center; gap: .5rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- Back -->
    <a href="{{ route('coordinator.evaluations.index') }}" class="btn btn-link text-decoration-none ps-0 text-muted mb-3 d-inline-flex align-items-center">
        <i class="bi bi-chevron-left me-1"></i><span class="small">กลับรายการ</span>
    </a>

    @php
        $roleLabels = [
            'advisor'    => 'ที่ปรึกษา',
            'committee1' => 'กรรมการ 1',
            'committee2' => 'กรรมการ 2',
            'committee3' => 'กรรมการ 3',
        ];
        $roleGradients = [
            'advisor'    => 'linear-gradient(135deg,#4e73df,#2e59d9)',
            'committee1' => 'linear-gradient(135deg,#1cc88a,#13855c)',
            'committee2' => 'linear-gradient(135deg,#36b9cc,#1a7486)',
            'committee3' => 'linear-gradient(135deg,#858796,#5a5c69)',
        ];

        $students   = $project->group->members->pluck('student')->take(2);
        $activeRoles = [];
        if ($project->advisor_code)    $activeRoles[] = 'advisor';
        if ($project->committee1_code) $activeRoles[] = 'committee1';
        if ($project->committee2_code) $activeRoles[] = 'committee2';
        if ($project->committee3_code) $activeRoles[] = 'committee3';

        // Which roles have submitted
        $submittedRoles = $project->evaluations->pluck('evaluator_role')->unique()->values()->toArray();

        // Completion check per student
        $studentComplete = [];
        foreach ($students as $idx => $student) {
            $done = $project->evaluations
                ->where('student_id', $student->student_id)
                ->pluck('evaluator_role')->unique()->values()->toArray();
            $studentComplete[$idx] = count($activeRoles) > 0
                && count(array_intersect($activeRoles, $done)) === count($activeRoles);
        }
        $allComplete = count($students) > 0 && !in_array(false, $studentComplete, true);
        $doneRoles   = count($submittedRoles);

        // Evaluation matrix: [student_idx][role] = eval record
        $evalMatrix = [];
        foreach ($students as $idx => $student) {
            foreach ($activeRoles as $role) {
                $evalMatrix[$idx][$role] = $project->evaluations
                    ->where('student_id', $student->student_id)
                    ->where('evaluator_role', $role)->first();
            }
        }

        // Per-student averages
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
            $studentSummary[$idx] = compact('p1','p2avg','p3a','p3b','p3c','p3tot','final');
        }

        $scoreColor = function($s) {
            if ($s >= 90) return '#1cc88a';
            if ($s >= 80) return '#36b9cc';
            if ($s >= 70) return '#4e73df';
            if ($s >= 60) return '#f6c23e';
            return '#e74a3b';
        };
    @endphp

    <!-- Hero Card -->
    <div class="scores-hero card mb-4">
        <div class="card-body px-4 py-4">
            <div class="row align-items-center g-3">
                <div class="col-lg-6">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div style="width:50px;height:50px;border-radius:12px;background:linear-gradient(135deg,#667eea,#764ba2);flex-shrink:0;box-shadow:0 4px 14px rgba(102,126,234,.4);display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-clipboard-data text-white" style="font-size:1.3rem;"></i>
                        </div>
                        <div>
                            <div class="small text-muted fw-semibold text-uppercase" style="letter-spacing:.05em;">คะแนนประเมินโครงงาน</div>
                            <h1 class="h5 fw-bold mb-0 text-dark">{{ $project->project_name ?? 'ยังไม่ระบุชื่อ' }}</h1>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <code class="text-primary fw-bold" style="font-size:.82rem;">{{ $project->project_code }}</code>
                        <span class="text-muted">·</span>
                        <span class="text-muted small">
                            <i class="bi bi-people me-1"></i>
                            @foreach($project->group->members as $member)
                                {{ $member->student->firstname_std ?? '' }}@if(!$loop->last), @endif
                            @endforeach
                        </span>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="small text-muted mb-1">ผู้ประเมิน</div>
                            <div class="fw-bold h5 mb-0">{{ $doneRoles }} / {{ count($activeRoles) }}</div>
                        </div>
                        <div class="col-4">
                            <div class="small text-muted mb-1">สถานะ</div>
                            @if($allComplete)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">ครบสมบูรณ์</span>
                            @elseif($doneRoles > 0)
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">รอเพิ่มเติม</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">ยังไม่มีคะแนน</span>
                            @endif
                        </div>
                        <div class="col-4">
                            <div class="small text-muted mb-1">นักศึกษา</div>
                            <div class="fw-bold h5 mb-0">{{ $students->count() }} คน</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Evaluator Status -->
    <div class="section-card card mb-4">
        <div class="card-header d-flex align-items-center gap-2">
            <div style="width:28px;height:28px;border-radius:7px;background:#e8f4ff;display:flex;align-items:center;justify-content:center;font-size:.75rem;color:#4e73df;flex-shrink:0;">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <span class="fw-semibold">สถานะผู้ประเมิน</span>
        </div>
        <div class="card-body p-0">
            @foreach([
                ['advisor',    $project->advisor_code,    $project->advisor    ?? null],
                ['committee1', $project->committee1_code, $project->committee1 ?? null],
                ['committee2', $project->committee2_code, $project->committee2 ?? null],
                ['committee3', $project->committee3_code, $project->committee3 ?? null],
            ] as [$role, $code, $user])
                @if($code)
                    @php
                        $submitted = $project->evaluations
                            ->where('evaluator_role', $role)
                            ->where('evaluator_code', $code)->isNotEmpty();
                        $initial = $user ? strtoupper(mb_substr($user->firstname_user ?? '?', 0, 1)) : strtoupper(substr($code, 0, 1));
                    @endphp
                    <div class="evaluator-row">
                        <div class="person-avatar" style="background:{{ $roleGradients[$role] }};">{{ $initial }}</div>
                        <div class="flex-fill">
                            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;margin-bottom:2px;">{{ $roleLabels[$role] }}</div>
                            <div class="fw-semibold small text-dark">
                                {{ $user ? $user->firstname_user . ' ' . $user->lastname_user : $code }}
                            </div>
                            <code class="text-muted" style="font-size:.7rem;">{{ $code }}</code>
                        </div>
                        @if($submitted)
                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:.72rem;">
                                <i class="bi bi-check-circle-fill me-1"></i>ให้คะแนนแล้ว
                            </span>
                        @else
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size:.72rem;">
                                <i class="bi bi-hourglass-split me-1"></i>รอคะแนน
                            </span>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Score Tables: one per student, evaluators as columns -->
    @foreach($students as $idx => $student)
    @php
        $isDone  = $studentComplete[$idx] ?? false;
        $summary = $studentSummary[$idx];
        $fs      = $summary['final'];
        $fsColor = $scoreColor($fs);
    @endphp

    <div class="mb-4">
        <!-- Student header -->
        <div class="student-tab-header">
            <div style="width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.25);display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:700;flex-shrink:0;">
                {{ strtoupper(mb_substr($student->firstname_std ?? 'N', 0, 1)) }}
            </div>
            <div>
                {{ $student->firstname_std }} {{ $student->lastname_std }}
                <span class="opacity-75 ms-2 fw-normal" style="font-size:.8rem;">{{ $student->student_code ?? '' }}</span>
            </div>
            <div class="ms-auto">
                @if($isDone)
                    <span class="badge bg-white text-success" style="font-size:.72rem;">
                        <i class="bi bi-check-circle-fill me-1"></i>ครบสมบูรณ์
                    </span>
                @else
                    <span class="badge" style="background:rgba(255,255,255,.2);font-size:.72rem;">
                        <i class="bi bi-hourglass-split me-1"></i>รอคะแนนบางส่วน
                    </span>
                @endif
            </div>
        </div>

        <!-- Score Table: evaluators as columns -->
        <div class="card border-0 shadow-sm" style="border-radius:0 0 14px 14px;">
            <div class="table-responsive">
                <table class="table table-bordered score-table mb-0">
                    <thead>
                        <tr>
                            <th class="th-criteria" style="width:34%">เกณฑ์การประเมิน</th>
                            <th style="width:7%">เต็ม</th>
                            @foreach($activeRoles as $role)
                                @php
                                    $roleUser = match($role) {
                                        'advisor'    => $project->advisor    ?? null,
                                        'committee1' => $project->committee1 ?? null,
                                        'committee2' => $project->committee2 ?? null,
                                        'committee3' => $project->committee3 ?? null,
                                        default      => null,
                                    };
                                @endphp
                                <th>
                                    {{ $roleLabels[$role] }}<br>
                                    <span class="fw-normal opacity-80" style="font-size:.72rem;">
                                        {{ $roleUser ? $roleUser->firstname_user : $project->{$role.'_code'} }}
                                    </span>
                                </th>
                            @endforeach
                            <th class="avg-col avg-col-th"><i class="bi bi-calculator me-1"></i>เฉลี่ย</th>
                        </tr>
                    </thead>
                    <tbody>

                        {{-- Part 1: advisor only --}}
                        <tr>
                            <td>
                                <span class="fw-semibold">ส่วนที่ 1</span>
                                <span class="text-muted ms-1">ความก้าวหน้าโครงงาน</span>
                                <div class="text-muted" style="font-size:.74rem;">(ให้โดยอาจารย์ที่ปรึกษาเท่านั้น)</div>
                            </td>
                            <td class="max-col">10</td>
                            @foreach($activeRoles as $role)
                                @php $ev = $evalMatrix[$idx][$role] ?? null; @endphp
                                <td class="score-cell">
                                    @if($role === 'advisor')
                                        @if($ev)
                                            <span class="text-info fw-bold">{{ number_format($ev->part1_score ?? 0, 1) }}</span>
                                        @else
                                            <span class="badge-pending"><i class="bi bi-hourglass-split"></i>รอ</span>
                                        @endif
                                    @else
                                        <span class="text-muted" style="font-size:.8rem;">—</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="avg-col"><span class="text-info fw-bold">{{ number_format($summary['p1'], 1) }}</span></td>
                        </tr>

                        {{-- Part 2 --}}
                        <tr>
                            <td>
                                <span class="fw-semibold">ส่วนที่ 2</span>
                                <span class="text-muted ms-1">คุณภาพของรายงาน</span>
                            </td>
                            <td class="max-col">30</td>
                            @foreach($activeRoles as $role)
                                @php $ev = $evalMatrix[$idx][$role] ?? null; @endphp
                                <td class="score-cell">
                                    @if($ev)
                                        <span class="text-primary fw-bold">{{ number_format($ev->part2_score ?? 0, 1) }}</span>
                                    @else
                                        <span class="badge-pending"><i class="bi bi-hourglass-split"></i>รอ</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="avg-col"><span class="text-primary fw-bold">{{ number_format($summary['p2avg'], 1) }}</span></td>
                        </tr>

                        {{-- Part 3 header --}}
                        <tr class="row-part3-header">
                            <td colspan="{{ 2 + count($activeRoles) + 1 }}">
                                <i class="bi bi-card-checklist me-1"></i>
                                <span class="fw-bold">ส่วนที่ 3</span>
                                <span class="fw-normal ms-1">การนำเสนอโครงงาน</span>
                                <span class="text-muted ms-1" style="font-size:.8rem;">(60 คะแนน)</span>
                            </td>
                        </tr>

                        {{-- Part 3a --}}
                        <tr class="row-sub-criteria">
                            <td>3.1 ความเข้าใจในงานที่ทำ</td>
                            <td class="max-col">20</td>
                            @foreach($activeRoles as $role)
                                @php $ev = $evalMatrix[$idx][$role] ?? null; @endphp
                                <td class="score-cell">
                                    @if($ev)
                                        <span class="text-warning fw-bold">{{ number_format($ev->part3a_score ?? 0, 1) }}</span>
                                    @else
                                        <span class="badge-pending"><i class="bi bi-hourglass-split"></i>รอ</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="avg-col"><span class="text-warning fw-bold">{{ number_format($summary['p3a'], 1) }}</span></td>
                        </tr>

                        {{-- Part 3b --}}
                        <tr class="row-sub-criteria">
                            <td>3.2 คุณภาพการนำเสนอและการตอบคำถาม</td>
                            <td class="max-col">20</td>
                            @foreach($activeRoles as $role)
                                @php $ev = $evalMatrix[$idx][$role] ?? null; @endphp
                                <td class="score-cell">
                                    @if($ev)
                                        <span class="text-warning fw-bold">{{ number_format($ev->part3b_score ?? 0, 1) }}</span>
                                    @else
                                        <span class="badge-pending"><i class="bi bi-hourglass-split"></i>รอ</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="avg-col"><span class="text-warning fw-bold">{{ number_format($summary['p3b'], 1) }}</span></td>
                        </tr>

                        {{-- Part 3c --}}
                        <tr class="row-sub-criteria">
                            <td style="font-size:.82rem;">3.3 การประยุกต์ใช้ความรู้ทางวิทยาการคอมพิวเตอร์ในการนำเสนอ</td>
                            <td class="max-col">20</td>
                            @foreach($activeRoles as $role)
                                @php $ev = $evalMatrix[$idx][$role] ?? null; @endphp
                                <td class="score-cell">
                                    @if($ev)
                                        <span class="text-warning fw-bold">{{ number_format($ev->part3c_score ?? 0, 1) }}</span>
                                    @else
                                        <span class="badge-pending"><i class="bi bi-hourglass-split"></i>รอ</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="avg-col"><span class="text-warning fw-bold">{{ number_format($summary['p3c'], 1) }}</span></td>
                        </tr>

                        {{-- Part 3 subtotal --}}
                        <tr class="row-subtotal">
                            <td class="fw-bold">รวมส่วนที่ 3</td>
                            <td class="text-center fw-bold">60</td>
                            @foreach($activeRoles as $role)
                                @php $ev = $evalMatrix[$idx][$role] ?? null; @endphp
                                <td class="score-cell">
                                    @if($ev)
                                        <span style="color:#3b4fd0;font-weight:700;">{{ number_format($ev->part3_score ?? 0, 1) }}</span>
                                    @else
                                        <span class="badge-pending"><i class="bi bi-hourglass-split"></i>รอ</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="avg-col" style="color:#3b4fd0;font-weight:700;">{{ number_format($summary['p3tot'], 1) }}</td>
                        </tr>

                        {{-- Grand total --}}
                        <tr class="row-total">
                            <td class="fw-bold">คะแนนรวมทั้งสิ้น</td>
                            <td class="text-center fw-bold">100</td>
                            @foreach($activeRoles as $role)
                                @php
                                    $ev   = $evalMatrix[$idx][$role] ?? null;
                                    $maxR = ($role === 'advisor') ? 100 : 90;
                                @endphp
                                <td class="score-cell">
                                    @if($ev)
                                        <span class="text-primary fw-bold">{{ number_format($ev->total_score ?? 0, 1) }}</span>
                                        <span class="text-muted" style="font-size:.72rem;">/{{ $maxR }}</span>
                                    @else
                                        <span class="badge-pending"><i class="bi bi-hourglass-split"></i>รอ</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="avg-col">
                                <span class="fw-bold" style="font-size:1.05rem; color:{{ $fsColor }};">
                                    {{ number_format($fs, 1) }}
                                </span>
                                <span class="text-muted" style="font-size:.72rem;">/100</span>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <!-- Summary bar -->
            <div class="px-4 py-3 border-top" style="background:{{ $isDone ? '#f0fdf4' : '#fffbeb' }};">
                <div class="row g-0 align-items-center">
                    <div class="col score-summary-col border-end">
                        <div class="label">ส่วนที่ 1</div>
                        <div class="value text-info">{{ number_format($summary['p1'], 1) }}</div>
                        <div class="max">/10</div>
                    </div>
                    <div class="col score-summary-col border-end">
                        <div class="label">ส่วนที่ 2</div>
                        <div class="value text-primary">{{ number_format($summary['p2avg'], 1) }}</div>
                        <div class="max">/30</div>
                    </div>
                    <div class="col score-summary-col border-end">
                        <div class="label">3.1 ความเข้าใจ</div>
                        <div class="value text-warning">{{ number_format($summary['p3a'], 1) }}</div>
                        <div class="max">/20</div>
                    </div>
                    <div class="col score-summary-col border-end">
                        <div class="label">3.2 นำเสนอ</div>
                        <div class="value text-warning">{{ number_format($summary['p3b'], 1) }}</div>
                        <div class="max">/20</div>
                    </div>
                    <div class="col score-summary-col border-end">
                        <div class="label">3.3 ประยุกต์</div>
                        <div class="value text-warning">{{ number_format($summary['p3c'], 1) }}</div>
                        <div class="max">/20</div>
                    </div>
                    <div class="col score-summary-col final-col">
                        <div class="label">คะแนนรวม</div>
                        <div class="value" style="color:{{ $fsColor }};">
                            {{ number_format($fs, 1) }}
                        </div>
                        <div class="max">/100</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    @endforeach

    @if($students->isEmpty())
        <div class="section-card card">
            <div class="card-body text-center py-5">
                <i class="bi bi-clipboard-x display-4 text-muted d-block mb-2" style="opacity:.3;"></i>
                <h6 class="text-muted fw-semibold">ยังไม่มีการให้คะแนน</h6>
                <p class="text-muted small mb-0">รอให้อาจารย์และคณะกรรมการให้คะแนนโครงงาน</p>
            </div>
        </div>
    @endif

</div>
@endsection

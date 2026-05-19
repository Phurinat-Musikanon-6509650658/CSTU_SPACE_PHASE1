@extends('layouts.app')

@section('title', 'กลุ่มที่ ' . sprintf('%02d', $group->group_id) . ' | CSTU SPACE')

@push('styles')
<style>
/* ── Hero ─────────────────────────────── */
.group-hero {
    border-radius: 16px;
    border: none;
    background: linear-gradient(135deg, #ffffff 0%, #f4f6ff 100%);
    border-top: 5px solid #667eea;
    box-shadow: 0 4px 20px rgba(102,126,234,.12);
}
.group-avatar {
    width: 60px; height: 60px; border-radius: 16px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 6px 18px rgba(102,126,234,.4);
}
/* ── Info rows ────────────────────────── */
.info-row {
    display: flex; align-items: center; gap: 14px;
    padding: 13px 20px;
    border-bottom: 1px solid #f4f5f7;
    transition: background .12s;
}
.info-row:last-child { border-bottom: none; }
.info-row:hover { background: #fafbff; }
.info-icon {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: .88rem; flex-shrink: 0;
}
.info-label {
    font-size: .7rem; color: #9ca3af; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em; margin-bottom: 2px;
}
.info-value { font-size: .88rem; font-weight: 500; color: #1f2937; line-height: 1.4; }
/* ── Committee rows ───────────────────── */
.committee-row {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 20px; border-bottom: 1px solid #f4f5f7;
    transition: background .12s;
}
.committee-row:last-child { border-bottom: none; }
.committee-row:hover { background: #fafbff; }
.person-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; font-weight: 700; color: #fff; flex-shrink: 0;
}
/* ── Member rows ──────────────────────── */
.member-row {
    display: flex; align-items: center; gap: 14px;
    padding: 14px 20px; border-bottom: 1px solid #f4f5f7;
    transition: background .12s;
}
.member-row:last-child { border-bottom: none; }
.member-row:hover { background: #fafbff; }
.member-avatar {
    width: 42px; height: 42px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .9rem; font-weight: 700; color: #fff; flex-shrink: 0;
}
/* ── Card headers ─────────────────────── */
.section-card { border: none; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,.07); overflow: hidden; }
.section-card .card-header {
    background: #fff; border-bottom: 1px solid #f0f1f5;
    padding: 14px 20px; font-size: .88rem;
}
/* ── Edit form ────────────────────────── */
.form-section { font-size: .7rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: #9ca3af; margin-bottom: .6rem;
    padding-bottom: .4rem; border-bottom: 1px solid #f0f1f5; }
.edit-toggle { cursor: pointer; user-select: none; }
.edit-toggle:hover { background: #fafbff !important; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- Back -->
    <a href="{{ route('coordinator.groups.index') }}" class="btn btn-link text-decoration-none ps-0 text-muted mb-3 d-inline-flex align-items-center">
        <i class="bi bi-chevron-left me-1"></i><span class="small">กลับรายการกลุ่ม</span>
    </a>

    @php
        $gsBadge = [
            'not_created'  => ['bg-secondary-subtle text-secondary border-secondary-subtle', 'circle',       'ยังไม่สร้าง'],
            'created'      => ['bg-success-subtle  text-success  border-success-subtle',     'check-circle', 'สร้างแล้ว'],
            'member_left'  => ['bg-warning-subtle  text-warning  border-warning-subtle',     'person-dash',  'สมาชิกออก'],
            'member_added' => ['bg-info-subtle     text-info     border-info-subtle',        'person-plus',  'เพิ่มสมาชิก'],
            'disbanded'    => ['bg-danger-subtle   text-danger   border-danger-subtle',      'x-circle',     'ยุบกลุ่ม'],
        ][$group->status_group] ?? ['bg-secondary-subtle text-secondary border-secondary-subtle', 'circle', $group->status_group];

        $psBadge = [
            'not_proposed'    => ['bg-secondary-subtle text-secondary border-secondary-subtle', 'circle',               'ยังไม่เสนอ'],
            'pending'         => ['bg-warning-subtle   text-warning  border-warning-subtle',    'clock',                'รออนุมัติ'],
            'approved'        => ['bg-info-subtle      text-info     border-info-subtle',        'check-circle',         'อนุมัติแล้ว'],
            'rejected'        => ['bg-danger-subtle    text-danger   border-danger-subtle',      'x-circle',             'ถูกปฏิเสธ'],
            'in_progress'     => ['bg-primary-subtle   text-primary  border-primary-subtle',    'gear-fill',            'กำลังดำเนินการ'],
            'submitted'       => ['bg-info-subtle      text-info     border-info-subtle',        'check-circle-fill',    'ส่งงานแล้ว'],
            'late_submission' => ['bg-warning-subtle   text-warning  border-warning-subtle',    'exclamation-triangle', 'ส่งงานล่าช้า'],
            'passed'          => ['bg-success-subtle   text-success  border-success-subtle',    'trophy-fill',          'ผ่าน'],
            'failed'          => ['bg-danger-subtle    text-danger   border-danger-subtle',      'x-octagon-fill',       'ไม่ผ่าน'],
        ][$group->project->status_project ?? ''] ?? ['bg-secondary-subtle text-secondary border-secondary-subtle', 'circle', '—'];

        $getLec  = fn($code) => $code ? ($lecturers->firstWhere('user_code', $code) ?? null) : null;
        $advLec  = $getLec($group->project->advisor_code    ?? null);
        $c1Lec   = $getLec($group->project->committee1_code ?? null);
        $c2Lec   = $getLec($group->project->committee2_code ?? null);
        $c3Lec   = $getLec($group->project->committee3_code ?? null);
    @endphp

    <!-- Hero Card -->
    <div class="group-hero card mb-4">
        <div class="card-body px-4 py-4">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="group-avatar">
                    <span class="text-white fw-bold" style="font-size:1.35rem;">{{ sprintf('%02d', $group->group_id) }}</span>
                </div>
                <div class="flex-fill" style="min-width:0;">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h1 class="h5 fw-bold mb-0 text-dark">กลุ่มที่ {{ sprintf('%02d', $group->group_id) }}</h1>
                        <span class="badge {{ $gsBadge[0] }} border" style="font-size:.7rem;">
                            <i class="bi bi-{{ $gsBadge[1] }} me-1"></i>{{ $gsBadge[2] }}
                        </span>
                        @if($group->project)
                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:.7rem;">
                            <i class="bi bi-folder-check me-1"></i>มีโครงงาน
                        </span>
                        @endif
                    </div>
                    @if($group->project)
                    <div class="mb-2">
                        <code class="text-primary fw-bold" style="font-size:.82rem;">{{ $group->project->project_code }}</code>
                        <span class="text-muted ms-2 small">{{ $group->project->project_name ?? '' }}</span>
                    </div>
                    @else
                    <div class="text-muted small mb-2">ยังไม่มีโครงงาน</div>
                    @endif
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:.72rem;">{{ $group->subject_code }}</span>
                        <span class="text-muted small">ปีการศึกษา {{ $group->year }} ภาค {{ $group->semester }}</span>
                        <span class="text-muted">·</span>
                        <span class="text-muted small"><i class="bi bi-people me-1"></i>{{ $group->members->count() }} สมาชิก</span>
                        <span class="text-muted">·</span>
                        <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i>{{ thaiDate($group->created_at) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($group->project)
    <!-- Project Info + Committee -->
    <div class="row g-4 mb-4">

        <!-- Project Details -->
        <div class="col-lg-7">
            <div class="section-card card h-100">
                <div class="card-header d-flex align-items-center gap-2">
                    <div class="info-icon bg-primary-subtle text-primary" style="width:28px;height:28px;border-radius:7px;font-size:.75rem;">
                        <i class="bi bi-folder2-open"></i>
                    </div>
                    <span class="fw-semibold">ข้อมูลโครงงาน</span>
                </div>
                <div class="card-body p-0">

                    <div class="info-row">
                        <div class="info-icon bg-primary-subtle text-primary"><i class="bi bi-code-square"></i></div>
                        <div>
                            <div class="info-label">รหัสโครงงาน</div>
                            <div class="info-value"><code class="text-primary fw-bold">{{ $group->project->project_code }}</code></div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon bg-info-subtle text-info"><i class="bi bi-card-text"></i></div>
                        <div style="min-width:0; flex:1;">
                            <div class="info-label">ชื่อโครงงาน</div>
                            <div class="info-value">{{ $group->project->project_name ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon bg-success-subtle text-success"><i class="bi bi-flag-fill"></i></div>
                        <div>
                            <div class="info-label">สถานะโครงงาน</div>
                            <div class="info-value">
                                <span class="badge {{ $psBadge[0] }} border" style="font-size:.73rem;">
                                    <i class="bi bi-{{ $psBadge[1] }} me-1"></i>{{ $psBadge[2] }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon bg-warning-subtle text-warning"><i class="bi bi-person-badge"></i></div>
                        <div>
                            <div class="info-label">ประเภทนักศึกษา</div>
                            <div class="info-value">
                                <span class="badge {{ $group->project->student_type == 'r' ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }}" style="font-size:.73rem;">
                                    {{ $group->project->student_type == 'r' ? 'ภาคปกติ (Regular)' : 'ภาคพิเศษ (Special)' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon bg-secondary-subtle text-secondary"><i class="bi bi-tag-fill"></i></div>
                        <div>
                            <div class="info-label">ประเภทโครงงาน</div>
                            <div class="info-value">{{ $group->project->project_type ?: '—' }}</div>
                        </div>
                    </div>

                    @if($group->project->parentProject)
                    <div class="info-row">
                        <div class="info-icon bg-purple-subtle" style="background:#f3e8ff;color:#7c3aed;"><i class="bi bi-diagram-2-fill"></i></div>
                        <div>
                            <div class="info-label">ต่อยอดจากโครงงาน (CS303)</div>
                            <div class="info-value">
                                <code class="text-primary fw-bold" style="font-size:.8rem;">{{ $group->project->parentProject->project_code }}</code>
                                <div class="text-muted small">{{ $group->project->parentProject->project_name }}</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="info-row">
                        <div class="info-icon {{ $group->project->exam_datetime ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                            <i class="bi bi-{{ $group->project->exam_datetime ? 'calendar-check-fill' : 'calendar-x-fill' }}"></i>
                        </div>
                        <div>
                            <div class="info-label">วันเวลาสอบ</div>
                            <div class="info-value">
                                @if($group->project->exam_datetime)
                                    <span class="text-success fw-semibold">
                                        {{ thaiDate($group->project->exam_datetime) }}
                                    </span>
                                    <span class="text-muted small ms-1">{{ \Carbon\Carbon::parse($group->project->exam_datetime)->format('H:i น.') }}</span>
                                @else
                                    <span class="text-danger small">ยังไม่กำหนด</span>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Committee -->
        <div class="col-lg-5">
            <div class="section-card card h-100">
                <div class="card-header d-flex align-items-center gap-2">
                    <div class="info-icon bg-success-subtle text-success" style="width:28px;height:28px;border-radius:7px;font-size:.75rem;">
                        <i class="bi bi-person-workspace"></i>
                    </div>
                    <span class="fw-semibold">คณะกรรมการ</span>
                </div>
                <div class="card-body p-0">
                    @php
                        $roles = [
                            ['ที่ปรึกษา', 'linear-gradient(135deg,#4e73df,#2e59d9)', $group->project->advisor_code    ?? null, $advLec],
                            ['กรรมการ 1', 'linear-gradient(135deg,#1cc88a,#13855c)', $group->project->committee1_code ?? null, $c1Lec],
                            ['กรรมการ 2', 'linear-gradient(135deg,#36b9cc,#1a7486)', $group->project->committee2_code ?? null, $c2Lec],
                            ['กรรมการ 3', 'linear-gradient(135deg,#858796,#5a5c69)', $group->project->committee3_code ?? null, $c3Lec],
                        ];
                    @endphp
                    @foreach($roles as [$role, $gradient, $code, $lec])
                    <div class="committee-row">
                        <div class="person-avatar" style="background:{{ $gradient }};">
                            {{ $lec ? strtoupper(mb_substr($lec->firstname_user ?? '?', 0, 1)) : ($code ? strtoupper(substr($code, 0, 1)) : '—') }}
                        </div>
                        <div class="flex-fill" style="min-width:0;">
                            <div class="info-label mb-1">{{ $role }}</div>
                            @if($code)
                                <div class="fw-semibold small text-dark">
                                    {{ $lec ? $lec->firstname_user . ' ' . $lec->lastname_user : $code }}
                                </div>
                                <code class="text-muted" style="font-size:.7rem;">{{ $code }}</code>
                            @else
                                <span class="text-muted small fst-italic">ยังไม่กำหนด</span>
                            @endif
                        </div>
                        @if($code)
                        <i class="bi bi-check-circle-fill text-success" style="font-size:.8rem; opacity:.7;"></i>
                        @else
                        <i class="bi bi-circle text-muted" style="font-size:.8rem; opacity:.4;"></i>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    @else
    <div class="section-card card mb-4">
        <div class="card-body text-center py-5">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning-subtle mb-3" style="width:64px;height:64px;">
                <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size:1.6rem;"></i>
            </div>
            <h6 class="fw-semibold text-dark mb-1">ยังไม่มีโครงงาน</h6>
            <p class="text-muted small mb-0">กลุ่มนี้ยังไม่ได้สร้างโครงงาน</p>
        </div>
    </div>
    @endif

    <!-- Members -->
    <div class="section-card card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <div class="info-icon bg-info-subtle text-info" style="width:28px;height:28px;border-radius:7px;font-size:.75rem;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <span class="fw-semibold">สมาชิกกลุ่ม</span>
            </div>
            <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size:.72rem;">
                {{ $group->members->count() }} คน
            </span>
        </div>
        <div class="card-body p-0">
            @php $memberColors = ['linear-gradient(135deg,#667eea,#764ba2)','linear-gradient(135deg,#1cc88a,#13855c)','linear-gradient(135deg,#f6c23e,#d4a017)','linear-gradient(135deg,#e74a3b,#be2617)']; @endphp
            @forelse($group->members as $i => $member)
            <div class="member-row">
                <div class="member-avatar" style="background:{{ $memberColors[$i % 4] }};">
                    {{ strtoupper(mb_substr($member->student->firstname_std ?? 'N', 0, 1)) }}
                </div>
                <div class="flex-fill" style="min-width:0;">
                    <div class="fw-semibold small text-dark">{{ ($member->student->firstname_std ?? 'N/A') . ' ' . ($member->student->lastname_std ?? '') }}</div>
                    <code class="text-muted" style="font-size:.72rem;">{{ $member->username_std }}</code>
                </div>
                <div class="text-end" style="min-width:140px;">
                    <div class="text-muted small text-truncate">{{ $member->student->email_std ?? '—' }}</div>
                    <div class="text-muted" style="font-size:.72rem;">
                        <i class="bi bi-calendar3 me-1"></i>{{ $member->created_at ? thaiDate($member->created_at) : '—' }}
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary-subtle mb-2" style="width:50px;height:50px;">
                    <i class="bi bi-person-x text-muted" style="font-size:1.2rem;"></i>
                </div>
                <p class="text-muted small mb-0">ไม่มีสมาชิก</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Edit / Create Form -->
    @if($group->project && Auth::guard('web')->user()->canEdit())
    <div class="section-card card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center edit-toggle"
             data-bs-toggle="collapse" data-bs-target="#editFormBody">
            <div class="d-flex align-items-center gap-2">
                <div class="info-icon bg-success-subtle text-success" style="width:28px;height:28px;border-radius:7px;font-size:.75rem;">
                    <i class="bi bi-pencil-square"></i>
                </div>
                <span class="fw-semibold text-success">แก้ไขข้อมูลโครงงาน</span>
            </div>
            <i class="bi bi-chevron-down text-muted small" id="editChevron"></i>
        </div>
        <div class="collapse" id="editFormBody">
            <div class="card-body px-4 py-4">
                <form action="{{ route('coordinator.projects.update', $group->project->project_id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Section: ข้อมูลโครงงาน -->
                    <div class="form-section">ข้อมูลโครงงาน</div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">ชื่อโครงงาน</label>
                            <input type="text" name="project_name" class="form-control form-control-sm"
                                   value="{{ $group->project->project_name }}" placeholder="ระบุชื่อโครงงาน">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">สถานะโครงงาน</label>
                            <select name="status_project" class="form-select form-select-sm">
                                @foreach(['not_proposed'=>'ยังไม่เสนอ','pending'=>'รออนุมัติ','approved'=>'อนุมัติแล้ว','rejected'=>'ถูกปฏิเสธ','in_progress'=>'กำลังดำเนินการ','submitted'=>'ส่งงานแล้ว','late_submission'=>'ส่งงานล่าช้า','passed'=>'ผ่าน','failed'=>'ไม่ผ่าน'] as $v => $l)
                                    <option value="{{ $v }}" {{ $group->project->status_project == $v ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">ประเภทโครงงาน</label>
                            <input type="text" name="project_type" class="form-control form-control-sm"
                                   value="{{ $group->project->project_type }}" placeholder="เช่น soft-en, ai, network">
                            <div class="form-text">คั่นหลายประเภทด้วย comma</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">รหัสโครงงาน</label>
                            <input type="text" class="form-control form-control-sm bg-light" value="{{ $group->project->project_code }}" readonly>
                            <div class="form-text">ไม่สามารถแก้ไขได้</div>
                        </div>
                        @if($group->subject_code === 'CS403' && $parentProjects->isNotEmpty())
                        <div class="col-12">
                            <label class="form-label fw-semibold small">ต่อยอดจากโครงงาน CS303 <span class="text-muted fw-normal">(ถ้ามี)</span></label>
                            <select name="parent_project_id" class="form-select form-select-sm">
                                <option value="">— ไม่มี / เริ่มใหม่ —</option>
                                @foreach($parentProjects as $pp)
                                    <option value="{{ $pp->project_id }}" {{ $group->project->parent_project_id == $pp->project_id ? 'selected' : '' }}>
                                        {{ $pp->project_code }} – {{ $pp->project_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>

                    <!-- Section: ตารางสอบและคณะกรรมการ -->
                    <div class="form-section">ตารางสอบและคณะกรรมการ</div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">อาจารย์ที่ปรึกษา</label>
                            <select name="advisor_code" class="form-select form-select-sm">
                                <option value="">— ไม่มี —</option>
                                @foreach($lecturers as $lec)
                                    <option value="{{ $lec->user_code }}" {{ $group->project->advisor_code == $lec->user_code ? 'selected' : '' }}>
                                        {{ $lec->user_code }} – {{ $lec->firstname_user }} {{ $lec->lastname_user }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">วันเวลาสอบ</label>
                            <input type="datetime-local" name="exam_datetime" class="form-control form-control-sm"
                                   value="{{ $group->project->exam_datetime ? \Carbon\Carbon::parse($group->project->exam_datetime)->format('Y-m-d\TH:i') : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">กรรมการ 1</label>
                            <select name="committee1_code" class="form-select form-select-sm">
                                <option value="">— ไม่มี —</option>
                                @foreach($lecturers as $lec)
                                    <option value="{{ $lec->user_code }}" {{ $group->project->committee1_code == $lec->user_code ? 'selected' : '' }}>
                                        {{ $lec->user_code }} – {{ $lec->firstname_user }} {{ $lec->lastname_user }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">กรรมการ 2</label>
                            <select name="committee2_code" class="form-select form-select-sm">
                                <option value="">— ไม่มี —</option>
                                @foreach($lecturers as $lec)
                                    <option value="{{ $lec->user_code }}" {{ $group->project->committee2_code == $lec->user_code ? 'selected' : '' }}>
                                        {{ $lec->user_code }} – {{ $lec->firstname_user }} {{ $lec->lastname_user }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">กรรมการ 3</label>
                            <select name="committee3_code" class="form-select form-select-sm">
                                <option value="">— ไม่มี —</option>
                                @foreach($lecturers as $lec)
                                    <option value="{{ $lec->user_code }}" {{ $group->project->committee3_code == $lec->user_code ? 'selected' : '' }}>
                                        {{ $lec->user_code }} – {{ $lec->firstname_user }} {{ $lec->lastname_user }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="pt-2 border-top d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            <i class="bi bi-save me-1"></i>บันทึกการเปลี่ยนแปลง
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @elseif(!$group->project && Auth::guard('web')->user()->canEdit())
    <div class="section-card card mb-4">
        <div class="card-header d-flex align-items-center gap-2">
            <div class="info-icon bg-warning-subtle text-warning" style="width:28px;height:28px;border-radius:7px;font-size:.75rem;">
                <i class="bi bi-plus-circle"></i>
            </div>
            <span class="fw-semibold text-warning">สร้างโครงงานใหม่</span>
        </div>
        <div class="card-body px-4 py-4">
            <form action="{{ route('coordinator.groups.approve', $group->group_id) }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold small">ชื่อโครงงาน <span class="text-danger">*</span></label>
                        <input type="text" name="project_name" class="form-control form-control-sm" required placeholder="ระบุชื่อโครงงาน">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">อาจารย์ที่ปรึกษา <span class="text-danger">*</span></label>
                        <select name="advisor_code" class="form-select form-select-sm" required>
                            <option value="">— เลือกอาจารย์ —</option>
                            @foreach($lecturers as $lec)
                                <option value="{{ $lec->user_code }}">{{ $lec->user_code }} – {{ $lec->firstname_user }} {{ $lec->lastname_user }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">ประเภทนักศึกษา <span class="text-danger">*</span></label>
                        <select name="student_type" class="form-select form-select-sm" required>
                            <option value="r">ภาคปกติ</option>
                            <option value="s">ภาคพิเศษ</option>
                        </select>
                    </div>
                    @if($group->subject_code === 'CS403' && $parentProjects->isNotEmpty())
                    <div class="col-12">
                        <label class="form-label fw-semibold small">ต่อยอดจากโครงงาน CS303 <span class="text-muted fw-normal">(ถ้ามี)</span></label>
                        <select name="parent_project_id" class="form-select form-select-sm">
                            <option value="">— ไม่มี / เริ่มใหม่ —</option>
                            @foreach($parentProjects as $pp)
                                <option value="{{ $pp->project_id }}">
                                    {{ $pp->project_code }} – {{ $pp->project_name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">เลือกถ้านักศึกษากลุ่มนี้ต่อยอดโครงงานจาก CS303</div>
                    </div>
                    @endif
                </div>
                <div class="mt-4 pt-2 border-top d-flex justify-content-end">
                    <button type="submit" class="btn btn-success btn-sm px-4">
                        <i class="bi bi-check-circle me-1"></i>สร้างโครงงาน
                    </button>
                </div>
            </form>
        </div>
    </div>

    @elseif(!$group->project)
    <div class="section-card card mb-4">
        <div class="card-body text-center py-4">
            <i class="bi bi-lock text-muted d-block mb-2" style="font-size:1.5rem; opacity:.3;"></i>
            <p class="text-muted small mb-0">ไม่มีสิทธิ์สร้างโครงงาน</p>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chevron toggle animation
    const target = document.getElementById('editFormBody');
    const chevron = document.getElementById('editChevron');
    if (target && chevron) {
        target.addEventListener('show.bs.collapse', () => chevron.style.transform = 'rotate(180deg)');
        target.addEventListener('hide.bs.collapse', () => chevron.style.transform = 'rotate(0deg)');
        chevron.style.transition = 'transform .2s';
    }

    // Committee duplicate validation
    const selectors = ['advisor_code','committee1_code','committee2_code','committee3_code']
        .map(n => document.querySelector(`select[name="${n}"]`));
    const [adv] = selectors;
    if (!adv) return;

    function validate() {
        const vals = selectors.map(s => s.value);
        const warnings = [];
        const names = ['ที่ปรึกษา','กรรมการ 1','กรรมการ 2','กรรมการ 3'];
        for (let i = 1; i < vals.length; i++) {
            for (let j = 0; j < i; j++) {
                if (vals[i] && vals[i] === vals[j])
                    warnings.push(`${names[i]} ซ้ำกับ ${names[j]}`);
            }
        }
        document.querySelector('.lecturer-validation-alert')?.remove();
        if (warnings.length > 0) {
            const el = document.createElement('div');
            el.className = 'alert alert-warning alert-dismissible fade show lecturer-validation-alert mb-3 border-0 shadow-sm';
            el.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-2"></i><strong>พบการเลือกซ้ำ:</strong>
                <ul class="mb-0 mt-1 small">${warnings.map(w => `<li>${w}</li>`).join('')}</ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            adv.closest('form').insertBefore(el, adv.closest('form').firstChild);
        }
    }

    selectors.forEach(s => s?.addEventListener('change', validate));
    adv.closest('form')?.addEventListener('submit', function(e) {
        const vals = selectors.map(s => s.value).filter(v => v !== '');
        if (vals.length !== new Set(vals).size) {
            e.preventDefault();
            alert('พบการเลือกอาจารย์ซ้ำกัน\nกรุณาเลือกอาจารย์ที่แตกต่างกันสำหรับแต่ละตำแหน่ง');
        }
    });
});
</script>
@endpush

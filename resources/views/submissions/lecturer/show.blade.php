@extends('layouts.app')

@section('title', 'รายละเอียดเล่มโครงงาน')

@push('styles')
<style>
    .btn-back {
        margin-bottom: 2rem;
    }

    .detail-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .detail-card-header {
        padding: 2rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .detail-card-header h4 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
    }

    .detail-card-body {
        padding: 2rem;
    }

    .detail-section {
        margin-bottom: 2rem;
    }

    .detail-section:last-child {
        margin-bottom: 0;
    }

    .detail-section h6 {
        font-size: 0.9rem;
        font-weight: 700;
        color: #667eea;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .detail-section p {
        margin: 0;
        color: #2c3e50;
        font-size: 0.95rem;
        line-height: 1.6;
    }

    .detail-section code {
        background-color: #e9ecef;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.9rem;
    }

    .detail-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    @media (max-width: 768px) {
        .detail-row {
            grid-template-columns: 1fr;
        }
    }

    .divider {
        height: 2px;
        background: linear-gradient(to right, #e9ecef, transparent);
        margin: 2rem 0;
    }

    .btn-download {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        transition: all 0.2s;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-download:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        color: white;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="btn-back">
        <a href="{{ route('lecturer.submissions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> กลับ
        </a>
    </div>

    @php $lateInfo = $project->getLateSubmissionInfo(); @endphp
    @if($lateInfo)
    <div class="alert border-0 mb-3 d-flex align-items-start gap-3"
         style="background:#fff3cd;border-left:5px solid #f59e0b !important;border-radius:10px;border:1px solid #fde68a;">
        <i class="bi bi-exclamation-triangle-fill text-warning mt-1" style="font-size:1.3rem;flex-shrink:0;"></i>
        <div>
            <div class="fw-bold" style="color:#92400e;">ส่งงานล่าช้า {{ $lateInfo['days'] }} วัน</div>
            <div class="small" style="color:#78350f;">
                กำหนดส่ง (ก่อนวันสอบ 1 วัน): <strong>{{ thaiDateTime($lateInfo['deadline']) }}</strong> &nbsp;|&nbsp;
                ส่งจริง: <strong>{{ thaiDateTime($project->submitted_at) }}</strong>
            </div>
            <div class="mt-1 small" style="color:#92400e;">
                <i class="bi bi-calculator me-1"></i>
                ควรหักคะแนนส่วนโครงงาน <strong>{{ $lateInfo['days'] }} × 20% = {{ $lateInfo['penalty_pct'] }}%</strong> จากคะแนนที่ได้รับ
            </div>
        </div>
    </div>
    @endif

    <div class="detail-card">
        <div class="detail-card-header">
            <h4>
                <i class="bi bi-file-pdf-fill"></i> {{ $project->project_name }}
            </h4>
        </div>
        <div class="detail-card-body">
            <div class="detail-row">
                <div class="detail-section">
                    <h6>รหัสโครงงาน</h6>
                    <p><code>{{ $project->project_code }}</code></p>
                </div>
                <div class="detail-section">
                    <h6>ปีการศึกษา / เทอม</h6>
                    <p>{{ $project->group->year }} / เทอม {{ $project->group->semester }}</p>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-section">
                    <h6>สถานะโครงงาน</h6>
                    <p>
                        @php
                            $sl = ['not_proposed'=>['secondary','ยังไม่เสนอ'],'pending'=>['warning','รออนุมัติ'],'approved'=>['info','อนุมัติแล้ว'],'rejected'=>['danger','ถูกปฏิเสธ'],'in_progress'=>['primary','กำลังดำเนินการ'],'submitted'=>['success','ส่งงานแล้ว'],'late_submission'=>['warning','ส่งงานล่าช้า'],'passed'=>['success','ผ่าน'],'failed'=>['danger','ไม่ผ่าน']];
                            [$sc,$st] = $sl[$project->status_project] ?? ['secondary',$project->status_project];
                        @endphp
                        <span class="badge bg-{{ $sc }}">{{ $st }}</span>
                    </p>
                </div>
            </div>

            <div class="divider"></div>

            <div class="detail-section">
                <h6>ไฟล์เล่มโครงงาน</h6>
                <p>
                    <strong>{{ $project->submission_original_name ?? 'submission.pdf' }}</strong>
                    <br>
                    <small class="text-muted">ส่งเมื่อ {{ thaiDateTimeSec($project->submitted_at) }}</small>
                </p>
            </div>

            <div style="margin-top: 2rem;">
                <a href="{{ route('lecturer.submissions.download', $project->project_id) }}"
                   class="btn-download" download>
                    <i class="bi bi-download"></i> ดาวน์โหลดเล่มโครงงาน
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

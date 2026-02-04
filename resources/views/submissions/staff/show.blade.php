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
        <a href="{{ route('staff.submissions.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> กลับ
        </a>
    </div>

    <div class="detail-card">
        <div class="detail-card-header">
            <h4>
                <i class="fas fa-file-pdf"></i> {{ $project->project_name }}
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
                    <h6>อาจารย์ที่ปรึกษา</h6>
                    <p>
                        @if($project->advisor)
                            {{ $project->advisor->first_name }} {{ $project->advisor->last_name }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </p>
                </div>
                <div class="detail-section">
                    <h6>สถานะโครงงาน</h6>
                    <p>
                        @switch($project->status_project)
                            @case('pending')
                                <span class="badge bg-warning">รออนุมัติ</span>
                                @break
                            @case('approved')
                                <span class="badge bg-success">อนุมัติ</span>
                                @break
                            @case('rejected')
                                <span class="badge bg-danger">ปฏิเสธ</span>
                                @break
                            @default
                                <span class="badge bg-secondary">{{ $project->status_project }}</span>
                        @endswitch
                    </p>
                </div>
            </div>

            <div class="divider"></div>

            <div class="detail-section">
                <h6>คณะกรรมการประเมิน</h6>
                <div class="detail-row">
                    <div>
                        <h6 style="font-size: 0.85rem; margin-top: 0.75rem;">กรรมการคนที่ 1</h6>
                        <p>
                            @if($project->committee1)
                                {{ $project->committee1->first_name }} {{ $project->committee1->last_name }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <h6 style="font-size: 0.85rem; margin-top: 0.75rem;">กรรมการคนที่ 2</h6>
                        <p>
                            @if($project->committee2)
                                {{ $project->committee2->first_name }} {{ $project->committee2->last_name }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="detail-row" style="margin-top: 1rem;">
                    <div>
                        <h6 style="font-size: 0.85rem; margin-top: 0.75rem;">กรรมการคนที่ 3</h6>
                        <p>
                            @if($project->committee3)
                                {{ $project->committee3->first_name }} {{ $project->committee3->last_name }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            <div class="detail-section">
                <h6>ไฟล์เล่มโครงงาน</h6>
                <p>
                    <strong>{{ $project->submission_original_name ?? 'submission.pdf' }}</strong>
                    <br>
                    <small class="text-muted">ส่งเมื่อ {{ $project->submitted_at->format('d/m/Y H:i:s') }}</small>
                </p>
            </div>

            <div style="margin-top: 2rem;">
                <a href="{{ route('staff.submissions.download', $project->project_id) }}" 
                   class="btn-download" download>
                    <i class="fas fa-download"></i> ดาวน์โหลดเล่มโครงงาน
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

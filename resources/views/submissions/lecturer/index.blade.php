@extends('layouts.app')

@section('title', 'เล่มโครงงานที่ส่ง')

@push('styles')
<style>
    .page-header {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .page-header h2 {
        color: #2c3e50;
        font-weight: 700;
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .section-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .section-title {
        color: #2c3e50;
        font-weight: 700;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e9ecef;
    }

    .semester-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 20px;
        font-weight: 600;
        margin-bottom: 1rem;
        font-size: 0.9rem;
    }

    .table-modern {
        margin-bottom: 0;
    }

    .table-modern thead th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        font-weight: 600;
        color: #2c3e50;
        border: none;
        padding: 1rem 0.75rem;
        white-space: nowrap;
        font-size: 0.9rem;
    }

    .table-modern tbody tr {
        transition: background-color 0.2s;
        border-bottom: 1px solid #f0f0f0;
    }

    .table-modern tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }

    .table-modern td {
        vertical-align: middle;
        padding: 1rem 0.75rem;
    }

    .table-modern code {
        background-color: #e9ecef;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.85rem;
    }

    .btn-action {
        padding: 0.4rem 0.6rem;
        font-size: 0.85rem;
        border-radius: 6px;
        transition: all 0.2s;
        margin: 0 2px;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .empty-state i {
        font-size: 3rem;
        color: #cbd5e0;
        margin-bottom: 1rem;
    }

    .empty-state p {
        color: #718096;
        font-size: 1.05rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <h2>
            <i class="fas fa-file-pdf" style="color: #667eea;"></i> เล่มโครงงานที่ส่ง
        </h2>
        <p class="text-muted mb-0"><i class="fas fa-box"></i> ทั้งหมด <strong>{{ $totalSubmissions }}</strong> เล่ม</p>
    </div>

    @if($grouped->isEmpty())
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>ยังไม่มีเล่มโครงงานที่ได้รับการส่ง</p>
        </div>
    @else
        @foreach($grouped as $year => $semesters)
            <div class="section-card">
                <h3 class="section-title">
                    <i class="fas fa-calendar-alt"></i> ปีการศึกษา {{ $year }}
                </h3>

                @foreach($semesters as $semester => $projects)
                    <div style="margin-left: 1rem; margin-bottom: 2rem;">
                        <div class="semester-badge">
                            <i class="fas fa-graduation-cap"></i> เทอม {{ $semester }}
                        </div>

                        <div class="table-responsive">
                            <table class="table table-modern table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="15%">รหัสโครงงาน</th>
                                        <th width="35%">ชื่อโครงงาน</th>
                                        <th width="15%">วันที่ส่ง</th>
                                        <th width="35%" class="text-center">การดำเนิน</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($projects as $project)
                                        <tr>
                                            <td>
                                                <code>{{ $project->project_code }}</code>
                                            </td>
                                            <td>
                                                <strong>{{ $project->project_name }}</strong>
                                            </td>
                                            <td>
                                                @if($project->submitted_at)
                                                    <small>{{ $project->submitted_at->format('d/m/Y H:i') }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('lecturer.submissions.show', $project->project_id) }}" 
                                                   class="btn btn-sm btn-outline-primary btn-action" title="ดูรายละเอียด">
                                                    <i class="fas fa-eye"></i> ดู
                                                </a>
                                                <a href="{{ route('lecturer.submissions.download', $project->project_id) }}" 
                                                   class="btn btn-sm btn-outline-success btn-action" title="ดาวน์โหลด">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    @endif
</div>
@endsection

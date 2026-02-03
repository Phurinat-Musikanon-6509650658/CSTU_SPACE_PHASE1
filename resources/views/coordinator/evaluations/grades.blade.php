@extends('layouts.app')

@section('title', 'เกรดและการยืนยัน')

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
    .grade-display {
        font-size: 5rem;
        font-weight: 700;
        line-height: 1;
    }
    .confirmation-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .timeline {
        position: relative;
        padding-left: 2rem;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }
    .timeline-item:before {
        content: '';
        position: absolute;
        left: -2rem;
        top: 0.5rem;
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        background: #dee2e6;
    }
    .timeline-item.confirmed:before {
        background: #28a745;
    }
    .timeline-item:after {
        content: '';
        position: absolute;
        left: calc(-2rem + 0.4rem);
        top: 1.5rem;
        width: 2px;
        height: calc(100% - 1rem);
        background: #dee2e6;
    }
    .timeline-item:last-child:after {
        display: none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="mb-4">
        <a href="{{ route('coordinator.evaluations.index') }}" class="btn btn-outline-primary mb-3">
            <i class="bi bi-arrow-left me-2"></i>กลับรายการ
        </a>
        <h1 class="h2 fw-bold">
            <i class="bi bi-award me-2 text-warning"></i>เกรดและการยืนยัน
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
                    <p class="mb-0"><strong>สมาชิก:</strong> 
                        @foreach($project->group->members as $member)
                            {{ $member->student->firstname_std ?? '' }} {{ $member->student->lastname_std ?? '' }}@if(!$loop->last), @endif
                        @endforeach
                    </p>
                </div>
                <div class="col-md-4 text-center">
                    <small class="text-muted d-block mb-2">จำนวนคะแนนที่ได้รับ</small>
                    <h4 class="mb-0">
                        <span class="badge bg-info" style="font-size: 1.2rem;">
                            {{ $project->evaluations->count() }}/{{ $project->advisor_code ? 1 : 0 }}{{ $project->committee1_code ? '+1' : '' }}{{ $project->committee2_code ? '+1' : '' }}{{ $project->committee3_code ? '+1' : '' }}
                        </span>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    @if($project->grade)
        <!-- Grade Display -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-white" style="background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);">
                        <h5 class="mb-0 text-white">
                            <i class="bi bi-trophy me-2"></i>เกรดสุดท้าย
                        </h5>
                    </div>
                    <div class="card-body text-center py-5">
                        @php
                            $gradeColors = [
                                'A' => 'success', 'B+' => 'info', 'B' => 'info',
                                'C+' => 'warning', 'C' => 'warning',
                                'D+' => 'danger', 'D' => 'danger', 'F' => 'danger'
                            ];
                            $color = $gradeColors[$project->grade->grade ?? ''] ?? 'secondary';
                        @endphp
                        
                        @if($project->grade->grade)
                            <div class="grade-display text-{{ $color }}">{{ $project->grade->grade }}</div>
                            <p class="text-muted mb-0 mt-3">คะแนนเฉลี่ย: <strong>{{ number_format($project->grade->final_score, 2) }}</strong> / 100</p>
                        @else
                            <div class="text-muted">
                                <i class="bi bi-hourglass-split" style="font-size: 3rem;"></i>
                                <p class="mt-3">กำลังรอคำนวณเกรด</p>
                            </div>
                        @endif

                        @if($project->grade->all_confirmed)
                            <div class="alert alert-success mt-4 mb-0">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <strong>ยืนยันครบถ้วนแล้ว</strong>
                                <br><small>{{ $project->grade->all_confirmed_at->format('d/m/Y H:i น.') }}</small>
                            </div>
                        @else
                            <div class="alert alert-warning mt-4 mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>รอการยืนยัน</strong>
                            </div>
                        @endif

                        @if($project->grade->grade_released)
                            <div class="alert alert-info mt-2 mb-0">
                                <i class="bi bi-send-check me-2"></i>
                                <strong>ส่งเกรดให้นักศึกษาแล้ว</strong>
                                <br><small>{{ $project->grade->grade_released_at->format('d/m/Y H:i น.') }}</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Confirmation Status -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-white" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
                        <h5 class="mb-0 text-white">
                            <i class="bi bi-check2-all me-2"></i>สถานะการยืนยัน
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @if($project->advisor_code)
                                <div class="timeline-item {{ $project->grade->advisor_confirmed ? 'confirmed' : '' }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1 fw-bold">
                                                <span class="badge bg-primary me-2">Advisor</span>
                                                {{ $project->advisor_code }}
                                            </h6>
                                            @if($project->advisor)
                                                <small class="text-muted">{{ $project->advisor->firstname_user }} {{ $project->advisor->lastname_user }}</small>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            @if($project->grade->advisor_confirmed)
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle-fill me-1"></i>ยืนยันแล้ว
                                                </span>
                                                <br><small class="text-muted">{{ $project->grade->advisor_confirmed_at->format('d/m/Y H:i') }}</small>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-clock me-1"></i>รอยืนยัน
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @foreach(['committee1', 'committee2', 'committee3'] as $role)
                                @if($project->{$role.'_code'})
                                    @php
                                        $confirmed = $project->grade->{$role.'_confirmed'};
                                        $confirmedAt = $project->grade->{$role.'_confirmed_at'};
                                    @endphp
                                    <div class="timeline-item {{ $confirmed ? 'confirmed' : '' }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1 fw-bold">
                                                    <span class="badge bg-success me-2">{{ ucfirst($role) }}</span>
                                                    {{ $project->{$role.'_code'} }}
                                                </h6>
                                                @if($project->{$role})
                                                    <small class="text-muted">{{ $project->{$role}->firstname_user }} {{ $project->{$role}->lastname_user }}</small>
                                                @endif
                                            </div>
                                            <div class="text-end">
                                                @if($confirmed)
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle-fill me-1"></i>ยืนยันแล้ว
                                                    </span>
                                                    <br><small class="text-muted">{{ $confirmedAt->format('d/m/Y H:i') }}</small>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="bi bi-clock me-1"></i>รอยืนยัน
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Score Breakdown -->
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-bar-chart-fill me-2"></i>รายละเอียดคะแนน
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead style="background-color: #000000;">
                            <tr>
                                <th class="text-white">ผู้ประเมิน</th>
                                <th class="text-white">ตำแหน่ง</th>
                                <th class="text-white text-center">รูปเล่ม (30)</th>
                                <th class="text-white text-center">พรีเซนต์ (70)</th>
                                <th class="text-white text-center">รวม (100)</th>
                                <th class="text-white text-center">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($project->evaluations as $evaluation)
                                <tr>
                                    <td>
                                        <strong>{{ $evaluation->evaluator_code }}</strong>
                                        @if($evaluation->evaluator)
                                            <br><small class="text-muted">{{ $evaluation->evaluator->firstname_user }} {{ $evaluation->evaluator->lastname_user }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $roleLabels = [
                                                'advisor' => 'Advisor',
                                                'committee1' => 'Committee 1',
                                                'committee2' => 'Committee 2',
                                                'committee3' => 'Committee 3'
                                            ];
                                            $roleColors = [
                                                'advisor' => 'primary',
                                                'committee1' => 'success',
                                                'committee2' => 'success',
                                                'committee3' => 'success'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $roleColors[$evaluation->evaluator_role] ?? 'secondary' }}">
                                            {{ $roleLabels[$evaluation->evaluator_role] ?? $evaluation->evaluator_role }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <strong class="text-info">{{ number_format($evaluation->document_score, 2) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <strong class="text-warning">{{ number_format($evaluation->presentation_score, 2) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <strong class="text-primary">{{ number_format($evaluation->total_score, 2) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $isConfirmed = false;
                                            if ($evaluation->evaluator_role === 'advisor' && $project->grade->advisor_confirmed) {
                                                $isConfirmed = true;
                                            } elseif (in_array($evaluation->evaluator_role, ['committee1', 'committee2', 'committee3']) && $project->grade->{$evaluation->evaluator_role.'_confirmed'}) {
                                                $isConfirmed = true;
                                            }
                                        @endphp
                                        @if($isConfirmed)
                                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                        @else
                                            <i class="bi bi-clock text-warning fs-5"></i>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">ยังไม่มีการให้คะแนน</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($project->evaluations->count() > 0)
                            <tfoot style="background-color: #f8f9fa;">
                                <tr>
                                    <td colspan="2" class="text-end"><strong>คะแนนเฉลี่ย:</strong></td>
                                    <td class="text-center">
                                        <strong class="text-info">{{ number_format($project->evaluations->avg('document_score'), 2) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <strong class="text-warning">{{ number_format($project->evaluations->avg('presentation_score'), 2) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <strong class="text-success">{{ number_format($project->evaluations->avg('total_score'), 2) }}</strong>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

    @else
        <!-- No Grade Yet -->
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-hourglass-split text-muted" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3">ยังไม่มีการคำนวณเกรด</h5>
                <p class="text-muted">รอให้อาจารย์และคณะกรรมการให้คะแนนก่อน</p>
            </div>
        </div>
    @endif
</div>
@endsection

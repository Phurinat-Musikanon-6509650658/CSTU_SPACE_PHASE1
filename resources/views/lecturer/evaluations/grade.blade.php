@extends('layouts.app')

@section('title', 'ยืนยันเกรด')

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
    .grade-display {
        font-size: 5rem;
        font-weight: 700;
        line-height: 1;
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
            <i class="bi bi-award me-2 text-warning"></i>เกรดโครงงาน
        </h1>
    </div>

    <!-- Project Info -->
    <div class="card mb-4">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
            <h5 class="mb-0 text-white">ข้อมูลโครงงาน</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-9">
                    <p class="mb-2"><strong>รหัสโครงงาน:</strong> <code class="text-primary fs-5">{{ $project->project_code }}</code></p>
                    <p class="mb-2"><strong>ชื่อโครงงาน:</strong> {{ $project->project_name ?? 'ยังไม่ระบุ' }}</p>
                    <p class="mb-0"><strong>สมาชิก:</strong> 
                        @foreach($project->group->members as $member)
                            {{ $member->student->firstname_std ?? '' }} {{ $member->student->lastname_std ?? '' }}@if(!$loop->last), @endif
                        @endforeach
                    </p>
                </div>
                <div class="col-md-3">
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
                        <h6 class="mb-0">
                            <span class="badge bg-{{ $roleColors[$role] ?? 'secondary' }}">
                                {{ $roleLabels[$role] ?? $role }}
                            </span>
                        </h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($project->grade)
        <div class="row">
            <!-- Grade Display -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-white" style="background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);">
                        <h5 class="mb-0 text-white">
                            <i class="bi bi-trophy me-2"></i>เกรดที่คำนวณได้
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
                        
                        <div class="grade-display text-{{ $color }}">{{ $project->grade->grade ?? '-' }}</div>
                        <p class="text-muted mb-0 mt-3">
                            คะแนนเฉลี่ย: <strong>{{ number_format($project->grade->final_score, 2) }}</strong> / 100
                        </p>

                        @php
                            $isConfirmed = false;
                            $confirmedAt = null;
                            
                            if ($role === 'advisor' && $project->grade->advisor_confirmed) {
                                $isConfirmed = true;
                                $confirmedAt = $project->grade->advisor_confirmed_at;
                            } elseif (in_array($role, ['committee1', 'committee2', 'committee3']) && $project->grade->{$role.'_confirmed'}) {
                                $isConfirmed = true;
                                $confirmedAt = $project->grade->{$role.'_confirmed_at'};
                            }
                        @endphp

                        @if($isConfirmed)
                            <div class="alert alert-success mt-4 mb-0">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <strong>คุณยืนยันเกรดแล้ว</strong>
                                <br><small>{{ $confirmedAt->format('d/m/Y H:i น.') }}</small>
                            </div>
                        @else
                            <form action="{{ route('lecturer.evaluations.confirm', $project->project_id) }}" method="POST" class="mt-4">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการยืนยันเกรดนี้?')">
                                    <i class="bi bi-check2-circle me-2"></i>ยืนยันเกรด
                                </button>
                                <p class="text-muted small mt-2 mb-0">กรุณาตรวจสอบเกรดก่อนยืนยัน</p>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Scores Table -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-bar-chart-fill me-2"></i>รายละเอียดคะแนน
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead style="background-color: #f8f9fa;">
                                    <tr>
                                        <th>ผู้ประเมิน</th>
                                        <th class="text-center">รูปเล่ม</th>
                                        <th class="text-center">พรีเซนต์</th>
                                        <th class="text-center">รวม</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($project->evaluations as $evaluation)
                                        <tr>
                                            <td>
                                                @php
                                                    $evalRole = $evaluation->evaluator_role;
                                                    $isMyEval = $evalRole === $role;
                                                @endphp
                                                {{ $roleLabels[$evalRole] ?? $evalRole }}
                                                @if($isMyEval)
                                                    <span class="badge bg-info text-white">คุณ</span>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ number_format($evaluation->document_score, 2) }}</td>
                                            <td class="text-center">{{ number_format($evaluation->presentation_score, 2) }}</td>
                                            <td class="text-center"><strong>{{ number_format($evaluation->total_score, 2) }}</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot style="background-color: #f8f9fa;">
                                    <tr>
                                        <td><strong>เฉลี่ย:</strong></td>
                                        <td class="text-center"><strong>{{ number_format($project->evaluations->avg('document_score'), 2) }}</strong></td>
                                        <td class="text-center"><strong>{{ number_format($project->evaluations->avg('presentation_score'), 2) }}</strong></td>
                                        <td class="text-center"><strong class="text-success">{{ number_format($project->evaluations->avg('total_score'), 2) }}</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        @if($project->evaluations->count() > 1)
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                <small>เกรดคำนวณจากคะแนนเฉลี่ยของทุกคน</small>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Confirmation Status -->
                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-people-fill me-2"></i>สถานะการยืนยัน
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($project->advisor_code)
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded {{ $project->grade->advisor_confirmed ? 'bg-success bg-opacity-10' : 'bg-light' }}">
                                <span><strong>Advisor:</strong> {{ $project->advisor_code }}</span>
                                @if($project->grade->advisor_confirmed)
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                @else
                                    <i class="bi bi-clock text-warning"></i>
                                @endif
                            </div>
                        @endif
                        
                        @foreach(['committee1', 'committee2', 'committee3'] as $commRole)
                            @if($project->{$commRole.'_code'})
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded {{ $project->grade->{$commRole.'_confirmed'} ? 'bg-success bg-opacity-10' : 'bg-light' }}">
                                    <span><strong>{{ ucfirst($commRole) }}:</strong> {{ $project->{$commRole.'_code'} }}</span>
                                    @if($project->grade->{$commRole.'_confirmed'})
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @else
                                        <i class="bi bi-clock text-warning"></i>
                                    @endif
                                </div>
                            @endif
                        @endforeach

                        @if($project->grade->all_confirmed)
                            <div class="alert alert-success mt-3 mb-0">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <strong>ยืนยันครบถ้วนแล้ว</strong>
                                <br><small>{{ $project->grade->all_confirmed_at->format('d/m/Y H:i น.') }}</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-hourglass-split text-muted" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3">ยังไม่มีการคำนวณเกรด</h5>
                <p class="text-muted">รอให้ทุกคนให้คะแนนครบก่อน</p>
            </div>
        </div>
    @endif
</div>
@endsection

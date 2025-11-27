@extends('layouts.app')

@section('title', 'คะแนนและผลการประเมิน - CSTU SPACE')

@push('styles')
<style>
    :root {
        --color-primary: #667eea;
        --color-success: #48bb78;
        --color-warning: #f6ad55;
        --color-danger: #f56565;
        --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --gradient-success: linear-gradient(135deg, #48bb78, #38a169);
        --gradient-warning: linear-gradient(135deg, #f6ad55, #ed8936);
        --gradient-gold: linear-gradient(135deg, #ffd700, #ffed4e);
        --shadow-light: 0 8px 25px rgba(0, 0, 0, 0.1);
        --shadow-medium: 0 15px 35px rgba(0, 0, 0, 0.15);
        --border-radius: 20px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
        min-height: 100vh;
    }

    .page-header {
        background: white;
        border-radius: var(--border-radius);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-light);
    }

    .page-header h2 {
        color: #2c3e50;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    /* Grade Card - Hero Section */
    .grade-hero {
        background: white;
        border-radius: var(--border-radius);
        padding: 3rem;
        box-shadow: var(--shadow-medium);
        margin-bottom: 2rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .grade-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: var(--gradient-gold);
    }

    .grade-display {
        display: inline-block;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: var(--gradient-gold);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        margin: 1rem auto;
        box-shadow: 0 10px 40px rgba(255, 215, 0, 0.3);
    }

    .grade-letter {
        font-size: 5rem;
        font-weight: 800;
        color: white;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        line-height: 1;
    }

    .grade-score {
        font-size: 1.5rem;
        color: white;
        font-weight: 600;
        margin-top: 0.5rem;
    }

    .grade-status {
        margin-top: 1.5rem;
        font-size: 1.1rem;
        color: #718096;
    }

    .grade-status.released {
        color: #48bb78;
        font-weight: 600;
    }

    .grade-status.pending {
        color: #f6ad55;
        font-weight: 600;
    }

    /* Project Info Card */
    .info-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 2rem;
        box-shadow: var(--shadow-light);
        margin-bottom: 2rem;
    }

    .info-card h4 {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .info-row {
        display: flex;
        padding: 1rem 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: #718096;
        min-width: 150px;
    }

    .info-value {
        color: #2c3e50;
        flex: 1;
    }

    /* Evaluation Table */
    .evaluation-card {
        background: white;
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--shadow-light);
        margin-bottom: 2rem;
    }

    .evaluation-header {
        background: var(--gradient-primary);
        color: white;
        padding: 1.5rem 2rem;
    }

    .evaluation-header h4 {
        margin: 0;
        font-weight: 600;
    }

    .evaluation-table {
        width: 100%;
        margin: 0;
    }

    .evaluation-table thead th {
        background: #f8f9fa;
        font-weight: 600;
        color: #2c3e50;
        padding: 1rem;
        border-bottom: 2px solid #e9ecef;
    }

    .evaluation-table tbody tr {
        transition: var(--transition);
    }

    .evaluation-table tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }

    .evaluation-table td {
        padding: 1.25rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }

    .evaluator-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .evaluator-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--gradient-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
    }

    .role-badge {
        display: inline-block;
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .role-advisor {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }

    .role-committee {
        background: linear-gradient(135deg, #48bb78, #38a169);
        color: white;
    }

    .score-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1.1rem;
    }

    .score-excellent {
        background: linear-gradient(135deg, #48bb78, #38a169);
        color: white;
    }

    .score-good {
        background: linear-gradient(135deg, #4299e1, #3182ce);
        color: white;
    }

    .score-average {
        background: linear-gradient(135deg, #f6ad55, #ed8936);
        color: white;
    }

    .score-poor {
        background: linear-gradient(135deg, #f56565, #e53e3e);
        color: white;
    }

    .confirmation-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .confirmation-status i {
        font-size: 1.2rem;
    }

    .confirmed {
        color: #48bb78;
    }

    .pending {
        color: #f6ad55;
    }

    .no-evaluations {
        text-align: center;
        padding: 3rem;
        color: #718096;
    }

    .no-evaluations i {
        font-size: 4rem;
        opacity: 0.3;
        margin-bottom: 1rem;
    }

    .back-btn {
        background: white;
        color: #2c3e50;
        padding: 0.75rem 1.5rem;
        border-radius: var(--border-radius);
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: var(--shadow-light);
        transition: var(--transition);
    }

    .back-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-medium);
        color: #667eea;
    }

    /* Members Section */
    .members-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .member-card {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .member-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: var(--gradient-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .member-info h6 {
        margin: 0;
        font-weight: 600;
        color: #2c3e50;
        font-size: 0.95rem;
    }

    .member-info p {
        margin: 0;
        font-size: 0.85rem;
        color: #718096;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2>
                    <i class="bi bi-award-fill me-2" style="color: #ffd700;"></i>
                    คะแนนและผลการประเมิน
                </h2>
                <p class="mb-0 text-muted">ผลการประเมินโครงงาน {{ $project->project_name }}</p>
            </div>
            <a href="{{ route('student.menu') }}" class="back-btn">
                <i class="bi bi-arrow-left"></i>
                <span>กลับหน้าหลัก</span>
            </a>
        </div>
    </div>

    @if($grade && $grade->grade_released)
        <!-- Grade Hero Section -->
        <div class="grade-hero">
            <h3 style="color: #2c3e50; font-weight: 700; margin-bottom: 1rem;">เกรดโครงงานของคุณ</h3>
            <div class="grade-display">
                <div class="grade-letter">{{ $grade->grade }}</div>
                <div class="grade-score">{{ number_format($grade->final_score, 2) }}</div>
            </div>
            <div class="grade-status released">
                <i class="bi bi-check-circle-fill me-2"></i>
                ประกาศเกรดเรียบร้อยแล้ว
            </div>
            <p class="text-muted mt-2">
                ประกาศเมื่อ: {{ $grade->grade_released_at->locale('th')->translatedFormat('j F Y เวลา H:i น.') }}
            </p>
        </div>
    @elseif($grade)
        <!-- Pending Grade -->
        <div class="grade-hero">
            <h3 style="color: #2c3e50; font-weight: 700; margin-bottom: 1rem;">สถานะการประเมิน</h3>
            <div class="grade-display" style="background: var(--gradient-warning);">
                <div class="grade-letter">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
            <div class="grade-status pending">
                <i class="bi bi-clock-fill me-2"></i>
                รอการยืนยันเกรดจากอาจารย์
            </div>
            <p class="text-muted mt-2">
                คะแนนเฉลี่ย: {{ number_format($grade->final_score, 2) }} คะแนน
            </p>
        </div>
    @else
        <!-- No Grade Yet -->
        <div class="grade-hero">
            <h3 style="color: #2c3e50; font-weight: 700; margin-bottom: 1rem;">สถานะการประเมิน</h3>
            <div class="grade-display" style="background: linear-gradient(135deg, #cbd5e0, #a0aec0);">
                <div class="grade-letter">
                    <i class="bi bi-dash-circle"></i>
                </div>
            </div>
            <div class="grade-status">
                <i class="bi bi-info-circle me-2"></i>
                ยังไม่มีผลการประเมิน
            </div>
        </div>
    @endif

    <!-- Project Information -->
    <div class="info-card">
        <h4>
            <i class="bi bi-folder-fill"></i>
            ข้อมูลโครงงาน
        </h4>
        <div class="info-row">
            <div class="info-label">ชื่อโครงงาน:</div>
            <div class="info-value"><strong>{{ $project->project_name }}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-label">รหัสโครงงาน:</div>
            <div class="info-value">{{ $project->project_code ?? 'ยังไม่กำหนด' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">อาจารย์ที่ปรึกษา:</div>
            <div class="info-value">
                @if($project->advisor)
                    {{ $project->advisor->firstname_user }} {{ $project->advisor->lastname_user }}
                @else
                    ยังไม่มีข้อมูล
                @endif
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">สมาชิกกลุ่ม:</div>
            <div class="info-value">
                <div class="members-grid">
                    @foreach($myGroup->members as $member)
                        <div class="member-card">
                            <div class="member-avatar">
                                {{ strtoupper(substr($member->student->firstname_std ?? 'N', 0, 1)) }}
                            </div>
                            <div class="member-info">
                                <h6>{{ $member->student->firstname_std ?? 'N/A' }} {{ $member->student->lastname_std ?? '' }}</h6>
                                <p>{{ $member->student->username_std ?? $member->username_std }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Evaluations -->
    <div class="evaluation-card">
        <div class="evaluation-header">
            <h4>
                <i class="bi bi-clipboard-check-fill me-2"></i>
                รายละเอียดการประเมิน
            </h4>
        </div>
        @if($evaluations && $evaluations->count() > 0)
            <table class="evaluation-table">
                <thead>
                    <tr>
                        <th>ผู้ประเมิน</th>
                        <th>ตำแหน่ง</th>
                        <th>คะแนนเอกสาร (30)</th>
                        <th>คะแนนนำเสนอ (70)</th>
                        <th>รวม (100)</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($evaluations as $eval)
                        <tr>
                            <td>
                                <div class="evaluator-info">
                                    <div class="evaluator-avatar">
                                        {{ strtoupper(substr($eval->evaluator->firstname_user ?? 'N', 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $eval->evaluator->firstname_user ?? 'N/A' }} {{ $eval->evaluator->lastname_user ?? '' }}</strong>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($eval->evaluator_role === 'advisor')
                                    <span class="role-badge role-advisor">
                                        <i class="bi bi-person-fill-check me-1"></i>อาจารย์ที่ปรึกษา
                                    </span>
                                @else
                                    <span class="role-badge role-committee">
                                        <i class="bi bi-people-fill me-1"></i>กรรมการ
                                    </span>
                                @endif
                            </td>
                            <td>{{ number_format($eval->document_score, 2) }}</td>
                            <td>{{ number_format($eval->presentation_score, 2) }}</td>
                            <td>
                                @php
                                    $total = $eval->total_score;
                                    $class = 'score-poor';
                                    if ($total >= 80) $class = 'score-excellent';
                                    elseif ($total >= 70) $class = 'score-good';
                                    elseif ($total >= 60) $class = 'score-average';
                                @endphp
                                <span class="score-badge {{ $class }}">
                                    {{ number_format($total, 2) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $confirmed = false;
                                    if ($grade) {
                                        if ($eval->evaluator_role === 'advisor' && $grade->advisor_confirmed) $confirmed = true;
                                        elseif ($eval->evaluator_role === 'committee1' && $grade->committee1_confirmed) $confirmed = true;
                                        elseif ($eval->evaluator_role === 'committee2' && $grade->committee2_confirmed) $confirmed = true;
                                        elseif ($eval->evaluator_role === 'committee3' && $grade->committee3_confirmed) $confirmed = true;
                                    }
                                @endphp
                                <div class="confirmation-status {{ $confirmed ? 'confirmed' : 'pending' }}">
                                    @if($confirmed)
                                        <i class="bi bi-check-circle-fill"></i>
                                        <span>ยืนยันแล้ว</span>
                                    @else
                                        <i class="bi bi-clock-fill"></i>
                                        <span>รอยืนยัน</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-evaluations">
                <i class="bi bi-inbox"></i>
                <p class="mb-0">ยังไม่มีผลการประเมิน</p>
            </div>
        @endif
    </div>
</div>
@endsection

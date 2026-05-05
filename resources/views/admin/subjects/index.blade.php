@extends('layouts.app')

@section('title', 'จัดการรายวิชา | CSTU SPACE')

@push('styles')
<style>
    body {
        background-color: #f8f9fa;
    }

    .subject-card {
        border: none;
        border-radius: 12px;
        transition: all 0.3s ease;
        height: 100%;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .subject-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }

    .subject-card-header {
        padding: 1.5rem;
        border-bottom: 3px solid;
        position: relative;
    }

    .subject-code {
        font-size: 0.85rem;
        font-weight: 600;
        opacity: 0.8;
        margin-bottom: 0.5rem;
    }

    .subject-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: #fff;
        word-break: break-word;
    }

    .subject-card-body {
        padding: 1.5rem;
    }

    .date-info {
        font-size: 0.85rem;
        margin-bottom: 1rem;
        line-height: 1.6;
    }

    .date-label {
        font-weight: 600;
        color: #495057;
        display: block;
    }

    .date-value {
        color: #212529;
    }

    .status-badge {
        display: inline-block;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .group-count {
        font-size: 1.5rem;
        font-weight: 700;
        color: #495057;
        margin-bottom: 1rem;
    }

    .group-count-label {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .card-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #dee2e6;
    }

    .card-actions button,
    .card-actions a {
        flex: 1;
        padding: 0.5rem;
        font-size: 0.8rem;
        border-radius: 6px;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }

    .filter-section {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h2 fw-bold">
                    <i class="bi bi-book-fill me-2 text-primary"></i>จัดการรายวิชา
                </h1>
                <p class="text-muted">ตั้งค่าและจัดการการเปิด-ปิดวิชา</p>
            </div>
            <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary btn-lg">
                <i class="bi bi-plus-circle me-2"></i>เพิ่มวิชาใหม่
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter Section -->
    <div class="filter-section">
        <form method="GET" action="{{ route('admin.subjects.index') }}" class="row g-3">
            <div class="col-md-4">
                <label for="year" class="form-label">ปีการศึกษา</label>
                <select class="form-select" id="year" name="year">
                    <option value="">ทั้งหมด</option>
                    @foreach($years as $y)
                        <option value="{{ $y->year }}" {{ request('year') == $y->year ? 'selected' : '' }}>
                            {{ $y->year }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="semester" class="form-label">ภาคเรียน</label>
                <select class="form-select" id="semester" name="semester">
                    <option value="">ทั้งหมด</option>
                    @foreach($semesters as $sem)
                        <option value="{{ $sem }}" {{ request('semester') == $sem ? 'selected' : '' }}>
                            ภาคเรียน {{ $sem }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-2 h-100 align-items-end">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-search me-2"></i>ค้นหา
                    </button>
                    <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>รีเซ็ต
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Subjects Grid -->
    @if($subjects->count() > 0)
        <div class="row g-4">
            @foreach($subjects as $subject)
                @php
                    $statusColor = $subject->getStatusColor();
                    $statusText = $subject->getStatusText();
                    $groupCount = $subject->getGroupCount();
                @endphp

                <div class="col-md-6 col-lg-4">
                    <div class="card subject-card border-0">
                        <!-- Header with Status Color -->
                        <div class="subject-card-header" style="background: linear-gradient(135deg, {{ $subject->is_enabled ? '#4e73df' : '#6c757d' }} 0%, {{ $subject->is_enabled ? '#224abe' : '#495057' }} 100%); color: white;">
                            <div class="subject-code">
                                {{ $subject->subject_code }}
                            </div>
                            <div class="subject-name">
                                {{ $subject->subject_name }}
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="subject-card-body">
                            <!-- Status Badge -->
                            <div>
                                <span class="badge bg-{{ $statusColor }} fs-6 status-badge">
                                    <i class="bi bi-{{ $statusColor === 'success' ? 'check-circle' : ($statusColor === 'warning' ? 'hourglass-split' : 'x-circle') }} me-1"></i>
                                    {{ $statusText }}
                                </span>
                            </div>

                            <!-- Dates -->
                            <div class="date-info">
                                <span class="date-label">
                                    <i class="bi bi-calendar-event me-1"></i>เปิด:
                                </span>
                                <span class="date-value">
                                    {{ $subject->open_date->format('d/m/Y H:i') }}
                                </span>
                            </div>

                            <div class="date-info">
                                <span class="date-label">
                                    <i class="bi bi-calendar-x me-1"></i>ปิด:
                                </span>
                                <span class="date-value">
                                    {{ $subject->close_date->format('d/m/Y H:i') }}
                                </span>
                            </div>

                            <!-- Semester & Year -->
                            <div class="date-info">
                                <span class="date-label">
                                    <i class="bi bi-calendar-week me-1"></i>ภาคเรียน:
                                </span>
                                <span class="date-value">
                                    ภาคเรียน {{ $subject->semester }} / ปี {{ $subject->year }}
                                </span>
                            </div>

                            <!-- Group Count -->
                            <div class="group-count">
                                {{ $groupCount }}
                                <div class="group-count-label">กลุ่มโครงงาน</div>
                            </div>

                            <!-- Actions -->
                            <div class="card-actions">
                                <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil-square me-1"></i>แก้ไข
                                </a>
                                <button type="button" class="btn btn-sm {{ $subject->is_enabled ? 'btn-outline-danger' : 'btn-outline-success' }} toggle-subject" data-subject-id="{{ $subject->subject_id }}" data-current-state="{{ $subject->is_enabled ? 1 : 0 }}">
                                    <i class="bi bi-{{ $subject->is_enabled ? 'lock' : 'unlock' }} me-1"></i>
                                    {{ $subject->is_enabled ? 'ปิด' : 'เปิด' }}
                                </button>
                                <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                        <i class="bi bi-trash me-1"></i>ลบ
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $subjects->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class="bi bi-book"></i>
            <h5 class="text-muted mt-3">ไม่มีรายวิชา</h5>
            <p class="text-muted">
                <a href="{{ route('admin.subjects.create') }}">สร้างวิชาใหม่</a>
            </p>
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.querySelectorAll('.toggle-subject').forEach(btn => {
        btn.addEventListener('click', async function() {
            const subjectId = this.dataset.subjectId;
            const currentState = this.dataset.currentState === '1';
            
            try {
                const response = await fetch(`/admin/subjects/${subjectId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Update button
                    this.dataset.currentState = data.is_enabled ? '1' : '0';
                    this.innerHTML = `<i class="bi bi-${data.is_enabled ? 'lock' : 'unlock'} me-1"></i>${data.is_enabled ? 'ปิด' : 'เปิด'}`;
                    this.classList.toggle('btn-outline-danger');
                    this.classList.toggle('btn-outline-success');

                    // Show toast notification
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                    alertDiv.innerHTML = `
                        <i class="bi bi-check-circle me-2"></i>${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.body.appendChild(alertDiv);

                    setTimeout(() => alertDiv.remove(), 3000);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาด');
            }
        });
    });
</script>
@endpush

@endsection

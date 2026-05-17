@extends('layouts.app')

@section('title', 'ตรวจสอบโครงงาน')

@section('content')
<div class="container-xl">
    <!-- Page Header -->
    <div class="page-header d-print-none mb-4">
        <div class="row justify-content-between align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <i class="bi bi-clipboard-check me-2"></i>
                    ตรวจสอบโครงงาน
                </h2>
                <p class="text-secondary">ดูรายละเอียดโครงงาน คณะกรรมการ และสถานะทั้งหมด</p>
            </div>
            <div>
                <a href="{{ route('coordinator.projects.export.csv') }}" class="btn btn-success">
                    <i class="bi bi-download me-2"></i>Export CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form class="row g-3" method="GET" action="{{ route('coordinator.projects.review') }}">
                <div class="col-md-4">
                    <label class="form-label">ค้นหาโครงงาน</label>
                    <input type="text" name="search" class="form-control"
                           placeholder="ชื่อโครงงาน หรือ รหัสโครงงาน"
                           value="{{ request('search') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">สถานะ</label>
                    <select name="status" class="form-select">
                        <option value="">-- ทั้งหมด --</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">ปีการศึกษา</label>
                    <select name="year" class="form-select">
                        <option value="">-- ทั้งหมด --</option>
                        @foreach ($years as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">เทอม</label>
                    <select name="semester" class="form-select">
                        <option value="">-- ทั้งหมด --</option>
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem }}" {{ request('semester') == $sem ? 'selected' : '' }}>
                                เทอม {{ $sem }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i>ค้นหา
                    </button>
                    <a href="{{ route('coordinator.projects.review') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover card-table table-vcenter">
                <thead class="table-light">
                    <tr>
                        <th width="5%">ลำดับ</th>
                        <th width="12%">รหัสโครงงาน</th>
                        <th width="20%">ชื่อโครงงาน</th>
                        <th width="15%">อาจารย์ที่ปรึกษา</th>
                        <th width="10%">สถานะ</th>
                        <th width="15%">วันสอบ</th>
                        <th width="15%">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $index => $project)
                        <tr>
                            <td>
                                <span class="badge bg-secondary">{{ ($projects->currentPage() - 1) * $projects->perPage() + $loop->iteration }}</span>
                            </td>
                            <td>
                                <strong>{{ $project->project_code ?? '-' }}</strong>
                            </td>
                            <td>
                                {{ $project->project_name ?? '-' }}
                            </td>
                            <td>
                                @if ($project->advisor)
                                    <strong>{{ $project->advisor->user_code }}</strong>
                                    <br>
                                    <small class="text-secondary">{{ $project->advisor->firstname_user }} {{ $project->advisor->lastname_user }}</small>
                                @else
                                    <span class="badge bg-warning">ยังไม่กำหนด</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $isCoordinator = auth()->check() && (auth()->user()->role & 16384 || auth()->user()->role & 32768);
                                    $statusColors = [
                                        'pending' => 'secondary',
                                        'in_progress' => 'primary',
                                        'submitted' => 'info',
                                        'late_submission' => 'warning',
                                        'approved' => 'success',
                                        'rejected' => 'danger'
                                    ];
                                    $color = $statusColors[$project->status_project] ?? 'secondary';
                                @endphp
                                @if($isCoordinator)
                                <form action="{{ route('coordinator.projects.update-review', $project->project_id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <select name="status_project" class="form-select form-select-sm" onchange="this.form.submit()">
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status }}" {{ $project->status_project === $status ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                                @else
                                <span class="badge bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $project->status_project)) }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($project->examSchedule)
                                    <strong>{{ $project->examSchedule->ex_start_time->format('d/m/Y') }}</strong>
                                    <br>
                                    <small class="text-secondary">{{ $project->examSchedule->ex_start_time->format('H:i') }} - {{ $project->examSchedule->ex_end_time->format('H:i') }}</small>
                                @else
                                    <span class="badge bg-warning">ยังไม่กำหนด</span>
                                @endif
                            </td>
                            <td>
                                <a href="#" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detailModal{{ $project->project_id }}">
                                    <i class="bi bi-eye me-1"></i>ดูรายละเอียด
                                </a>
                            </td>
                        </tr>

                        <!-- Detail Modal -->
                        <div class="modal fade" id="detailModal{{ $project->project_id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">รายละเอียดโครงงาน: {{ $project->project_code }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <h6 class="text-secondary">อาจารย์ที่ปรึกษา</h6>
                                                @if ($project->advisor)
                                                    <p>
                                                        <strong>{{ $project->advisor->user_code }}</strong><br>
                                                        {{ $project->advisor->firstname_user }} {{ $project->advisor->lastname_user }}
                                                    </p>
                                                @else
                                                    <p><span class="badge bg-warning">ยังไม่กำหนด</span></p>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-secondary">สถานะ</h6>
                                                <p>
                                                    <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $project->status_project)) }}</span>
                                                </p>
                                            </div>
                                        </div>

                                        <h6 class="text-secondary">คณะกรรมการ</h6>
                                        <div class="list-group mb-3">
                                            @php
                                                $committees = [
                                                    $project->committee1,
                                                    $project->committee2,
                                                    $project->committee3
                                                ];
                                            @endphp
                                            @forelse ($committees as $index => $committee)
                                                @if ($committee)
                                                    <div class="list-group-item">
                                                        <strong>คณะกรรมการที่ {{ $index + 1 }}</strong><br>
                                                        {{ $committee->user_code }} - {{ $committee->firstname_user }} {{ $committee->lastname_user }}
                                                    </div>
                                                @endif
                                            @empty
                                                <p class="text-secondary">ยังไม่มีการกำหนดคณะกรรมการ</p>
                                            @endforelse
                                        </div>

                                        <h6 class="text-secondary">สมาชิกกลุ่ม</h6>
                                        <div class="list-group mb-3">
                                            @if ($project->group && $project->group->members)
                                                @foreach ($project->group->members as $member)
                                                    <div class="list-group-item">
                                                        {{ $member->student->firstname_std }} {{ $member->student->lastname_std }}
                                                        <small class="text-secondary">({{ $member->username_std }})</small>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="text-secondary">ไม่มีข้อมูลสมาชิก</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <p class="text-secondary">ไม่พบข้อมูลโครงงาน</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="card-footer d-flex align-items-center">
            <p class="text-secondary m-0">
                แสดง {{ $projects->firstItem() ?? 0 }} - {{ $projects->lastItem() ?? 0 }} จากทั้งหมด {{ $projects->total() }} รายการ
            </p>
            <div class="ms-auto">
                {{ $projects->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

<style>
    .form-select-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .table-hover tbody tr:hover {
        background-color: #f5f5f5;
    }
</style>
@endsection

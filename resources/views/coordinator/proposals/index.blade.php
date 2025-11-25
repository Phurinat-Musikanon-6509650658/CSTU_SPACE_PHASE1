@extends('layouts.app')

@section('title', 'ข้อเสนอโครงงานทั้งหมด - Coordinator')

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="page-header mb-4">
                <h1>
                    <i class="bi bi-file-earmark-text-fill me-2"></i>ข้อเสนอโครงงานทั้งหมด
                </h1>
                <p>รายการข้อเสนอหัวข้อโครงงานทั้งหมดในระบบ (Coordinator View)</p>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center p-3" style="border-left: 4px solid var(--color-blue);">
                        <h3 class="mb-0" style="color: var(--color-blue);">
                            {{ $proposals->count() }}
                        </h3>
                        <small class="text-muted">ทั้งหมด</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center p-3" style="border-left: 4px solid var(--color-yellow);">
                        <h3 class="mb-0" style="color: var(--color-yellow);">
                            {{ $proposals->where('status', 'pending')->count() }}
                        </h3>
                        <small class="text-muted">รอพิจารณา</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center p-3" style="border-left: 4px solid var(--color-green);">
                        <h3 class="mb-0" style="color: var(--color-green);">
                            {{ $proposals->where('status', 'approved')->count() }}
                        </h3>
                        <small class="text-muted">อนุมัติแล้ว</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center p-3" style="border-left: 4px solid var(--color-blue);">
                        <h3 class="mb-0" style="color: var(--color-blue);">
                            {{ $proposals->whereIn('status', ['in_progress', 'late_submission'])->count() }}
                        </h3>
                        <small class="text-muted">กำลังทำ</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-center p-3" style="border-left: 4px solid var(--color-green);">
                        <h3 class="mb-0" style="color: var(--color-green);">
                            {{ $proposals->where('status', 'submitted')->count() }}
                        </h3>
                        <small class="text-muted">ส่งเล่มแล้ว</small>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="card text-center p-3" style="border-left: 4px solid var(--color-red);">
                        <h3 class="mb-0" style="color: var(--color-red);">
                            {{ $proposals->where('status', 'rejected')->count() }}
                        </h3>
                        <small class="text-muted">ปฏิเสธ</small>
                    </div>
                </div>
            </div>

            <!-- Proposals List -->
            @if($proposals->isEmpty())
                <div class="card p-5 text-center">
                    <i class="bi bi-inbox text-muted" style="font-size: 5rem;"></i>
                    <h5 class="mt-3 text-muted">ยังไม่มีข้อเสนอโครงงาน</h5>
                    <p class="text-muted mb-0">เมื่อนักศึกษาส่งข้อเสนอมา จะแสดงที่นี่</p>
                </div>
            @else
                @foreach($proposals as $proposal)
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="mb-0">{{ $proposal->proposed_title }}</h5>
                                        <span class="badge 
                                            @if($proposal->status === 'pending') bg-warning
                                            @elseif($proposal->status === 'approved') bg-success
                                            @else bg-danger
                                            @endif
                                        ">
                                            @if($proposal->status === 'pending') รอพิจารณา
                                            @elseif($proposal->status === 'approved') อนุมัติแล้ว
                                            @else ปฏิเสธแล้ว
                                            @endif
                                        </span>
                                    </div>

                                    @if($proposal->description)
                                        <p class="text-muted mb-2">{{ Str::limit($proposal->description, 150) }}</p>
                                    @endif

                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <p class="mb-1 small">
                                                <i class="bi bi-diagram-3 me-1"></i>
                                                <strong>กลุ่มที่:</strong> {{ $proposal->group->group_id }}
                                            </p>
                                            <p class="mb-1 small">
                                                <i class="bi bi-book me-1"></i>
                                                <strong>รหัสวิชา:</strong> {{ $proposal->group->subject_code }}
                                            </p>
                                            <p class="mb-1 small">
                                                <i class="bi bi-person-badge me-1"></i>
                                                <strong>อาจารย์:</strong> 
                                                @if($proposal->lecturer)
                                                    {{ $proposal->lecturer->firstname_user }} {{ $proposal->lecturer->lastname_user }}
                                                @else
                                                    -
                                                @endif
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1 small">
                                                <i class="bi bi-people me-1"></i>
                                                <strong>สมาชิก:</strong> {{ $proposal->group->members->count() }} คน
                                            </p>
                                            <p class="mb-1 small">
                                                <i class="bi bi-person me-1"></i>
                                                <strong>เสนอโดย:</strong> 
                                                @if($proposal->student)
                                                    {{ $proposal->student->firstname_std }} {{ $proposal->student->lastname_std }}
                                                @else
                                                    -
                                                @endif
                                            </p>
                                            <p class="mb-0 small">
                                                <i class="bi bi-clock me-1"></i>
                                                เสนอเมื่อ {{ $proposal->proposed_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 d-flex align-items-center justify-content-end">
                                    <div class="d-grid gap-2 w-100">
                                        <a href="{{ route('lecturer.proposals.show', $proposal->proposal_id) }}" 
                                           class="btn btn-primary">
                                            <i class="bi bi-eye me-1"></i>ดูรายละเอียด
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection

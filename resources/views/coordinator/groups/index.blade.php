@extends('layouts.app')

@section('title', 'จัดการกลุ่มโครงงาน')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Back to Menu Button -->
    <div class="mb-4">
        <a href="{{ route('menu') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>กลับไปหน้าเมนูหลัก
        </a>
    </div>

    <h1 class="mb-4">จัดการกลุ่มโครงงาน</h1>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-funnel me-1"></i>ฟิลเตอร์
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('coordinator.groups.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">สถานะ</label>
                        <select name="status" class="form-select">
                            <option value="">ทั้งหมด</option>
                            <option value="created" {{ request('status') == 'created' ? 'selected' : '' }}>สร้างแล้ว</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>รออนุมัติ</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>อนุมัติแล้ว</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>ปฏิเสธ</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">รหัสวิชา</label>
                        <select name="subject" class="form-select">
                            <option value="">ทั้งหมด</option>
                            <option value="CS303" {{ request('subject') == 'CS303' ? 'selected' : '' }}>CS303</option>
                            <option value="CS403" {{ request('subject') == 'CS403' ? 'selected' : '' }}>CS403</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">เทอม</label>
                        <select name="semester" class="form-select">
                            <option value="">ทั้งหมด</option>
                            <option value="1" {{ request('semester') == '1' ? 'selected' : '' }}>ภาคต้น</option>
                            <option value="2" {{ request('semester') == '2' ? 'selected' : '' }}>ภาคปลาย</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> ค้นหา
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Groups Table -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-table me-1"></i>
            รายการกลุ่มทั้งหมด ({{ $groups->total() }} กลุ่ม)
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>รหัสวิชา</th>
                            <th>ปี/เทอม</th>
                            <th>ชื่อโครงงาน</th>
                            <th>สมาชิก</th>
                            <th>สถานะกลุ่ม</th>
                            <th>สถานะโครงงาน</th>
                            <th>อาจารย์ที่ปรึกษา</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groups as $group)
                        <tr>
                            <td>
                                <strong>{{ sprintf('%02d-%02d', $group->semester, $group->group_id) }}</strong>
                            </td>
                            <td>{{ $group->subject_code }}</td>
                            <td>{{ $group->year }}/{{ $group->semester }}</td>
                            <td>
                                @if($group->project)
                                    {{ $group->project->project_name }}
                                    <br><small class="text-muted">{{ $group->project->project_code }}</small>
                                @elseif($group->latestProposal)
                                    {{ $group->latestProposal->proposed_title }}
                                    <br><small class="text-muted">(รอดำเนินการ)</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                {{ $group->members->count() }}/2
                                <br>
                                <small class="text-muted">
                                    @foreach($group->members as $member)
                                        {{ $member->student->firstname_std ?? 'N/A' }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                </small>
                            </td>
                            <td>
                                @if($group->status_group === 'not_created')
                                    <span class="badge bg-secondary">ยังไม่ได้สร้าง</span>
                                @elseif($group->status_group === 'created')
                                    <span class="badge bg-info">สร้างแล้ว</span>
                                @elseif($group->status_group === 'member_left')
                                    <span class="badge bg-warning">สมาชิกออกกลุ่ม</span>
                                @elseif($group->status_group === 'member_added')
                                    <span class="badge bg-primary">สมาชิกเพิ่มเข้ามา</span>
                                @elseif($group->status_group === 'disbanded')
                                    <span class="badge bg-danger">กลุ่มถูกยุบ</span>
                                @else
                                    <span class="badge bg-secondary">{{ $group->status_group }}</span>
                                @endif
                            </td>
                            <td>
                                @if($group->project && $group->project->status_project)
                                    @if($group->project->status_project === 'not_proposed')
                                        <span class="badge bg-secondary">
                                            ยังไม่ได้กำหนดหัวข้อ
                                        </span>
                                    @elseif($group->project->status_project === 'pending')
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-clock me-1"></i>รอพิจารณา
                                        </span>
                                    @elseif($group->project->status_project === 'approved')
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>อนุมัติ
                                        </span>
                                    @elseif($group->project->status_project === 'rejected')
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle me-1"></i>ปฏิเสธ
                                        </span>
                                    @elseif($group->project->status_project === 'in_progress')
                                        <span class="badge bg-info">
                                            <i class="bi bi-gear-fill me-1"></i>กำลังดำเนินงาน
                                        </span>
                                    @elseif($group->project->status_project === 'late_submission')
                                        <span class="badge bg-warning">
                                            <i class="bi bi-exclamation-triangle me-1"></i>ส่งเล่มล่าช้า
                                        </span>
                                    @elseif($group->project->status_project === 'submitted')
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle-fill me-1"></i>ส่งเล่มแล้ว
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">{{ $group->project->status_project }}</span>
                                    @endif
                                @elseif($group->latestProposal)
                                    @if($group->latestProposal->status === 'pending')
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-clock me-1"></i>รอพิจารณา
                                        </span>
                                    @elseif($group->latestProposal->status === 'approved')
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>อนุมัติ
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle me-1"></i>ปฏิเสธ
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($group->latestProposal && $group->latestProposal->lecturer)
                                    {{ $group->latestProposal->lecturer->firstname_user }} {{ $group->latestProposal->lecturer->lastname_user }}
                                    <br><small class="text-muted">(ข้อเสนอ)</small>
                                @elseif($group->project && $group->project->advisor)
                                    {{ $group->project->advisor->firstname_user }} {{ $group->project->advisor->lastname_user }}
                                    <br><small class="text-muted">{{ $group->project->advisor_code }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('coordinator.groups.show', $group->group_id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> ดู
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">ไม่พบข้อมูล</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $groups->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@php
    $statusLabels = [
        'not_proposed'   => 'ยังไม่เสนอ',
        'pending'        => 'รออนุมัติ',
        'approved'       => 'อนุมัติแล้ว',
        'rejected'       => 'ถูกปฏิเสธ',
        'in_progress'    => 'กำลังดำเนินการ',
        'submitted'      => 'ส่งงานแล้ว',
        'late_submission'=> 'ส่งงานล่าช้า',
        'passed'         => 'ผ่าน',
        'failed'         => 'ไม่ผ่าน',
    ];
    $typeLabels = ['s'=>'พิเศษ','r'=>'ปกติ','m'=>'ผสม'];
@endphp

@if($projects->isEmpty())
    <div class="empty-state">
        <i class="bi bi-inbox" style="font-size:3rem;"></i>
        <p class="mt-2 mb-0">ไม่พบโครงงานสำหรับ {{ $subject }}</p>
        <small class="text-muted">ลองเปลี่ยนปีการศึกษา หรือ import ข้อมูลก่อน</small>
    </div>
@else
    <div class="table-responsive">
        <table class="table proj-table mb-0">
            <thead>
                <tr>
                    <th style="width:38px;">No.</th>
                    <th style="min-width:160px;">รหัสโครงงาน</th>
                    <th style="min-width:220px;">ชื่อโครงงาน</th>
                    <th>ประเภท</th>
                    <th style="min-width:140px;">นักศึกษา 1</th>
                    <th style="min-width:140px;">นักศึกษา 2</th>
                    <th>ที่ปรึกษา</th>
                    <th>กรรมการ 1</th>
                    <th>กรรมการ 2</th>
                    <th>กรรมการ 3</th>
                    <th style="min-width:90px;">วันสอบ</th>
                    <th>เวลา</th>
                    <th>ห้อง</th>
                    <th>สถานะ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($projects as $no => $p)
                @php
                    $m1  = $p->first_member;
                    $m2  = $p->second_member;
                    $edt = $p->exam_datetime;
                    $eet = $p->exam_end_time;
                    $rowBg = $no % 2 === 1 ? 'style="background:#f9fbff;"' : '';
                    $memberText = collect([$m1, $m2])->filter()->map(fn($m) => $m->firstname_std.' '.$m->lastname_std.' '.$m->username_std)->implode(' ');
                @endphp
                <tr {!! $rowBg !!}
                    data-code="{{ strtolower($p->project_code) }}"
                    data-name="{{ strtolower($p->project_name) }}"
                    data-members="{{ strtolower($memberText) }}"
                    data-advisor="{{ strtolower($p->advisorLecturer?->user_code ?? '') }}"
                    data-status="{{ $p->status_project }}"
                    data-type="{{ $p->project_type }}"
                    data-has-exam="{{ $edt ? '1' : '0' }}">
                    <td class="text-center text-muted">{{ $no + 1 }}</td>
                    <td>
                        <code class="small text-primary">{{ $p->project_code }}</code>
                    </td>
                    <td>
                        <div class="fw-semibold" style="font-size:.83rem; line-height:1.35;">
                            {{ $p->project_name }}
                        </div>
                    </td>
                    <td>
                        @if($p->project_type)
                            @foreach(explode(',', $p->project_type) as $t)
                                <span class="badge-type badge-{{ trim($t) }}">
                                    {{ $typeLabels[trim($t)] ?? trim($t) }}
                                </span>
                            @endforeach
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    {{-- Student 1 --}}
                    <td>
                        @if($m1)
                            <div class="member-block">
                                <div class="fw-semibold">{{ $m1->firstname_std }} {{ $m1->lastname_std }}</div>
                                <small>{{ $m1->username_std }}</small>
                            </div>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    {{-- Student 2 --}}
                    <td>
                        @if($m2)
                            <div class="member-block">
                                <div class="fw-semibold">{{ $m2->firstname_std }} {{ $m2->lastname_std }}</div>
                                <small>{{ $m2->username_std }}</small>
                            </div>
                        @else
                            <span class="text-muted small">1 คน</span>
                        @endif
                    </td>

                    {{-- Advisor --}}
                    <td>
                        @if($p->advisorLecturer)
                            <span class="adv-code">{{ $p->advisorLecturer->user_code }}</span>
                            @if($p->advisorLecturer->user)
                                <div class="small text-muted" style="font-size:.73rem;">
                                    {{ $p->advisorLecturer->user->firstname_user }}
                                </div>
                            @endif
                        @else
                            <span class="text-danger small">ยังไม่ระบุ</span>
                        @endif
                    </td>

                    {{-- Committees --}}
                    @foreach([0,1,2] as $ci)
                    @php $comm = $p->committeeLecturers->get($ci); @endphp
                    <td>
                        @if($comm)
                            <span class="adv-code">{{ $comm->user_code }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    @endforeach

                    {{-- Exam --}}
                    <td>
                        @if($edt)
                            <span style="font-size:.79rem;">{{ thaiDate($edt) }}</span>
                        @else
                            <span class="text-danger small">ยังไม่กำหนด</span>
                        @endif
                    </td>
                    <td>
                        @if($edt)
                            <span style="font-size:.79rem; white-space:nowrap;">
                                {{ $edt->format('H:i') }}
                                @if($eet) – {{ $eet->format('H:i') }} @endif
                                น.
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td><span class="text-muted">-</span></td>

                    {{-- Status --}}
                    <td>
                        <span class="status-chip status-{{ $p->status_project }}">
                            {{ $statusLabels[$p->status_project] ?? $p->status_project }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="text-end text-muted small p-2 summary-count" data-total="{{ $projects->count() }}">
        รวม <span class="visible-count">{{ $projects->count() }}</span> / {{ $projects->count() }} โครงงาน
    </div>
@endif

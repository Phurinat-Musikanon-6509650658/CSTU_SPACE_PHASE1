<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบประเมินรวม — {{ trim(($user->firstname_user ?? '') . ' ' . ($user->lastname_user ?? '')) ?: $user->username_user }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'TH Sarabun New', 'Sarabun', 'Kanit', Arial, sans-serif;
            font-size: 14px;
            color: #000;
            background: #f5f5f5;
            padding: 20px;
        }

        /* ── Warning banner (ซ่อนตอน print) ── */
        .warning-banner {
            background: #fff3cd;
            border: 1.5px solid #ffc107;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 20px;
        }
        .warning-banner .warn-title {
            font-size: 15px;
            font-weight: 700;
            color: #856404;
            margin-bottom: 10px;
        }
        .warn-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .warn-list li {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #664d03;
        }
        .warn-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #ffc107;
            flex-shrink: 0;
        }

        /* ── Toolbar (ซ่อนตอน print) ── */
        .toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .btn-print {
            padding: 8px 22px;
            background: #2c3e7a;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            font-family: inherit;
        }
        .btn-back {
            padding: 8px 18px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            font-family: inherit;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        .page-count {
            font-size: 13px;
            color: #666;
        }

        /* ── Each project page ── */
        .project-page {
            background: #fff;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
            page-break-after: always;
        }
        .project-page:last-child {
            page-break-after: avoid;
        }

        /* ── Doc header ── */
        .doc-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .doc-header .dept { font-size: 12px; color: #444; }
        .doc-header .title { font-size: 19px; font-weight: 700; margin: 3px 0; }
        .doc-header .subtitle { font-size: 12px; color: #555; }

        .role-pill {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            border: 1.5px solid #333;
        }

        /* ── Info section ── */
        .info-section {
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 14px;
            background: #fafafa;
        }
        .info-row {
            display: flex;
            gap: 8px;
            margin-bottom: 4px;
            line-height: 1.5;
            font-size: 13px;
        }
        .info-label {
            font-weight: 600;
            min-width: 110px;
            flex-shrink: 0;
        }

        /* ── Eval table ── */
        .eval-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 12.5px;
        }
        .eval-table th, .eval-table td {
            border: 1px solid #888;
            padding: 6px 8px;
            vertical-align: middle;
        }
        .eval-table thead th {
            background: #2c3e7a;
            color: #fff;
            text-align: center;
            font-weight: 600;
        }
        .eval-table thead th.criteria-th { text-align: left; }
        .eval-table .max-col { text-align: center; color: #555; }
        .eval-table .score-col { text-align: center; font-weight: 600; font-size: 13px; min-width: 70px; }
        .eval-table .section-header td { background: #dce8ff; font-weight: 700; color: #1a3070; border-top: 2px solid #4e73df; }
        .eval-table .sub-row td:first-child { padding-left: 24px; color: #333; }
        .eval-table .subtotal-row td { background: #e8f0ff; font-weight: 700; border-top: 2px solid #4e73df; }
        .eval-table .total-row td { background: #d0e4ff; font-weight: 700; font-size: 14px; border-top: 2px solid #2c3e7a; }

        /* ── Signature ── */
        .signature-section {
            margin-top: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 16px 20px;
        }
        .sig-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 28px; }
        .sig-box { text-align: center; }
        .sig-line { border-bottom: 1.5px solid #333; height: 46px; margin: 8px 0 6px; }
        .sig-label { font-size: 12px; color: #444; line-height: 1.8; }
        .sig-label strong { color: #000; font-size: 13px; }

        /* ── Footer ── */
        .doc-footer {
            margin-top: 16px;
            padding-top: 8px;
            border-top: 1px dashed #ccc;
            font-size: 11px;
            color: #777;
            text-align: center;
        }

        /* ── Print ── */
        @media print {
            body { padding: 0; background: #fff; }
            .no-print { display: none !important; }
            .project-page { box-shadow: none; border-radius: 0; margin: 0; padding: 1.2cm; }
            @page { margin: 1.2cm; size: A4; }
        }
    </style>
</head>
<body>

    @php
        $roleLabels = [
            'advisor'    => 'อาจารย์ที่ปรึกษา',
            'committee1' => 'กรรมการคนที่ 1',
            'committee2' => 'กรรมการคนที่ 2',
            'committee3' => 'กรรมการคนที่ 3',
        ];
        $evaluatorName = trim(($user->firstname_user ?? '') . ' ' . ($user->lastname_user ?? '')) ?: $user->username_user;
    @endphp

    {{-- ── Toolbar (ซ่อนตอน print) ── --}}
    <div class="toolbar no-print">
        <a href="{{ route('lecturer.evaluations.index') }}" class="btn-back">← กลับ</a>
        @if(count($evaluated) > 0)
            <button onclick="window.print()" class="btn-print">🖨 พิมพ์ / บันทึก PDF ({{ count($evaluated) }} ใบ)</button>
        @endif
        <span class="page-count">
            ประเมินแล้ว {{ count($evaluated) }} โครงงาน
            @if(count($pending) > 0)
                &nbsp;·&nbsp; <span style="color:#856404;">ยังไม่ได้ประเมิน {{ count($pending) }} โครงงาน</span>
            @endif
        </span>
    </div>

    {{-- ── Warning: โครงงานยังไม่ได้ประเมิน (ซ่อนตอน print) ── --}}
    @if(count($pending) > 0)
    <div class="warning-banner no-print">
        <div class="warn-title">⚠️ โครงงานที่ยังไม่ได้ประเมิน ({{ count($pending) }} โครงงาน) — จะไม่ถูกรวมใน PDF</div>
        <ul class="warn-list">
            @foreach($pending as $entry)
            @php $p = $entry['project']; @endphp
            <li>
                <span class="warn-dot"></span>
                <strong>{{ $p->project_code }}</strong>
                &nbsp;·&nbsp;
                {{ $p->project_name ?? 'ยังไม่ระบุชื่อ' }}
                &nbsp;·&nbsp;
                <span style="color:#999;">ตำแหน่ง: {{ $roleLabels[$entry['role']] ?? $entry['role'] }}</span>
                &nbsp;
                <a href="{{ route('lecturer.evaluations.form', $p->project_id) }}"
                   style="font-size:12px;color:#0d6efd;">ไปให้คะแนน →</a>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ── ไม่มีใบประเมินเลย ── --}}
    @if(count($evaluated) === 0)
    <div class="no-print" style="text-align:center;padding:60px 20px;color:#888;">
        <div style="font-size:3rem;margin-bottom:12px;">📋</div>
        <div style="font-size:16px;font-weight:600;">ยังไม่มีโครงงานที่ประเมินแล้ว</div>
        <div style="font-size:13px;margin-top:6px;">กรุณาให้คะแนนโครงงานก่อนแล้วค่อย export</div>
    </div>
    @endif

    {{-- ── ใบประเมินแต่ละโครงงาน ── --}}
    @foreach($evaluated as $entry)
    @php
        $project  = $entry['project'];
        $role     = $entry['role'];
        $students = $entry['students'];
        $scores   = $entry['scores'];
        $criteria = $entry['criteria'];
        $maxScore = $role === 'advisor' ? $criteria->getAdvisorMax() : $criteria->getCommitteeMax();
    @endphp

    <div class="project-page">

        <div class="doc-header">
            <div class="dept">ภาควิชาวิทยาการคอมพิวเตอร์ คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยธรรมศาสตร์</div>
            <div class="title">ใบประเมินโครงงานพิเศษ</div>
            <div class="subtitle">
                <span class="role-pill">{{ $roleLabels[$role] ?? $role }}</span>
                &nbsp;|&nbsp; คะแนนเต็ม {{ $maxScore }} คะแนน
                &nbsp;|&nbsp; วิชา {{ $project->group->subject_code ?? '' }}
                &nbsp;|&nbsp; พิมพ์เมื่อ: {{ now()->format('d/m/Y H:i น.') }}
            </div>
        </div>

        <div class="info-section">
            <div class="info-row">
                <span class="info-label">รหัสโครงงาน:</span>
                <span><strong>{{ $project->project_code }}</strong></span>
            </div>
            <div class="info-row">
                <span class="info-label">ชื่อโครงงาน:</span>
                <span>{{ $project->project_name ?? 'ยังไม่ระบุ' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">สมาชิก:</span>
                <span>
                    @foreach($project->group->members as $member)
                        {{ $member->student->firstname_std ?? '' }} {{ $member->student->lastname_std ?? '' }}
                        ({{ $member->student->username_std ?? '' }})@if(!$loop->last),&nbsp;@endif
                    @endforeach
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">วันเวลาสอบ:</span>
                <span>
                    @if($project->exam_datetime)
                        {{ $project->exam_datetime->format('d/m/Y H:i น.') }}
                    @else
                        ยังไม่กำหนด
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">ผู้ประเมิน:</span>
                <span>{{ $evaluatorName }} ({{ $roleLabels[$role] ?? $role }})</span>
            </div>
        </div>

        <table class="eval-table">
            <thead>
                <tr>
                    <th class="criteria-th" style="width:44%">เกณฑ์การประเมิน</th>
                    <th style="width:8%">คะแนนเต็ม</th>
                    @foreach($students as $idx => $student)
                    <th>
                        {{ $student->firstname_std }} {{ $student->lastname_std }}<br>
                        <span style="font-size:11px;font-weight:400;">{{ $student->username_std }}</span>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>

                @if($role === 'advisor')
                <tr>
                    <td><strong>ส่วนที่ 1:</strong> {{ $criteria->part1_label }} <small style="color:#777">(อาจารย์ที่ปรึกษา)</small></td>
                    <td class="max-col">{{ $criteria->part1_max }}</td>
                    @foreach($students as $idx => $student)
                    <td class="score-col">{{ $scores[$idx]['part1'] }}</td>
                    @endforeach
                </tr>
                @endif

                <tr>
                    <td><strong>ส่วนที่ 2:</strong> {{ $criteria->part2_label }}</td>
                    <td class="max-col">{{ $criteria->part2_max }}</td>
                    @foreach($students as $idx => $student)
                    <td class="score-col">{{ $scores[$idx]['part2'] }}</td>
                    @endforeach
                </tr>

                <tr class="section-header">
                    <td colspan="{{ 2 + $students->count() }}">
                        ส่วนที่ 3: การนำเสนอโครงงาน (รวม {{ $criteria->getPart3Max() }} คะแนน)
                    </td>
                </tr>

                <tr class="sub-row">
                    <td>3.1 {{ $criteria->part3a_label }}</td>
                    <td class="max-col">{{ $criteria->part3a_max }}</td>
                    @foreach($students as $idx => $student)
                    <td class="score-col">{{ $scores[$idx]['part3a'] }}</td>
                    @endforeach
                </tr>

                <tr class="sub-row">
                    <td>3.2 {{ $criteria->part3b_label }}</td>
                    <td class="max-col">{{ $criteria->part3b_max }}</td>
                    @foreach($students as $idx => $student)
                    <td class="score-col">{{ $scores[$idx]['part3b'] }}</td>
                    @endforeach
                </tr>

                <tr class="sub-row">
                    <td>3.3 {{ $criteria->part3c_label }}</td>
                    <td class="max-col">{{ $criteria->part3c_max }}</td>
                    @foreach($students as $idx => $student)
                    <td class="score-col">{{ $scores[$idx]['part3c'] }}</td>
                    @endforeach
                </tr>

                <tr class="subtotal-row">
                    <td>รวมส่วนที่ 3</td>
                    <td class="max-col">{{ $criteria->getPart3Max() }}</td>
                    @foreach($students as $idx => $student)
                    <td class="score-col">
                        @php
                            $s3a = is_numeric($scores[$idx]['part3a']) ? $scores[$idx]['part3a'] : 0;
                            $s3b = is_numeric($scores[$idx]['part3b']) ? $scores[$idx]['part3b'] : 0;
                            $s3c = is_numeric($scores[$idx]['part3c']) ? $scores[$idx]['part3c'] : 0;
                        @endphp
                        {{ ($scores[$idx]['part3a'] === '-') ? '-' : number_format($s3a + $s3b + $s3c, 2) }}
                    </td>
                    @endforeach
                </tr>

                <tr class="total-row">
                    <td><strong>คะแนนรวมทั้งหมด</strong></td>
                    <td class="max-col"><strong>{{ $maxScore }}</strong></td>
                    @foreach($students as $idx => $student)
                    <td class="score-col">
                        @php
                            $p1  = ($role === 'advisor' && is_numeric($scores[$idx]['part1'])) ? $scores[$idx]['part1'] : 0;
                            $p2  = is_numeric($scores[$idx]['part2'])  ? $scores[$idx]['part2']  : 0;
                            $p3a = is_numeric($scores[$idx]['part3a']) ? $scores[$idx]['part3a'] : 0;
                            $p3b = is_numeric($scores[$idx]['part3b']) ? $scores[$idx]['part3b'] : 0;
                            $p3c = is_numeric($scores[$idx]['part3c']) ? $scores[$idx]['part3c'] : 0;
                            $grand = $p1 + $p2 + $p3a + $p3b + $p3c;
                        @endphp
                        <strong>{{ ($scores[$idx]['part2'] === '-') ? '-' : number_format($grand, 2) }}</strong>
                    </td>
                    @endforeach
                </tr>

            </tbody>
        </table>

        <div class="signature-section">
            <div style="font-size:13px;font-weight:700;margin-bottom:14px;border-bottom:1px dashed #aaa;padding-bottom:6px;">
                ลายเซ็นรับรองคะแนน (ลงนามนอกระบบ)
            </div>
            <div class="sig-grid">
                <div class="sig-box">
                    <div class="sig-line"></div>
                    <div class="sig-label">
                        <strong>{{ $evaluatorName }}</strong><br>
                        {{ $roleLabels[$role] ?? $role }}<br>
                        วันที่ ............/............/............
                    </div>
                </div>
                <div class="sig-box">
                    <div class="sig-line"></div>
                    <div class="sig-label">
                        <strong>ผู้รับรองคะแนน</strong><br>
                        ตำแหน่ง ........................................<br>
                        วันที่ ............/............/............
                    </div>
                </div>
            </div>
        </div>

        <div class="doc-footer">
            CSTU SPACE &nbsp;|&nbsp; {{ $project->project_code }} &nbsp;|&nbsp; {{ $roleLabels[$role] ?? $role }}: {{ $evaluatorName }}
        </div>

    </div>
    @endforeach

</body>
</html>

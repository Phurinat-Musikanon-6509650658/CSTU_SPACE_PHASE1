<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบประเมินโครงงาน — {{ $project->project_code }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'TH Sarabun New', 'Sarabun', 'Kanit', Arial, sans-serif;
            font-size: 14px;
            color: #000;
            background: #fff;
            padding: 20px;
        }

        /* ── Header ── */
        .doc-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .doc-header .dept {
            font-size: 13px;
            color: #444;
        }
        .doc-header .title {
            font-size: 20px;
            font-weight: 700;
            margin: 4px 0;
        }
        .doc-header .subtitle {
            font-size: 13px;
            color: #555;
        }

        /* ── Project Info ── */
        .info-section {
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 16px;
            background: #fafafa;
        }
        .info-row {
            display: flex;
            gap: 8px;
            margin-bottom: 5px;
            line-height: 1.5;
        }
        .info-label {
            font-weight: 600;
            min-width: 120px;
            flex-shrink: 0;
        }

        /* ── Role badge ── */
        .role-pill {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            border: 1.5px solid #333;
        }

        /* ── Eval Table ── */
        .eval-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 13px;
        }
        .eval-table th, .eval-table td {
            border: 1px solid #888;
            padding: 7px 9px;
            vertical-align: middle;
        }
        .eval-table thead th {
            background: #2c3e7a;
            color: #fff;
            text-align: center;
            font-weight: 600;
        }
        .eval-table thead th.criteria-th {
            text-align: left;
        }
        .eval-table .max-col {
            text-align: center;
            color: #555;
        }
        .eval-table .score-col {
            text-align: center;
            font-weight: 600;
            font-size: 14px;
            min-width: 80px;
        }
        .eval-table .section-header td {
            background: #dce8ff;
            font-weight: 700;
            color: #1a3070;
            border-top: 2px solid #4e73df;
        }
        .eval-table .sub-row td:first-child {
            padding-left: 28px;
            color: #333;
        }
        .eval-table .subtotal-row td {
            background: #e8f0ff;
            font-weight: 700;
            border-top: 2px solid #4e73df;
        }
        .eval-table .total-row td {
            background: #d0e4ff;
            font-weight: 700;
            font-size: 15px;
            border-top: 2px solid #2c3e7a;
        }

        /* ── Signature Section ── */
        .signature-section {
            margin-top: 24px;
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 20px 24px;
        }
        .signature-section .section-title {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 16px;
            border-bottom: 1px dashed #aaa;
            padding-bottom: 6px;
        }
        .sig-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
        }
        .sig-box {
            text-align: center;
        }
        .sig-line {
            border-bottom: 1.5px solid #333;
            height: 50px;
            margin-bottom: 6px;
            margin-top: 8px;
        }
        .sig-label {
            font-size: 12px;
            color: #444;
            line-height: 1.8;
        }
        .sig-label strong {
            color: #000;
            font-size: 13px;
        }

        /* ── Footer note ── */
        .doc-footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
            font-size: 11px;
            color: #777;
            text-align: center;
        }

        /* ── Print ── */
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
            @page { margin: 1.5cm; size: A4; }
        }
    </style>
</head>
<body>

    <!-- Print button (hidden when printing) -->
    <div class="no-print" style="margin-bottom:16px; display:flex; gap:10px;">
        <button onclick="window.print()" style="padding:8px 20px; background:#2c3e7a; color:white; border:none; border-radius:6px; font-size:14px; cursor:pointer;">
            🖨 พิมพ์ / บันทึก PDF
        </button>
        <button onclick="window.close()" style="padding:8px 20px; background:#6c757d; color:white; border:none; border-radius:6px; font-size:14px; cursor:pointer;">
            ✕ ปิด
        </button>
    </div>

    @php
        $roleLabels = [
            'advisor'    => 'อาจารย์ที่ปรึกษา',
            'committee1' => 'กรรมการคนที่ 1',
            'committee2' => 'กรรมการคนที่ 2',
            'committee3' => 'กรรมการคนที่ 3',
        ];
        $maxScore = $role === 'advisor' ? $criteria->getAdvisorMax() : $criteria->getCommitteeMax();
        $evaluatorName = trim(($user->firstname_user ?? '') . ' ' . ($user->lastname_user ?? '')) ?: $user->username_user;
    @endphp

    <!-- Document Header -->
    <div class="doc-header">
        <div class="dept">ภาควิชาวิทยาการคอมพิวเตอร์ คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยธรรมศาสตร์</div>
        <div class="title">ใบประเมินโครงงานพิเศษ</div>
        <div class="subtitle">
            <span class="role-pill">{{ $roleLabels[$role] ?? $role }}</span>
            &nbsp;|&nbsp; คะแนนเต็ม {{ $maxScore }} คะแนน
            &nbsp;|&nbsp; พิมพ์เมื่อ: {{ thaiDateTime(now()) }}
        </div>
    </div>

    <!-- Project Info -->
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
                    {{ thaiDateTime($project->exam_datetime) }}
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

    <!-- Evaluation Table -->
    <table class="eval-table">
        <thead>
            <tr>
                <th class="criteria-th" style="width:44%">เกณฑ์การประเมิน</th>
                <th style="width:8%">คะแนนเต็ม</th>
                @foreach($students as $idx => $student)
                <th>
                    {{ $student->firstname_std }} {{ $student->lastname_std }}<br>
                    <span style="font-size:11px; font-weight:400;">{{ $student->username_std }}</span>
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody>

            {{-- ส่วนที่ 1 (advisor only) --}}
            @if($role === 'advisor')
            <tr>
                <td><strong>ส่วนที่ 1:</strong> ความก้าวหน้าโครงงาน <small style="color:#777">(อาจารย์ที่ปรึกษา)</small></td>
                <td class="max-col">10</td>
                @foreach($students as $idx => $student)
                <td class="score-col">{{ $scores[$idx]['part1'] }}</td>
                @endforeach
            </tr>
            @endif

            {{-- ส่วนที่ 2 --}}
            <tr>
                <td><strong>ส่วนที่ 2:</strong> {{ $criteria->part2_label }}</td>
                <td class="max-col">{{ $criteria->part2_max }}</td>
                @foreach($students as $idx => $student)
                <td class="score-col">{{ $scores[$idx]['part2'] }}</td>
                @endforeach
            </tr>

            {{-- ส่วนที่ 3 header --}}
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

            {{-- รวมส่วนที่ 3 --}}
            <tr class="subtotal-row">
                <td>รวมส่วนที่ 3</td>
                <td class="max-col">{{ $criteria->getPart3Max() }}</td>
                @foreach($students as $idx => $student)
                <td class="score-col">
                    @php
                        $s3a = is_numeric($scores[$idx]['part3a']) ? $scores[$idx]['part3a'] : 0;
                        $s3b = is_numeric($scores[$idx]['part3b']) ? $scores[$idx]['part3b'] : 0;
                        $s3c = is_numeric($scores[$idx]['part3c']) ? $scores[$idx]['part3c'] : 0;
                        $sub3 = $s3a + $s3b + $s3c;
                    @endphp
                    {{ ($scores[$idx]['part3a'] === '-') ? '-' : number_format($sub3, 2) }}
                </td>
                @endforeach
            </tr>

            {{-- คะแนนรวม --}}
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
                        $hasScores = $scores[$idx]['part2'] !== '-';
                    @endphp
                    <strong>{{ $hasScores ? number_format($grand, 2) : '-' }}</strong>
                </td>
                @endforeach
            </tr>

        </tbody>
    </table>

    <!-- Comment Section -->
    <div style="margin-top:16px;border:1px solid #ccc;border-radius:6px;padding:12px 16px;">
        <div style="font-size:13px;font-weight:700;margin-bottom:8px;color:#333;">
            ความเห็นเพิ่มเติม / ข้อเสนอแนะจากอาจารย์
        </div>
        @if(!empty($comment))
            <div style="font-size:13px;line-height:1.7;white-space:pre-wrap;color:#111;">{{ $comment }}</div>
        @else
            <div style="height:56px;border-bottom:1px dashed #bbb;"></div>
            <div style="height:28px;border-bottom:1px dashed #bbb;margin-top:8px;"></div>
        @endif
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="section-title">ลายเซ็นรับรองคะแนน (ลงนามนอกระบบ)</div>
        <div class="sig-grid">
            <!-- ผู้ประเมิน -->
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-label">
                    <strong>{{ $evaluatorName }}</strong><br>
                    {{ $roleLabels[$role] ?? $role }}<br>
                    วันที่ ............/............/............
                </div>
            </div>
            <!-- ประธาน/หัวหน้าภาค -->
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

    <!-- Footer -->
    <div class="doc-footer">
        เอกสารนี้ผลิตจากระบบ CSTU SPACE &nbsp;|&nbsp; ใช้สำหรับลงลายเซ็นนอกระบบเท่านั้น &nbsp;|&nbsp;
        โครงงาน {{ $project->project_code }} &nbsp;|&nbsp; {{ $roleLabels[$role] ?? $role }}: {{ $evaluatorName }}
    </div>

</body>
</html>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สรุปรายวิชา {{ $subject }} | CSTU SPACE</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'TH SarabunPSK', 'Tahoma', sans-serif; font-size: 12pt; color: #111; background: #fff; }
        h1 { font-size: 16pt; font-weight: bold; text-align: center; margin-bottom: 4px; }
        .subtitle { text-align: center; color: #555; font-size: 11pt; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; font-size: 10pt; }
        thead th {
            background: #1F4E79;
            color: white;
            padding: 5px 6px;
            text-align: center;
            border: 1px solid #0f3a5c;
            font-weight: bold;
        }
        tbody td { padding: 4px 6px; border: 1px solid #ccc; vertical-align: top; }
        tbody tr:nth-child(even) td { background: #eaf0f8; }
        .code { font-family: monospace; font-size: 9pt; color: #1a3c5e; }
        .no-exam { color: #c0392b; font-size: 9pt; }
        .footer { margin-top: 16px; font-size: 9pt; color: #888; text-align: right; }
        @media print {
            .no-print { display: none !important; }
            @page { margin: 1.5cm; size: A4 landscape; }
        }
    </style>
</head>
<body>

<div class="no-print" style="padding:12px; background:#f0f4ff;">
    <button onclick="window.print()" style="padding:6px 18px; background:#1F4E79; color:white; border:none; border-radius:6px; cursor:pointer; font-size:13pt;">
        🖨 พิมพ์ / บันทึกเป็น PDF
    </button>
    <a href="{{ route('coordinator.subject-summary.index') }}" style="margin-left:12px; color:#555; font-size:11pt;">← กลับ</a>
</div>

<h1>สรุปข้อมูลโครงงาน — {{ $subject === 'all' ? 'ทุกรายวิชา' : $subject }}</h1>
<div class="subtitle">เทอม {{ $semester }} ปีการศึกษา {{ $year }} &nbsp;|&nbsp; พิมพ์เมื่อ {{ now()->format('d/m/Y H:i') }}</div>

@php
    $statusLabels = [
        'not_proposed'=>'ยังไม่เสนอ','pending'=>'รออนุมัติ','approved'=>'อนุมัติแล้ว',
        'rejected'=>'ถูกปฏิเสธ','in_progress'=>'กำลังดำเนิน','submitted'=>'ส่งงานแล้ว',
        'late_submission'=>'ส่งล่าช้า',
    ];
@endphp

<table>
    <thead>
        <tr>
            <th>No.</th>
            <th>รหัสโครงงาน</th>
            <th>วิชา</th>
            <th>ชื่อโครงงาน</th>
            <th>นักศึกษา</th>
            <th>ที่ปรึกษา</th>
            <th>กรรมการ</th>
            <th>วันสอบ</th>
            <th>ห้อง</th>
            <th>สถานะ</th>
        </tr>
    </thead>
    <tbody>
        @foreach($projects as $no => $p)
        @php
            $m1 = $p->first_member;
            $m2 = $p->second_member;
            $es = $p->examSchedule;
            $comms = $p->committeeLecturers->map(fn($c) => $c->user_code)->implode(', ');
        @endphp
        <tr>
            <td style="text-align:center;">{{ $no+1 }}</td>
            <td><span class="code">{{ $p->project_code }}</span></td>
            <td style="text-align:center;">{{ $p->group->subject_code ?? '' }}</td>
            <td>{{ $p->project_name }}</td>
            <td>
                @if($m1) {{ $m1->firstname_std }} {{ $m1->lastname_std }}<br>
                    <span style="font-size:8.5pt;color:#555;">{{ $m1->username_std }}</span>
                @endif
                @if($m2)<br>{{ $m2->firstname_std }} {{ $m2->lastname_std }}<br>
                    <span style="font-size:8.5pt;color:#555;">{{ $m2->username_std }}</span>
                @endif
            </td>
            <td class="code">{{ $p->advisorLecturer?->user_code ?? '-' }}</td>
            <td class="code">{{ $comms ?: '-' }}</td>
            <td>
                @if($es)
                    {{ $es->ex_start_time->format('d/m/Y') }}<br>
                    <span style="font-size:9pt;">{{ $es->ex_start_time->format('H:i') }}–{{ $es->ex_end_time->format('H:i') }}</span>
                @else
                    <span class="no-exam">ยังไม่กำหนด</span>
                @endif
            </td>
            <td>{{ $es?->location ?? '-' }}</td>
            <td style="text-align:center;">{{ $statusLabels[$p->status_project] ?? $p->status_project }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">รวม {{ $projects->count() }} โครงงาน</div>

</body>
</html>

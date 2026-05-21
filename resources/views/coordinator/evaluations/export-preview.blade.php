@extends('layouts.app')

@section('title', 'สรุปคะแนนและส่งออกคะแนน | CSTU SPACE')

@push('styles')
<style>
/* ── Base table ─────────────────────────── */
.preview-table {
    table-layout: fixed;
    width: 100%;
    font-size: .78rem;
    border-collapse: collapse;
}
.preview-table thead th {
    background: #1e40af;
    color: #fff;
    font-weight: 600;
    font-size: .72rem;
    text-align: center;
    vertical-align: middle;
    padding: .45rem .35rem;
    white-space: normal;
    line-height: 1.3;
    word-break: break-word;
}
.preview-table tbody td {
    vertical-align: middle;
    padding: .38rem .4rem;
    overflow-wrap: break-word;
    word-break: break-word;
    line-height: 1.35;
}
.preview-table tbody tr { transition: background .1s; }
.preview-table tbody tr:nth-child(even) { background: #eff6ff; }
.preview-table tbody tr:hover           { background: #dbeafe !important; }

/* ── CS303 สรุปรวม ── */
.sheet-303m thead th                    { background: #166534; }
.sheet-303m tbody tr:nth-child(even)    { background: #f0fdf4; }
.sheet-303m tbody tr:hover              { background: #dcfce7 !important; }

/* ── CS403 แยกตามอาจารย์ ── */
.sheet-403d thead th                    { background: #6d28d9; }
.sheet-403d tbody tr:nth-child(even)    { background: #f5f3ff; }
.sheet-403d tbody tr:hover              { background: #ede9fe !important; }

/* ── CS403 สรุปรวม ── */
.sheet-403s thead th                    { background: #9d174d; }
.sheet-403s tbody tr:nth-child(even)    { background: #fdf2f8; }
.sheet-403s tbody tr:hover              { background: #fce7f3 !important; }

/* ── Column widths: detail (13 cols) ─────── */
.preview-table.detail col.c-no    { width: 2%; }
.preview-table.detail col.c-code  { width: 9%; }
.preview-table.detail col.c-name  { width: 11%; }
.preview-table.detail col.c-pcode { width: 8%; }
.preview-table.detail col.c-pname { width: 11%; }
.preview-table.detail col.c-score { width: 6%; }   /* ×5 = 30% */
.preview-table.detail col.c-total { width: 5%; }
.preview-table.detail col.c-eval  { width: 13%; }
.preview-table.detail col.c-role  { width: 11%; }

/* ── Column widths: summary (12 cols) ────── */
.preview-table.summary col.c-no    { width: 2%; }
.preview-table.summary col.c-code  { width: 9%; }
.preview-table.summary col.c-name  { width: 13%; }
.preview-table.summary col.c-pcode { width: 9%; }
.preview-table.summary col.c-pname { width: 15%; }
.preview-table.summary col.c-score { width: 7%; }  /* ×5 = 35% */
.preview-table.summary col.c-total { width: 8%; }
.preview-table.summary col.c-cnt   { width: 7%; }

/* ── Code columns: no wrap ──────────────── */
/* col position 2 = รหัสนักศึกษา, col 4 = รหัสโครงงาน */
.preview-table thead th:nth-child(2),
.preview-table thead th:nth-child(4),
.preview-table tbody td:nth-child(2),
.preview-table tbody td:nth-child(4) {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ── Misc ────────────────────────────────── */
.score-cell  { font-variant-numeric: tabular-nums; letter-spacing: .01em; }
.empty-table { padding: 3rem 0; }
.refresh-badge { font-size: .72rem; cursor: pointer; user-select: none; }
.pulse { animation: pulse 1.4s ease-in-out infinite; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-2">

    {{-- Page Header --}}
    <div style="background:white;border-radius:var(--border-radius);padding:2rem;margin-bottom:2rem;box-shadow:var(--shadow-light);">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 style="color:#2c3e50;font-weight:700;font-size:2rem;margin-bottom:.5rem;">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>สรุปคะแนนและส่งออกคะแนน
                </h2>
                <p class="mb-0 opacity-75">Preview real-time · 4 sheet แยก CS303 / CS403 แยกตามอาจารย์ / สรุปรวม</p>
            </div>
        <div class="d-flex flex-column align-items-end gap-2">
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('coordinator.evaluations.export', request()->query()) }}"
                   id="exportBtn" class="btn btn-success fw-bold">
                    <i class="bi bi-download me-2"></i>Export .xlsx
                </a>
                <a href="{{ route('coordinator.evaluations.index') }}" class="btn" style="background:#f8f9fa;color:#2c3e50;font-weight:600;display:inline-flex;align-items:center;gap:.5rem;border-radius:10px;">
                    <i class="bi bi-arrow-left"></i><span>กลับรายการ</span>
                </a>
                <a href="{{ route('coordinator.dashboard') }}" class="btn" style="background:#f8f9fa;color:#2c3e50;font-weight:600;display:inline-flex;align-items:center;gap:.5rem;border-radius:10px;">
                    <i class="bi bi-house"></i><span>Dashboard</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle refresh-badge" id="lastUpdated">
                    <i class="bi bi-clock me-1"></i>โหลดครั้งแรก
                </span>
                <span class="badge bg-info-subtle text-info border border-info-subtle refresh-badge"
                      id="countdownBadge" title="คลิกเพื่อรีเฟรชทันที">
                    <i class="bi bi-arrow-clockwise me-1" id="refreshIcon"></i>รีเฟรชใน <span id="countdown">30</span>s
                </span>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="autoRefreshToggle" checked>
                    <label class="form-check-label small text-muted" for="autoRefreshToggle">auto</label>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('coordinator.evaluations.export-preview') }}" id="filterForm">
                <div class="row g-2 align-items-end">
                    <div class="col-sm-6 col-md-3">
                        <label class="form-label fw-semibold small mb-1">ปีการศึกษา</label>
                        <select name="year" class="form-select form-select-sm auto-submit">
                            <option value="">ทั้งหมด</option>
                            @foreach($years as $yr)
                                <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <label class="form-label fw-semibold small mb-1">ภาคเรียน</label>
                        <select name="semester" class="form-select form-select-sm auto-submit">
                            <option value="">ทั้งหมด</option>
                            <option value="1" {{ request('semester') == '1' ? 'selected' : '' }}>เทอม 1</option>
                            <option value="2" {{ request('semester') == '2' ? 'selected' : '' }}>เทอม 2</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label fw-semibold small mb-1 d-block">เรียงตาม</label>
                        <input type="hidden" name="sort" id="sortInput" value="{{ request('sort', 'code') }}">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" onclick="setSort('code')"
                                class="btn {{ request('sort','code')=='code' ? 'btn-primary' : 'btn-outline-primary' }} sort-btn"
                                data-sort="code">
                                <i class="bi bi-hash me-1"></i>รหัสโครงงาน
                            </button>
                            <button type="button" onclick="setSort('student')"
                                class="btn {{ request('sort')=='student' ? 'btn-primary' : 'btn-outline-primary' }} sort-btn"
                                data-sort="student">
                                <i class="bi bi-person me-1"></i>รหัสนักศึกษา
                            </button>
                            <button type="button" onclick="setSort('time')"
                                class="btn {{ request('sort')=='time' ? 'btn-primary' : 'btn-outline-primary' }} sort-btn"
                                data-sort="time">
                                <i class="bi bi-clock me-1"></i>เวลาที่ประเมิน
                            </button>
                        </div>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('coordinator.evaluations.export-preview') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-lg me-1"></i>ล้าง
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Stats --}}
    @php
        $totalStudents = count($subjects['cs303']['rowsSummary']) + count($subjects['cs403']['rowsSummary']);
        $totalEvals    = count($subjects['cs303']['rowsDetail'])  + count($subjects['cs403']['rowsDetail']);
        $allSummary    = array_merge($subjects['cs303']['rowsSummary'], $subjects['cs403']['rowsSummary']);
        $avgTotal      = $totalStudents > 0
            ? round(array_sum(array_column($allSummary, 9)) / $totalStudents, 2)
            : 0;
    @endphp
    <div class="row g-3 mb-3">
        @foreach([
            ['CS303', count($subjects['cs303']['rowsSummary']), 'primary',   'bi-people-fill'],
            ['CS403', count($subjects['cs403']['rowsSummary']), 'purple',    'bi-people-fill', '#7c3aed', '#f5f3ff'],
            ['การประเมินทั้งหมด', $totalEvals,   'info',    'bi-clipboard-check-fill'],
            ['คะแนนเฉลี่ยรวม',   $avgTotal,     'success', 'bi-bar-chart-fill'],
        ] as $stat)
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2 d-flex align-items-center gap-2">
                    @if(isset($stat[4]))
                    <div style="width:36px;height:36px;border-radius:8px;background:{{ $stat[5] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi {{ $stat[3] }}" style="color:{{ $stat[4] }}"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="color:{{ $stat[4] }}" id="stat-{{ $loop->index }}">{{ $stat[1] }}</div>
                        <div class="small text-muted">นักศึกษา {{ $stat[0] }}</div>
                    </div>
                    @else
                    <div style="width:36px;height:36px;border-radius:8px;background:var(--bs-{{ $stat[2] }}-bg-subtle);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi {{ $stat[3] }} text-{{ $stat[2] }}"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-{{ $stat[2] }}" id="stat-{{ $loop->index }}">{{ $stat[1] }}</div>
                        <div class="small text-muted">{{ $stat[0] }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Tabs --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-0 d-flex justify-content-between align-items-center">
            <ul class="nav nav-tabs card-header-tabs border-0" id="sheetTabs" role="tablist">
                @php
                    $tabs = [
                        ['id'=>'s1','target'=>'#tab-cs303d','label'=>'CS303 — แยกตามอาจารย์','badge'=>'primary','count'=>count($subjects['cs303']['rowsDetail']),'color'=>'#1e40af'],
                        ['id'=>'s2','target'=>'#tab-cs303m','label'=>'CS303 — สรุปรวม',        'badge'=>'success','count'=>count($subjects['cs303']['rowsSummary']),'color'=>'#166534'],
                        ['id'=>'s3','target'=>'#tab-cs403d','label'=>'CS403 — แยกตามอาจารย์','badge'=>'purple', 'count'=>count($subjects['cs403']['rowsDetail']),'color'=>'#7c3aed'],
                        ['id'=>'s4','target'=>'#tab-cs403m','label'=>'CS403 — สรุปรวม',        'badge'=>'pink',   'count'=>count($subjects['cs403']['rowsSummary']),'color'=>'#9d174d'],
                    ];
                @endphp
                @foreach($tabs as $i => $tab)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $i===0?'active':'' }} fw-semibold small"
                            data-bs-toggle="tab" data-bs-target="{{ $tab['target'] }}"
                            type="button" role="tab">
                        <i class="bi bi-table me-1" style="color:{{ $tab['color'] }}"></i>{{ $tab['label'] }}
                        <span class="badge ms-1" style="font-size:.68rem;background:{{ $tab['color'] }}20;color:{{ $tab['color'] }};border:1px solid {{ $tab['color'] }}40;"
                              id="cnt-{{ $tab['id'] }}">{{ $tab['count'] }}</span>
                    </button>
                </li>
                @endforeach
            </ul>
            <span class="badge bg-warning-subtle text-warning border border-warning-subtle refresh-badge me-2 d-none" id="refreshingBadge">
                <i class="bi bi-arrow-clockwise pulse me-1"></i>กำลังอัปเดต...
            </span>
        </div>

        <div class="tab-content">

            @php
                $tabDefs = [
                    ['tab-cs303d', 'preview-table detail',   $subjects['cs303']['headersDetail'],  $subjects['cs303']['rowsDetail'],  true,  'show active'],
                    ['tab-cs303m', 'preview-table summary sheet-303m', $subjects['cs303']['headersSummary'], $subjects['cs303']['rowsSummary'], false, ''],
                    ['tab-cs403d', 'preview-table detail sheet-403d',  $subjects['cs403']['headersDetail'],  $subjects['cs403']['rowsDetail'],  true,  ''],
                    ['tab-cs403m', 'preview-table summary sheet-403s', $subjects['cs403']['headersSummary'], $subjects['cs403']['rowsSummary'], false, ''],
                ];
            @endphp

            @foreach($tabDefs as $t)
            @php [$tabId, $tableClass, $headers, $rows, $isDetail, $active] = $t; @endphp
            <div class="tab-pane fade {{ $active }} p-0" id="{{ $tabId }}" role="tabpanel">
                <table class="table table-bordered {{ $tableClass }} mb-0">

                    {{-- colgroup defines proportional column widths --}}
                    <colgroup>
                        <col class="c-no">
                        <col class="c-code">
                        <col class="c-name">
                        <col class="c-pcode">
                        <col class="c-pname">
                        <col class="c-score"><col class="c-score"><col class="c-score">
                        <col class="c-score"><col class="c-score">
                        <col class="c-total">
                        @if($isDetail)
                            <col class="c-eval"><col class="c-role">
                        @else
                            <col class="c-cnt">
                        @endif
                    </colgroup>

                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            @foreach($headers as $h)
                                <th class="text-center">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody id="tbody-{{ $tabId }}">
                        @forelse($rows as $idx => $row)
                        <tr>
                            <td class="text-center text-muted" style="font-size:.72rem;">{{ $idx + 1 }}</td>
                            @foreach($row as $i => $cell)
                                @if(in_array($i, [4,5,6,7,8,9]))
                                    <td class="text-center fw-semibold score-cell">{{ $cell ?? '—' }}</td>
                                @elseif(!$isDetail && $i === 10)
                                    <td class="text-center">
                                        <span class="badge bg-info-subtle text-info border border-info-subtle">{{ $cell }}</span>
                                    </td>
                                @else
                                    <td>{{ $cell ?? '—' }}</td>
                                @endif
                            @endforeach
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ count($headers) + 1 }}" class="text-center text-muted empty-table">
                                <i class="bi bi-inbox display-4 d-block mb-2 opacity-25"></i>ไม่พบข้อมูล
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endforeach

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const REFRESH_INTERVAL = 30;
const DATA_URL   = '{{ route("coordinator.evaluations.export-data") }}';
const EXPORT_URL = '{{ route("coordinator.evaluations.export") }}';

let countdown = REFRESH_INTERVAL, autoOn = true, fetching = false;
let timer = null;

const $countdown = document.getElementById('countdown');
const $lastUpd   = document.getElementById('lastUpdated');
const $cdbadge   = document.getElementById('countdownBadge');
const $spinning  = document.getElementById('refreshingBadge');
const $icon      = document.getElementById('refreshIcon');
const $toggle    = document.getElementById('autoRefreshToggle');
const $exportBtn = document.getElementById('exportBtn');

// tab body map: [tabId, isDetail]
const TAB_MAP = [
    ['tab-cs303d', 'cs303', 'rowsDetail',   12, false],
    ['tab-cs303m', 'cs303', 'rowsSummary',  11, true],
    ['tab-cs403d', 'cs403', 'rowsDetail',   12, false],
    ['tab-cs403m', 'cs403', 'rowsSummary',  11, true],
];
const CNT_IDS = ['cnt-s1','cnt-s2','cnt-s3','cnt-s4'];

function getQuery() {
    return new URLSearchParams(window.location.search).toString();
}

function buildRow(row, colCount, isSummary) {
    let cells = `<td class="text-center text-muted small"></td>`;
    row.forEach((cell, i) => {
        if (!isSummary && i === 10) {
            cells += `<td class="px-3 text-center"><span class="badge bg-info-subtle text-info border border-info-subtle">${cell}</span></td>`;
        } else if ([4,5,6,7,8,9].includes(i)) {
            cells += `<td class="text-center fw-semibold px-3">${cell ?? '—'}</td>`;
        } else {
            cells += `<td class="px-3">${cell ?? '—'}</td>`;
        }
    });
    return `<tr>${cells}</tr>`;
}

function renderBody(tbodyId, rows, colCount, isSummary) {
    const el = document.getElementById(tbodyId);
    if (!el) return;
    if (!rows || rows.length === 0) {
        el.innerHTML = `<tr><td colspan="${colCount + 1}" class="text-center text-muted py-5">
            <i class="bi bi-inbox display-4 d-block mb-2 opacity-25"></i>ไม่พบข้อมูล</td></tr>`;
        return;
    }
    el.innerHTML = rows.map((row, ri) => {
        let cells = `<td class="text-center text-muted small">${ri + 1}</td>`;
        row.forEach((cell, i) => {
            if (isSummary && i === 10) {
                cells += `<td class="px-3 text-center"><span class="badge bg-info-subtle text-info border border-info-subtle">${cell}</span></td>`;
            } else if ([4,5,6,7,8,9].includes(i)) {
                cells += `<td class="text-center fw-semibold px-3">${cell ?? '—'}</td>`;
            } else {
                cells += `<td class="px-3">${cell ?? '—'}</td>`;
            }
        });
        return `<tr>${cells}</tr>`;
    }).join('');
}

async function fetchData() {
    if (fetching) return;
    fetching = true;
    $spinning.classList.remove('d-none');
    $icon.classList.add('pulse');
    try {
        const qs   = getQuery();
        const resp = await fetch(`${DATA_URL}?${qs}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        if (!resp.ok) throw new Error(resp.status);
        const data = await resp.json();

        TAB_MAP.forEach(([tabId, subKey, rowKey, cols, isSummary], idx) => {
            const rows = data[subKey]?.[rowKey] ?? [];
            renderBody(`tbody-${tabId}`, rows, cols, isSummary);
            if (CNT_IDS[idx]) document.getElementById(CNT_IDS[idx]).textContent = rows.length;
        });

        // update stats
        const cs303s = data.cs303?.rowsSummary ?? [];
        const cs403s = data.cs403?.rowsSummary ?? [];
        const cs303d = data.cs303?.rowsDetail  ?? [];
        const cs403d = data.cs403?.rowsDetail  ?? [];
        const allS   = [...cs303s, ...cs403s];
        const avg    = allS.length
            ? (allS.reduce((a, r) => a + (parseFloat(r[9]) || 0), 0) / allS.length).toFixed(2)
            : 0;

        document.getElementById('stat-0').textContent = cs303s.length;
        document.getElementById('stat-1').textContent = cs403s.length;
        document.getElementById('stat-2').textContent = cs303d.length + cs403d.length;
        document.getElementById('stat-3').textContent = avg;

        $exportBtn.href = `${EXPORT_URL}?${qs}`;
        $lastUpd.innerHTML = `<i class="bi bi-check-circle me-1 text-success"></i>อัปเดต: ${data.updated_at}`;
        $lastUpd.className = 'badge bg-success-subtle text-success border border-success-subtle refresh-badge';
    } catch {
        $lastUpd.innerHTML = `<i class="bi bi-exclamation-triangle me-1"></i>รีเฟรชไม่สำเร็จ`;
        $lastUpd.className = 'badge bg-warning-subtle text-warning border border-warning-subtle refresh-badge';
    } finally {
        fetching = false;
        $spinning.classList.add('d-none');
        $icon.classList.remove('pulse');
        countdown = REFRESH_INTERVAL;
    }
}

timer = setInterval(() => {
    if (!autoOn) return;
    countdown--;
    $countdown.textContent = countdown;
    if (countdown <= 0) fetchData();
}, 1000);

$toggle.addEventListener('change', () => {
    autoOn = $toggle.checked;
    $cdbadge.style.opacity = autoOn ? '1' : '0.35';
    if (autoOn) { countdown = REFRESH_INTERVAL; $countdown.textContent = countdown; }
});

$cdbadge.addEventListener('click', () => { countdown = 0; });

document.querySelectorAll('.auto-submit').forEach(el => {
    el.addEventListener('change', () => document.getElementById('filterForm').submit());
});

function setSort(val) {
    document.getElementById('sortInput').value = val;
    document.querySelectorAll('.sort-btn').forEach(btn => {
        const active = btn.dataset.sort === val;
        btn.classList.toggle('btn-primary', active);
        btn.classList.toggle('btn-outline-primary', !active);
    });
    document.getElementById('filterForm').submit();
}
</script>
@endpush

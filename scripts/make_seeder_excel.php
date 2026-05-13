<?php
/**
 * Generate test Excel files from seeder data.
 * Run: docker exec cstu_space_app php scripts/make_seeder_excel.php
 *
 * Output:
 *   public/templates/test_students.xlsx  — CS303--Students + CS403--Students sheets
 *   public/templates/test_users.xlsx     — Lecturers sheet (username/user_code/firstname/lastname/email)
 */

// ─── Student data (from StudentTableSeeder) ──────────────────────────────────
// format: [prefix, fullname, student_id, email, phone, course_type_text, sheet]
$students = [
    // CS303
    ['นาย',    'หฤษฎ์ อัชฌาวนิชย์',     '6509650757', 'haritch.utc@dome.tu.ac.th',     '080-000-0001', 'ภาคปกติ',   'CS303'],
    ['นางสาว', 'ณัชชา วัฒนบำเพ็ญ',      '6509611676', 'natcha.wattan@dome.tu.ac.th',   '080-000-0002', 'ภาคปกติ',   'CS303'],
    ['นาย',    'ภูริณัฐ มุสิกานนท์',     '6509650658', 'phurinat.mus@dome.tu.ac.th',    '080-000-0003', 'ภาคปกติ',   'CS303'],
    ['นาย',    'สมชาย ใจดี',             '6509650001', '6509650001@dome.tu.ac.th',       '080-000-0004', 'ภาคปกติ',   'CS303'],
    ['นางสาว', 'สมหญิง รักเรียน',        '6509650002', '6509650002@dome.tu.ac.th',       '080-000-0005', 'ภาคพิเศษ',  'CS303'],
    ['นาย',    'วิทยา ศรีสุข',           '6509650003', '6509650003@dome.tu.ac.th',       '080-000-0006', 'ภาคปกติ',   'CS303'],
    ['นาย',    'ชัยชนะ มั่นคง',          '6509650004', '6509650004@dome.tu.ac.th',       '080-000-0007', 'ภาคพิเศษ',  'CS303'],
    // CS403
    ['นาย',    'ปรีชา เจริญ',            '6509650005', '6509650005@dome.tu.ac.th',       '080-000-0008', 'ภาคปกติ',   'CS403'],
];

// ─── User/Lecturer data (from UserTableSeeder) ────────────────────────────────
// format: [prefix, firstname, lastname, username, user_code, email, role_label]
$users = [
    ['อ.ดร.', 'เด่นดวง',     'ประดับสุวรรณ',  'denduang',  'ddp', 'denduang@tu.ac.th',   'Lecturer'],
    ['อ.',     'เสาวลักษณ์',  'วรรธนาภา',      'wsaowalu',  'scw', 'wsaowalu@tu.ac.th',   'Lecturer'],
    ['อ.ดร.', 'ทรงศักดิ์',   'รองวิริยะพานิช','rongviri',  'ssr', 'rongviri@tu.ac.th',   'Lecturer'],
    ['อ.',     'ธนาธร',       'ทะนานทอง',      'tanatorn',  'tnt', 'tanatorn@tu.ac.th',   'Lecturer'],
    ['อ.ดร.', 'ปกรณ์',       'ลี้สุทธิพรชัย', 'pakornl',   'pkl', 'pakornl@tu.ac.th',    'Lecturer'],
    ['อ.ดร.', 'ประภาพร',     'รัตนธำรง',      'rattanat',  'ppr', 'rattanat@tu.ac.th',   'Lecturer'],
    ['อ.',     'วิรัตน์',     'จารีวงศ์ไพบูลย์','wirat',    'wjr', 'wirat@tu.ac.th',      'Lecturer'],
    ['อ.',     'วิลาวรรณ',    'รักผกาวงศ์',    'rwilawan',  'wlr', 'rwilawan@tu.ac.th',   'Lecturer'],
    ['อ.',     'อรจิรา',      'สิทธิศักดิ์',   'onjira',    'ojs', 'onjira@tu.ac.th',     'Lecturer'],
    ['อ.ดร.', 'ภัคพร',       'เสาร์ฝืน',      'pakkp',     'pkp', 'pakkp@tu.ac.th',      'Lecturer'],
    ['อ.ดร.', 'กษิดิศ',      'ชาญเชี่ยว',     'ckasidit',  'kdc', 'ckasidit@tu.ac.th',   'Lecturer'],
    ['อ.',     'ฐาปนา',       'บุญชู',         'thapanab',  'tpb', 'thapanab@tu.ac.th',   'Lecturer'],
    ['อ.ดร.', 'ปกป้อง',      'ส่องเมือง',     'pokpongs',  'pps', 'pokpongs@tu.ac.th',   'Lecturer'],
    ['อ.',     'ลัมพาพรรณ',   'พันธุ์ซูจิตร์', 'lumpapun',  'lpp', 'lumpapun@tu.ac.th',   'Lecturer'],
    ['อ.',     'วนิดา',       'พฤทธิวิทยา',   'pwanida',   'wdp', 'pwanida@tu.ac.th',    'Lecturer'],
    ['อ.',     'นุชซากร',     'งามเสาวรส',     'nnuchako',  'nng', 'nnuchako@tu.ac.th',   'Lecturer'],
    ['อ.ดร.', 'สิริกันยา',   'นิลพานิช',      'nsirikun',  'skn', 'nsirikun@tu.ac.th',   'Lecturer'],
    ['อ.ดร.', 'ศาตนาฏ',      'กิจศิรานุวัตร', 'satanat',   'snk', 'satanat@tu.ac.th',    'Lecturer'],
    ['อ.ดร.', 'นวฤกษ์',      'ชลารักษ์',      'nawarerk',  'nrc', 'nawarerk@tu.ac.th',   'Lecturer'],
];

// ─── XLSX builder (minimal OOXML) ─────────────────────────────────────────────

function buildXlsx(array $sheets): string
{
    // $sheets = [ 'SheetName' => [ [col0, col1, ...], ... ] ]

    // Collect all strings into shared pool
    $shared = [];
    $sharedIdx = [];
    $addStr = function(string $s) use (&$shared, &$sharedIdx): int {
        if (!isset($sharedIdx[$s])) {
            $sharedIdx[$s] = count($shared);
            $shared[] = $s;
        }
        return $sharedIdx[$s];
    };

    // Pre-scan all cells
    foreach ($sheets as $rows) {
        foreach ($rows as $row) {
            foreach ($row as $cell) {
                $addStr((string)$cell);
            }
        }
    }

    // Build sharedStrings.xml
    $ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
           . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
           . ' count="' . count($shared) . '" uniqueCount="' . count($shared) . '">';
    foreach ($shared as $s) {
        $ssXml .= '<si><t xml:space="preserve">' . xmle($s) . '</t></si>';
    }
    $ssXml .= '</sst>';

    // Build each sheet XML
    $sheetXmls = [];
    foreach ($sheets as $sheetRows) {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
             . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
             . '<sheetData>';
        foreach ($sheetRows as $ri => $row) {
            $rowNum = $ri + 1;
            $xml .= '<row r="' . $rowNum . '">';
            foreach ($row as $ci => $cell) {
                $colLetter = colLetter($ci);
                $ref = $colLetter . $rowNum;
                $idx = $sharedIdx[(string)$cell];
                $xml .= '<c r="' . $ref . '" t="s"><v>' . $idx . '</v></c>';
            }
            $xml .= '</row>';
        }
        $xml .= '</sheetData></worksheet>';
        $sheetXmls[] = $xml;
    }

    $sheetNames = array_keys($sheets);
    $sheetCount = count($sheetNames);

    // workbook.xml
    $wbXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
           . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
           . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
           . '<sheets>';
    for ($i = 0; $i < $sheetCount; $i++) {
        $wbXml .= '<sheet name="' . xmle($sheetNames[$i]) . '" sheetId="' . ($i+1) . '" r:id="rId' . ($i+1) . '"/>';
    }
    $wbXml .= '</sheets></workbook>';

    // workbook.xml.rels
    $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
    for ($i = 0; $i < $sheetCount; $i++) {
        $wbRels .= '<Relationship Id="rId' . ($i+1) . '"'
                 . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"'
                 . ' Target="worksheets/sheet' . ($i+1) . '.xml"/>';
    }
    $wbRels .= '<Relationship Id="rId' . ($sheetCount+1) . '"'
             . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings"'
             . ' Target="sharedStrings.xml"/>';
    $wbRels .= '</Relationships>';

    // [Content_Types].xml
    $ct = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
        . '<Default Extension="xml"  ContentType="application/xml"/>'
        . '<Override PartName="/xl/workbook.xml"'
        . '  ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
        . '<Override PartName="/xl/sharedStrings.xml"'
        . '  ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>';
    for ($i = 0; $i < $sheetCount; $i++) {
        $ct .= '<Override PartName="/xl/worksheets/sheet' . ($i+1) . '.xml"'
             . ' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
    }
    $ct .= '</Types>';

    // _rels/.rels
    $rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
              . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
              . '<Relationship Id="rId1"'
              . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument"'
              . ' Target="xl/workbook.xml"/>'
              . '</Relationships>';

    // Assemble ZIP in memory
    $tmp = tempnam(sys_get_temp_dir(), 'xlsx_');
    $zip = new ZipArchive();
    $zip->open($tmp, ZipArchive::OVERWRITE);
    $zip->addFromString('[Content_Types].xml',            $ct);
    $zip->addFromString('_rels/.rels',                    $rootRels);
    $zip->addFromString('xl/workbook.xml',                $wbXml);
    $zip->addFromString('xl/_rels/workbook.xml.rels',     $wbRels);
    $zip->addFromString('xl/sharedStrings.xml',           $ssXml);
    for ($i = 0; $i < $sheetCount; $i++) {
        $zip->addFromString('xl/worksheets/sheet' . ($i+1) . '.xml', $sheetXmls[$i]);
    }
    $zip->close();

    $bytes = file_get_contents($tmp);
    unlink($tmp);
    return $bytes;
}

function xmle(string $s): string
{
    return htmlspecialchars($s, ENT_XML1, 'UTF-8');
}

function colLetter(int $idx): string
{
    $letters = '';
    $idx++;
    while ($idx > 0) {
        $mod = ($idx - 1) % 26;
        $letters = chr(65 + $mod) . $letters;
        $idx = (int)(($idx - $mod) / 26);
    }
    return $letters;
}

// ─── Build student Excel ──────────────────────────────────────────────────────

// Header row for each sheet
$studentHeader = ['คำนำหน้า', 'ชื่อ-นามสกุล', 'รหัสนักศึกษา', 'อีเมล', 'เบอร์โทร', 'ประเภท'];

$cs303rows = [$studentHeader];
$cs403rows = [$studentHeader];

foreach ($students as $s) {
    $row = [$s[0], $s[1], $s[2], $s[3], $s[4], $s[5]];
    if ($s[6] === 'CS303') {
        $cs303rows[] = $row;
    } else {
        $cs403rows[] = $row;
    }
}

$studentXlsx = buildXlsx([
    'CS303--Students' => $cs303rows,
    'CS403--Students' => $cs403rows,
]);

// ─── Build user/lecturer Excel ────────────────────────────────────────────────

$userHeader = ['คำนำหน้า', 'ชื่อ', 'นามสกุล', 'username', 'user_code', 'email', 'role'];
$userRows   = [$userHeader];
foreach ($users as $u) {
    $userRows[] = $u;
}

$userXlsx = buildXlsx(['Lecturers' => $userRows]);

// ─── Write files ─────────────────────────────────────────────────────────────

$outDir = __DIR__ . '/../public/templates';
if (!is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

file_put_contents($outDir . '/test_students.xlsx', $studentXlsx);
file_put_contents($outDir . '/test_users.xlsx',    $userXlsx);

echo "✓ public/templates/test_students.xlsx  (" . count($cs303rows)-1 . " CS303 + " . count($cs403rows)-1 . " CS403 students)\n";
echo "✓ public/templates/test_users.xlsx     (" . count($userRows)-1 . " lecturers)\n";

<?php
/**
 * Generate seeder_students.xlsx and seeder_users.xlsx from seeder data.
 * Run: docker exec cstu_space_app php scripts/make_seeder_xlsx.php
 */

// ─── Students data (from StudentTableSeeder) ──────────────────────────────
// col[0]=prefix  col[1]=fullname  col[2]=username_std  col[3]=email  col[4]=phone  col[5]=course_type_text
// col[0]=prefix  col[1]=fullname  col[2]=username_std  col[3]=email  col[4]=phone  col[5]=course_type_text  col[6]=password
$cs303Students = [
    ['',        'นักศึกษา ทดสอบ',    'student',    'student@cstu.ac.th',           '', 'โครงการปริญญาตรีภาคพิเศษ', 'student123'],
    ['',        'หฤษฎ์ อัชฌาวนิชย์', '6509650757', 'haritch.utc@dome.tu.ac.th',   '', 'โครงการปริญญาตรีภาคพิเศษ', '1101700338550'],
    ['นางสาว', 'ณัชชา วัฒนบำเพ็ญ',   '6509611676', 'natcha.wattan@dome.tu.ac.th', '', 'โครงการปริญญาตรีภาคพิเศษ', '1709700292985'],
    ['นาย',    'ภูริณัฐ มุสิกานนท์',  '6509650658', 'phurinat.mus@dome.tu.ac.th',  '', 'โครงการปริญญาตรีภาคพิเศษ', '1104000099105'],
];

$cs403Students = [];

// ─── Users data (from UserTableSeeder) ────────────────────────────────────
// col[0]=prefix  col[1]=firstname  col[2]=lastname  col[3]=username  col[4]=email  col[5]=user_code  col[6]=role  col[7]=password
$users = [
    // System accounts
    ['',      'Admin',         'System',               'admin',       'admin@cstu.ac.th',       'ADM', 32768, 'admin123'],
    ['',      'ผู้ประสานงาน',   'ทดสอบ',                'coordinator', 'coordinator@cstu.ac.th', 'CRD', 16384, 'coordinator123'],
    ['',      'อาจารย์ที่ปรึกษา','ทดสอบ',               'advisor',     'advisor@cstu.ac.th',     'ADV',  8192, 'advisor123'],
    ['',      'เจ้าหน้าที่',    'ทดสอบ',                'staff',       'staff@cstu.ac.th',       'STF',  4096, 'staff123'],
    // Lecturers (19 คน)
    ['อ.',    'เด่นดวง',       'ประดับสุวรรณ',          'denduang',    'denduang@tu.ac.th',      'ddp',  8192, 'ddp@CSTU'],
    ['อ.',    'เสาวลักษณ์',    'วรรธนาภา',              'wsaowalu',    'wsaowalu@tu.ac.th',      'scw',  8192, 'scw@CSTU'],
    ['ผศ.',   'ทรงศักดิ์',     'รองวิริยะพานิช',         'rongviri',    'rongviri@tu.ac.th',      'ssr',  8192, 'ssr@CSTU'],
    ['อ.',    'ธนาธร',         'ทะนานทอง',              'tanatorn',    'tanatorn@tu.ac.th',      'tnt',  8192, 'tnt@CSTU'],
    ['ผศ.',   'ปกรณ์',         'ลี้สุทธิพรชัย',          'pakornl',     'pakornl@tu.ac.th',       'pkl',  8192, 'pkl@CSTU'],
    ['ผศ.',   'ประภาพร',       'รัตนธำรง',              'rattanat',    'rattanat@tu.ac.th',      'ppr',  8192, 'ppr@CSTU'],
    ['อ.',    'วิรัตน์',       'จารีวงศ์ไพบูลย์',         'wirat',       'wirat@tu.ac.th',         'wjr',  8192, 'wjr@CSTU'],
    ['ผศ.',   'วิลาวรรณ',      'รักผกาวงศ์',             'rwilawan',    'rwilawan@tu.ac.th',      'wlr',  8192, 'wlr@CSTU'],
    ['อ.',    'อรจิรา',        'สิทธิศักดิ์',            'onjira',      'onjira@tu.ac.th',        'ojs',  8192, 'ojs@CSTU'],
    ['อ.',    'ภัคพร',         'เสาร์ฝืน',               'pakkp',       'pakkp@tu.ac.th',         'pkp',  8192, 'pkp@CSTU'],
    ['ผศ.ดร.','กษิดิศ',        'ชาญเชี่ยว',              'ckasidit',    'ckasidit@tu.ac.th',      'kdc',  8192, 'kdc@CSTU'],
    ['อ.',    'ฐาปนา',         'บุญชู',                 'thapanab',    'thapanab@tu.ac.th',      'tpb',  8192, 'tpb@CSTU'],
    ['อ.',    'ปกป้อง',        'ส่องเมือง',              'pokpongs',    'pokpongs@tu.ac.th',      'pps',  8192, 'pps@CSTU'],
    ['อ.',    'ลัมพาพรรณ',     'พันธุ์ซูจิตร์',           'lumpapun',    'lumpapun@tu.ac.th',      'lpp',  8192, 'lpp@CSTU'],
    ['อ.',    'วนิดา',         'พฤทธิวิทยา',             'pwanida',     'pwanida@tu.ac.th',       'wdp',  8192, 'wdp@CSTU'],
    ['อ.',    'นุชซากร',       'งามเสาวรส',              'nnuchako',    'nnuchako@tu.ac.th',      'nng',  8192, 'nng@CSTU'],
    ['อ.',    'สิริกันยา',     'นิลพานิช',               'nsirikun',    'nsirikun@tu.ac.th',      'skn',  8192, 'skn@CSTU'],
    ['อ.',    'ศาตนาฏ',        'กิจศิรานุวัตร',           'satanat',     'satanat@tu.ac.th',       'snk',  8192, 'snk@CSTU'],
    ['อ.',    'นวฤกษ์',        'ชลารักษ์',               'nawarerk',    'nawarerk@tu.ac.th',      'nrc',  8192, 'nrc@CSTU'],
];

// ─── XLSX builder (raw ZipArchive + OOXML) ────────────────────────────────

function buildXlsx(string $path, array $sheets): void
{
    $sharedStrings = [];
    $sharedIndex   = [];

    $si = function(string $v) use (&$sharedStrings, &$sharedIndex): int {
        if (!isset($sharedIndex[$v])) { $sharedIndex[$v] = count($sharedStrings); $sharedStrings[] = $v; }
        return $sharedIndex[$v];
    };

    $sheetXmls = [];
    $sheetMeta = [];

    foreach ($sheets as $name => $rows) {
        $xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $xml .= '<sheetData>';
        foreach ($rows as $ri => $row) {
            $xml .= '<row r="' . ($ri + 1) . '">';
            foreach ($row as $ci => $val) {
                $col = colLetter($ci) . ($ri + 1);
                $val = (string)$val;
                if ($val === '') { $xml .= '<c r="' . $col . '"/>'; continue; }
                if (is_numeric($val) && !preg_match('/^0\d/', $val)) {
                    $xml .= '<c r="' . $col . '" t="n"><v>' . htmlspecialchars($val, ENT_XML1) . '</v></c>';
                } else {
                    $sidx = $si($val);
                    $xml .= '<c r="' . $col . '" t="s"><v>' . $sidx . '</v></c>';
                }
            }
            $xml .= '</row>';
        }
        $xml .= '</sheetData></worksheet>';
        $sheetXmls[] = $xml;
        $sheetMeta[] = $name;
    }

    // sharedStrings.xml
    $ssXml  = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n";
    $ssXml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($sharedStrings) . '" uniqueCount="' . count($sharedStrings) . '">';
    foreach ($sharedStrings as $s) {
        $ssXml .= '<si><t xml:space="preserve">' . htmlspecialchars($s, ENT_XML1, 'UTF-8') . '</t></si>';
    }
    $ssXml .= '</sst>';

    // workbook.xml
    $wbXml  = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n";
    $wbXml .= '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
    $wbXml .= '<sheets>';
    foreach ($sheetMeta as $i => $n) {
        $wbXml .= '<sheet name="' . htmlspecialchars($n, ENT_XML1) . '" sheetId="' . ($i + 1) . '" r:id="rId' . ($i + 1) . '"/>';
    }
    $wbXml .= '</sheets></workbook>';

    // workbook.xml.rels
    $wbRels  = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n";
    $wbRels .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
    foreach ($sheetMeta as $i => $_) {
        $wbRels .= '<Relationship Id="rId' . ($i + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . ($i + 1) . '.xml"/>';
    }
    $wbRels .= '<Relationship Id="rId' . (count($sheetMeta) + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>';
    $wbRels .= '</Relationships>';

    $contentTypes  = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n";
    $contentTypes .= '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">';
    $contentTypes .= '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>';
    $contentTypes .= '<Default Extension="xml"  ContentType="application/xml"/>';
    $contentTypes .= '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
    $contentTypes .= '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>';
    foreach ($sheetMeta as $i => $_) {
        $contentTypes .= '<Override PartName="/xl/worksheets/sheet' . ($i + 1) . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
    }
    $contentTypes .= '</Types>';

    $appRels  = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n";
    $appRels .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
    $appRels .= '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>';
    $appRels .= '</Relationships>';

    $tmp = sys_get_temp_dir() . '/' . basename($path);
    if (file_exists($tmp)) unlink($tmp);
    $zip = new ZipArchive();
    $zip->open($tmp, ZipArchive::CREATE);
    $zip->addFromString('[Content_Types].xml',         $contentTypes);
    $zip->addFromString('_rels/.rels',                 $appRels);
    $zip->addFromString('xl/workbook.xml',             $wbXml);
    $zip->addFromString('xl/_rels/workbook.xml.rels',  $wbRels);
    $zip->addFromString('xl/sharedStrings.xml',        $ssXml);
    foreach ($sheetXmls as $i => $xml) {
        $zip->addFromString('xl/worksheets/sheet' . ($i + 1) . '.xml', $xml);
    }
    $zip->close();
    copy($tmp, $path);
    unlink($tmp);
}

function colLetter(int $n): string
{
    $r = '';
    $n++;
    while ($n > 0) { $r = chr(65 + ($n - 1) % 26) . $r; $n = intdiv($n - 1, 26); }
    return $r;
}

// ─── Generate files ────────────────────────────────────────────────────────

$outDir = __DIR__ . '/../storage/app';
if (!is_dir($outDir)) mkdir($outDir, 0755, true);

// Determine output filenames — use _new suffix if original is locked
function safeOut(string $dir, string $name): string
{
    $path = $dir . '/' . $name;
    if (!file_exists($path)) return $path;
    // Test writability
    $fh = @fopen($path, 'a');
    if ($fh) { fclose($fh); return $path; }
    // File locked — use _new suffix
    $ext  = pathinfo($name, PATHINFO_EXTENSION);
    $base = pathinfo($name, PATHINFO_FILENAME);
    return $dir . '/' . $base . '_new.' . $ext;
}

// seeder_students.xlsx
$studPath = safeOut($outDir, 'seeder_students.xlsx');
buildXlsx($studPath, [
    'CS303--Students' => $cs303Students,
    'CS403--Students' => $cs403Students,
]);
echo "Created: " . basename($studPath) . " (" . count($cs303Students) . " CS303 + " . count($cs403Students) . " CS403 students)\n";

// seeder_users.xlsx
$userHeader = ['prefix', 'firstname', 'lastname', 'username', 'email', 'user_code', 'role', 'password'];
$userRows   = array_merge([$userHeader], $users);
$usersPath  = safeOut($outDir, 'seeder_users.xlsx');
buildXlsx($usersPath, [
    'Lecturers' => $userRows,
]);
echo "Created: " . basename($usersPath) . " (" . count($users) . " users)\n";

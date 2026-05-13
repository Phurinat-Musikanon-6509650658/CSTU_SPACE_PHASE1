<?php

namespace App\Helpers;

class XlsxBuilder
{
    /**
     * Write an xlsx file to $path.
     * $sheets = [ 'SheetName' => [ [col0, col1, ...], ... ], ... ]
     */
    public static function build(string $path, array $sheets): void
    {
        $sharedStrings = [];
        $sharedIndex   = [];

        $si = function(string $v) use (&$sharedStrings, &$sharedIndex): int {
            if (!isset($sharedIndex[$v])) {
                $sharedIndex[$v] = count($sharedStrings);
                $sharedStrings[] = $v;
            }
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
                    $col = self::colLetter($ci) . ($ri + 1);
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

        $ssXml  = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n";
        $ssXml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($sharedStrings) . '" uniqueCount="' . count($sharedStrings) . '">';
        foreach ($sharedStrings as $s) {
            $ssXml .= '<si><t xml:space="preserve">' . htmlspecialchars($s, ENT_XML1, 'UTF-8') . '</t></si>';
        }
        $ssXml .= '</sst>';

        $wbXml  = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n";
        $wbXml .= '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
        $wbXml .= '<sheets>';
        foreach ($sheetMeta as $i => $n) {
            $wbXml .= '<sheet name="' . htmlspecialchars($n, ENT_XML1) . '" sheetId="' . ($i + 1) . '" r:id="rId' . ($i + 1) . '"/>';
        }
        $wbXml .= '</sheets></workbook>';

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

        $tmp = sys_get_temp_dir() . '/' . uniqid('xlsx_', true);
        $zip = new \ZipArchive();
        $zip->open($tmp, \ZipArchive::CREATE);
        $zip->addFromString('[Content_Types].xml',        $contentTypes);
        $zip->addFromString('_rels/.rels',                $appRels);
        $zip->addFromString('xl/workbook.xml',            $wbXml);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);
        $zip->addFromString('xl/sharedStrings.xml',       $ssXml);
        foreach ($sheetXmls as $i => $xml) {
            $zip->addFromString('xl/worksheets/sheet' . ($i + 1) . '.xml', $xml);
        }
        $zip->close();

        if (file_exists($path)) unlink($path);
        rename($tmp, $path);
    }

    /**
     * Stream an xlsx file as a download response.
     */
    public static function download(string $filename, array $sheets): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $tmp = sys_get_temp_dir() . '/' . uniqid('xlsx_dl_', true);
        self::build($tmp, $sheets);

        return response()->streamDownload(function () use ($tmp) {
            readfile($tmp);
            @unlink($tmp);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private static function colLetter(int $n): string
    {
        $r = '';
        $n++;
        while ($n > 0) { $r = chr(65 + ($n - 1) % 26) . $r; $n = intdiv($n - 1, 26); }
        return $r;
    }
}

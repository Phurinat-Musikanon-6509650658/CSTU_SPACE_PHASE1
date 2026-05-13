<?php

namespace App\Helpers;

/**
 * Minimal XLSX reader.
 * Reads an XLSX file and returns rows as arrays of strings.
 * Requires PHP's ZipArchive and SimpleXML.
 */
class XlsxParser
{
    /**
     * Parse sheet1 of an uploaded XLSX file.
     *
     * @param  \Illuminate\Http\UploadedFile $file
     * @param  int   $skipRows  number of header rows to skip
     * @return array  [ [col0, col1, ...], ... ]
     */
    public static function parse($file, int $skipRows = 0): array
    {
        return self::parseSheet($file, null, $skipRows);
    }

    /**
     * Parse a specific sheet by name (or sheet1 if null) from an uploaded XLSX.
     *
     * @param  \Illuminate\Http\UploadedFile|string $file  uploaded file or path string
     * @param  string|null $sheetName  exact sheet name; null = first sheet
     * @param  int         $skipRows   header rows to skip (1-based: skip rows 1..skipRows)
     * @return array  [ [col0, col1, ...], ... ]
     */
    public static function parseSheet($file, ?string $sheetName = null, int $skipRows = 0): array
    {
        $path = is_string($file) ? $file : $file->getRealPath();
        $zip  = new \ZipArchive();

        if ($zip->open($path) !== true) {
            throw new \RuntimeException('ไม่สามารถเปิดไฟล์ XLSX ได้');
        }

        // Read shared strings
        $shared = [];
        $ssXml  = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml) {
            $ss = simplexml_load_string($ssXml);
            foreach ($ss->si as $si) {
                if (isset($si->t)) {
                    $shared[] = (string) $si->t;
                } else {
                    $text = '';
                    foreach ($si->r as $r) {
                        $text .= (string) $r->t;
                    }
                    $shared[] = $text;
                }
            }
        }

        // Resolve which worksheet file to use
        $worksheetPath = self::resolveSheetPath($zip, $sheetName);
        $sheetXml = $zip->getFromName($worksheetPath);
        $zip->close();

        if (!$sheetXml) {
            throw new \RuntimeException("ไม่พบ sheet '{$sheetName}' ในไฟล์ XLSX");
        }

        return self::parseSheetXml($sheetXml, $shared, $skipRows);
    }

    /**
     * List all sheet names in an XLSX file.
     */
    public static function sheetNames($file): array
    {
        $path = is_string($file) ? $file : $file->getRealPath();
        $zip  = new \ZipArchive();
        if ($zip->open($path) !== true) return [];

        $wbXml = $zip->getFromName('xl/workbook.xml');
        $zip->close();
        if (!$wbXml) return [];

        $wb    = simplexml_load_string($wbXml);
        $names = [];
        foreach ($wb->sheets->sheet as $sheet) {
            $names[] = (string) $sheet['name'];
        }
        return $names;
    }

    // ── private helpers ──────────────────────────────────────

    private static function resolveSheetPath(\ZipArchive $zip, ?string $sheetName): string
    {
        // Read workbook.xml to get sheet list
        $wbXml = $zip->getFromName('xl/workbook.xml');
        if (!$wbXml) return 'xl/worksheets/sheet1.xml';

        $wb = simplexml_load_string($wbXml);

        // If no name given, use first sheet
        if ($sheetName === null) {
            $first = $wb->sheets->sheet[0] ?? null;
            if (!$first) return 'xl/worksheets/sheet1.xml';
            $rId = (string) $first->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
        } else {
            $rId = null;
            foreach ($wb->sheets->sheet as $sheet) {
                if ((string) $sheet['name'] === $sheetName) {
                    $rId = (string) $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
                    break;
                }
            }
            if (!$rId) return 'xl/worksheets/sheet1.xml';
        }

        // Read workbook rels to map rId → target file
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if (!$relsXml) return 'xl/worksheets/sheet1.xml';

        $rels = simplexml_load_string($relsXml);
        foreach ($rels->Relationship as $rel) {
            if ((string) $rel['Id'] === $rId) {
                $target = ltrim((string) $rel['Target'], '/');
                // Target may be absolute "/xl/…" (openpyxl) or relative "worksheets/…"
                return strpos($target, 'xl/') === 0 ? $target : 'xl/' . $target;
            }
        }

        return 'xl/worksheets/sheet1.xml';
    }

    private static function parseSheetXml(string $xml, array $shared, int $skipRows): array
    {
        $sheet = simplexml_load_string($xml);
        $rows  = [];

        foreach ($sheet->sheetData->row as $row) {
            $rowNum = (int) $row['r'];
            if ($rowNum <= $skipRows) continue;

            $cells  = [];
            $maxCol = 0;

            foreach ($row->c as $c) {
                $ref  = (string) $c['r'];
                $col  = self::colIndex($ref);
                $type = (string) ($c['t'] ?? '');
                $val  = isset($c->v) ? (string) $c->v : '';

                if ($type === 's') {
                    $val = $shared[(int) $val] ?? '';
                } elseif ($type === 'inlineStr') {
                    $val = isset($c->is->t) ? (string) $c->is->t : '';
                }

                $cells[$col] = $val;
                if ($col > $maxCol) $maxCol = $col;
            }

            $result = [];
            for ($i = 0; $i <= max($maxCol, 10); $i++) {
                $result[] = $cells[$i] ?? '';
            }
            $rows[] = $result;
        }

        return $rows;
    }

    /**
     * Convert a cell reference like "AB6" to a 0-based column index.
     */
    private static function colIndex(string $ref): int
    {
        preg_match('/^([A-Z]+)/', strtoupper($ref), $m);
        $letters = $m[1] ?? 'A';
        $n = 0;
        foreach (str_split($letters) as $ch) {
            $n = $n * 26 + (ord($ch) - 64);
        }
        return $n - 1;
    }
}

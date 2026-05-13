<?php

namespace App\Helpers;

/**
 * Minimal XLSX writer using ZipArchive + OOXML.
 * No external packages required.
 *
 * Styles:
 *   0 = default / data cell
 *   1 = bold header (blue bg, white text, center, border)
 *   2 = title row  (navy bg, white text, center, border)
 *   3 = data cell with thin border
 *   4 = alt-row    (light-blue bg, border)
 *   5 = subheader  (teal bg, white text, center, border)
 */
class SimpleXlsx
{
    private array  $rows      = [];
    private array  $rowMeta   = [];   // [style, height]
    private array  $merges    = [];   // ["A1:P1", ...]
    private array  $colWidths = [];   // col_idx => width
    private array  $strings   = [];
    private array  $strMap    = [];
    private string $title     = 'Sheet1';

    public function setTitle(string $t): void { $this->title = $t; }

    public function setColWidth(int $idx, float $w): void
    {
        $this->colWidths[$idx] = $w;
    }

    public function addMerge(string $ref): void
    {
        $this->merges[] = $ref;
    }

    /**
     * @param array  $cells   [value, value, ...]  (null = empty)
     * @param int    $style   style index (0-5)
     * @param float  $height  row height (0 = default)
     */
    public function addRow(array $cells, int $style = 0, float $height = 0): void
    {
        $this->rows[]    = $cells;
        $this->rowMeta[] = ['style' => $style, 'height' => $height];
    }

    // ─── public generate ────────────────────────────────────

    public function toBinary(): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx_');
        $zip = new \ZipArchive();
        $zip->open($tmp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        // xmlSheet() must run first — it populates $this->strings (shared string pool)
        $sheetXml = $this->xmlSheet();

        $zip->addFromString('[Content_Types].xml',      $this->xmlContentTypes());
        $zip->addFromString('_rels/.rels',               $this->xmlRootRels());
        $zip->addFromString('xl/workbook.xml',           $this->xmlWorkbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels',$this->xmlWorkbookRels());
        $zip->addFromString('xl/styles.xml',             $this->xmlStyles());
        $zip->addFromString('xl/sharedStrings.xml',      $this->xmlSharedStrings());
        $zip->addFromString('xl/worksheets/sheet1.xml',  $sheetXml);

        $zip->close();
        $bin = file_get_contents($tmp);
        unlink($tmp);
        return $bin;
    }

    // ─── string index ────────────────────────────────────────

    private function strIdx(string $s): int
    {
        if (!isset($this->strMap[$s])) {
            $this->strMap[$s] = count($this->strings);
            $this->strings[]  = $s;
        }
        return $this->strMap[$s];
    }

    // ─── column letter helper ────────────────────────────────

    private static function colLetter(int $n): string
    {
        $s = '';
        do {
            $s = chr(65 + ($n % 26)) . $s;
            $n = intdiv($n, 26) - 1;
        } while ($n >= 0);
        return $s;
    }

    // ─── XML builders ────────────────────────────────────────

    private function xmlContentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml"  ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml"
    ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml"
    ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/sharedStrings.xml"
    ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
  <Override PartName="/xl/styles.xml"
    ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';
    }

    private function xmlRootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument"
    Target="xl/workbook.xml"/>
</Relationships>';
    }

    private function xmlWorkbook(): string
    {
        $t = htmlspecialchars($this->title, ENT_XML1);
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
  xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="' . $t . '" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>';
    }

    private function xmlWorkbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1"
    Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"
    Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2"
    Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings"
    Target="sharedStrings.xml"/>
  <Relationship Id="rId3"
    Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"
    Target="styles.xml"/>
</Relationships>';
    }

    private function xmlStyles(): string
    {
        // fonts[0]=default, [1]=bold white 11pt, [2]=bold white 13pt
        // fills[0]=none(req), [1]=gray125(req), [2]=blue header, [3]=navy title, [4]=alt light blue, [5]=teal subheader
        // borders[0]=none, [1]=thin all sides
        // cellXfs: 0=default, 1=header, 2=title, 3=data+border, 4=alt+border, 5=subheader
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="3">
    <font>
      <sz val="11"/><name val="Calibri"/>
    </font>
    <font>
      <b/><sz val="11"/><name val="Calibri"/><color rgb="FFFFFFFF"/>
    </font>
    <font>
      <b/><sz val="13"/><name val="Calibri"/><color rgb="FFFFFFFF"/>
    </font>
  </fonts>
  <fills count="6">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF2E75B6"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF1F4E79"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFDCE6F1"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF375623"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border><left/><right/><top/><bottom/><diagonal/></border>
    <border>
      <left style="thin"><color auto="1"/></left>
      <right style="thin"><color auto="1"/></right>
      <top style="thin"><color auto="1"/></top>
      <bottom style="thin"><color auto="1"/></bottom>
      <diagonal/>
    </border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="6">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0"
        applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center" wrapText="1"/>
    </xf>
    <xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0"
        applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center"/>
    </xf>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"
        applyBorder="1" applyAlignment="1">
      <alignment vertical="center"/>
    </xf>
    <xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0"
        applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment vertical="center"/>
    </xf>
    <xf numFmtId="0" fontId="1" fillId="5" borderId="1" xfId="0"
        applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center" wrapText="1"/>
    </xf>
  </cellXfs>
  <cellStyles count="1">
    <cellStyle name="Normal" xfId="0" builtinId="0"/>
  </cellStyles>
</styleSheet>';
    }

    private function xmlSharedStrings(): string
    {
        // Build shared strings AFTER sheet() so all strIdx calls are registered
        // We call sheet() first in toBinary, but here we just read $this->strings
        $n   = count($this->strings);
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
              . ' count="' . $n . '" uniqueCount="' . $n . '">' . "\n";
        foreach ($this->strings as $s) {
            $esc = htmlspecialchars((string) $s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $xml .= '  <si><t xml:space="preserve">' . $esc . '</t></si>' . "\n";
        }
        $xml .= '</sst>';
        return $xml;
    }

    private function xmlSheet(): string
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";
        $xml .= '  <sheetFormatPr defaultColWidth="12" defaultRowHeight="15"/>' . "\n";

        // Column widths
        if (!empty($this->colWidths)) {
            ksort($this->colWidths);
            $xml .= '  <cols>' . "\n";
            foreach ($this->colWidths as $idx => $w) {
                $col = $idx + 1;
                $xml .= '    <col min="' . $col . '" max="' . $col
                     . '" width="' . $w . '" customWidth="1"/>' . "\n";
            }
            $xml .= '  </cols>' . "\n";
        }

        $xml .= '  <sheetData>' . "\n";

        foreach ($this->rows as $rowIdx => $cells) {
            $meta   = $this->rowMeta[$rowIdx];
            $style  = $meta['style'];
            $height = $meta['height'];
            $rowNum = $rowIdx + 1;

            $htAttr = $height > 0 ? " ht=\"{$height}\" customHeight=\"1\"" : '';
            $xml   .= "    <row r=\"{$rowNum}\"{$htAttr}>\n";

            foreach ($cells as $colIdx => $val) {
                $cellRef = self::colLetter($colIdx) . $rowNum;
                if ($val === null || $val === '') {
                    $xml .= "      <c r=\"{$cellRef}\" s=\"{$style}\"/>\n";
                    continue;
                }

                if (is_numeric($val) && !is_string($val)) {
                    $xml .= "      <c r=\"{$cellRef}\" s=\"{$style}\">"
                         . "<v>{$val}</v></c>\n";
                } else {
                    $si   = $this->strIdx((string) $val);
                    $xml .= "      <c r=\"{$cellRef}\" s=\"{$style}\" t=\"s\">"
                         . "<v>{$si}</v></c>\n";
                }
            }
            $xml .= "    </row>\n";
        }

        $xml .= '  </sheetData>' . "\n";

        // Merged cells
        if (!empty($this->merges)) {
            $xml .= '  <mergeCells count="' . count($this->merges) . '">' . "\n";
            foreach ($this->merges as $ref) {
                $xml .= "    <mergeCell ref=\"{$ref}\"/>\n";
            }
            $xml .= '  </mergeCells>' . "\n";
        }

        $xml .= '</worksheet>';
        return $xml;
    }
}

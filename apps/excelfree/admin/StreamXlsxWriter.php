<?php
/**
 * Streaming XLSX writer.
 *
 * Writes arbitrary data directly to the XLSX XML format without accumulating
 * any cells in PHP memory.  Memory usage is O(1) — only the current row's
 * string data is held at a time, regardless of how many rows are written.
 *
 * How it works:
 *  1. Opens a temp file and writes the xl/worksheets/sheet1.xml incrementally.
 *  2. On save(), assembles a valid .xlsx ZIP (ZipArchive) from static metadata
 *     XML strings + the streamed sheet temp file.
 *
 * Usage:
 *   $w = new StreamXlsxWriter('/path/to/output.xlsx');
 *   $w->addRow(['Column A', 'Column B', ...]);  // header
 *   while ($row = fetch_next_row()) {
 *       $w->addRow(array_values($row));
 *   }
 *   $w->save();
 */
class StreamXlsxWriter
{
    /** @var string  Path to the output .xlsx file */
    private $outputFile;

    /** @var string  Path to the temp sheet XML file */
    private $sheetTempFile;

    /** @var resource */
    private $sheetFH;

    /** @var int  1-based row counter */
    private $rowIndex = 1;

    // ── Static XLSX metadata ────────────────────────────────────────────────

    private static $CONTENT_TYPES = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';

    private static $RELS = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';

    private static $WORKBOOK = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Sheet1" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>';

    private static $WORKBOOK_RELS = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';

    private static $STYLES = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>
  <fills count="2">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
  </fills>
  <borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>
</styleSheet>';

    // ── Construction ────────────────────────────────────────────────────────

    public function __construct($outputFile)
    {
        $this->outputFile    = $outputFile;
        $this->sheetTempFile = tempnam(sys_get_temp_dir(), 'xlsx_sheet_');
        $this->sheetFH       = fopen($this->sheetTempFile, 'w');

        fwrite($this->sheetFH,
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheetData>'
        );
    }

    // ── Public API ──────────────────────────────────────────────────────────

    /**
     * Append one row to the sheet.
     *
     * @param array $values  Flat list of scalar values (strings, numbers).
     *                       Arrays / nulls are cast to string.
     */
    public function addRow(array $values)
    {
        $ri  = $this->rowIndex;
        $xml = '<row r="' . $ri . '">';
        foreach ($values as $ci => $value) {
            $col   = self::colLetter($ci + 1);
            $coord = $col . $ri;
            if (is_numeric($value) && $value !== '' && !is_bool($value)) {
                // Numeric: write as number (no type attribute = default "n")
                $xml .= '<c r="' . $coord . '"><v>' . $value . '</v></c>';
            } else {
                // String: use inline string to avoid building sharedStrings
                $safe = htmlspecialchars((string)$value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $xml .= '<c r="' . $coord . '" t="inlineStr"><is><t>' . $safe . '</t></is></c>';
            }
        }
        $xml .= '</row>';
        fwrite($this->sheetFH, $xml);
        $this->rowIndex++;
    }

    /**
     * Finalise the sheet XML and assemble the .xlsx ZIP file.
     * The StreamXlsxWriter object cannot be used after this call.
     */
    public function save()
    {
        fwrite($this->sheetFH, '</sheetData></worksheet>');
        fclose($this->sheetFH);
        $this->sheetFH = null;

        $zip = new ZipArchive();
        if ($zip->open($this->outputFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Cannot create XLSX file: ' . $this->outputFile);
        }

        $zip->addFromString('[Content_Types].xml',        self::$CONTENT_TYPES);
        $zip->addFromString('_rels/.rels',                self::$RELS);
        $zip->addFromString('xl/workbook.xml',            self::$WORKBOOK);
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::$WORKBOOK_RELS);
        $zip->addFromString('xl/styles.xml',              self::$STYLES);
        $zip->addFile($this->sheetTempFile, 'xl/worksheets/sheet1.xml');
        $zip->close();

        unlink($this->sheetTempFile);
        $this->sheetTempFile = null;
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Convert 1-based column number to Excel letter (1→A, 26→Z, 27→AA …)
     */
    private static function colLetter($n)
    {
        $s = '';
        while ($n > 0) {
            $r = ($n - 1) % 26;
            $s = chr(65 + $r) . $s;
            $n = (int)(($n - 1) / 26);
        }
        return $s;
    }

    public function __destruct()
    {
        // Clean up temp file if save() was never called
        if ($this->sheetFH !== null) {
            fclose($this->sheetFH);
        }
        if ($this->sheetTempFile !== null && file_exists($this->sheetTempFile)) {
            unlink($this->sheetTempFile);
        }
    }
}

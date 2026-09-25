<?php

namespace App\Services;

/**
 * Lightweight, high-performance XLSX Generator with Multi-Sheet Support.
 * Uses native OpenXML and zlib compression without external dependencies.
 */
class SimpleXlsxWriter
{
    protected array $sheets = [];

    /**
     * Add a sheet to the workbook.
     *
     * @param string $name Sheet title (max 31 chars, sanitized)
     * @param array $headers List of column header titles
     * @param array $rows List of row arrays
     * @param array $colWidths Optional array of column widths (in characters)
     */
    public function addSheet(string $name, array $headers, array $rows, array $colWidths = []): void
    {
        $sanitizedName = mb_substr(preg_replace('/[\\\\\/*?:\[\]]/', '', trim($name)), 0, 31);
        if ($sanitizedName === '') {
            $sanitizedName = 'Sheet ' . (count($this->sheets) + 1);
        }

        // Avoid duplicate sheet names
        $uniqueName = $sanitizedName;
        $counter = 1;
        $existing = array_column($this->sheets, 'name');
        while (in_array($uniqueName, $existing, true)) {
            $suffix = " ($counter)";
            $uniqueName = mb_substr($sanitizedName, 0, 31 - mb_strlen($suffix)) . $suffix;
            $counter++;
        }

        $this->sheets[] = [
            'name' => $uniqueName,
            'headers' => $headers,
            'rows' => $rows,
            'widths' => $colWidths,
        ];
    }

    /**
     * Build the raw binary .xlsx content.
     */
    public function build(): string
    {
        if (empty($this->sheets)) {
            $this->addSheet('Sheet1', ['Data'], [['Tidak ada data']]);
        }

        $files = [];

        // 1. [Content_Types].xml
        $ct = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $ct .= '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">';
        $ct .= '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>';
        $ct .= '<Default Extension="xml" ContentType="application/xml"/>';
        $ct .= '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
        $ct .= '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        foreach ($this->sheets as $idx => $s) {
            $ct .= '<Override PartName="/xl/worksheets/sheet' . ($idx + 1) . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }
        $ct .= '</Types>';
        $files['[Content_Types].xml'] = $ct;

        // 2. _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $rels .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        $rels .= '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>';
        $rels .= '</Relationships>';
        $files['_rels/.rels'] = $rels;

        // 3. xl/_rels/workbook.xml.rels
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $wbRels .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        $wbRels .= '<Relationship Id="rIdStyles" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
        foreach ($this->sheets as $idx => $s) {
            $wbRels .= '<Relationship Id="rId' . ($idx + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . ($idx + 1) . '.xml"/>';
        }
        $wbRels .= '</Relationships>';
        $files['xl/_rels/workbook.xml.rels'] = $wbRels;

        // 4. xl/styles.xml
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $styles .= '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $styles .= '<fonts count="3">';
        $styles .= '<font><sz val="10"/><name val="Calibri"/></font>'; // 0: Normal
        $styles .= '<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'; // 1: Header (White, Bold)
        $styles .= '<font><b/><sz val="10"/><name val="Calibri"/></font>'; // 2: Bold
        $styles .= '</fonts>';
        $styles .= '<fills count="4">';
        $styles .= '<fill><patternFill patternType="none"/></fill>'; // 0: None
        $styles .= '<fill><patternFill patternType="gray125"/></fill>'; // 1: Gray125
        $styles .= '<fill><patternFill patternType="solid"><fgColor rgb="FF1E3A8A"/></patternFill></fill>'; // 2: Navy Header
        $styles .= '<fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill>'; // 3: Zebra Row
        $styles .= '</fills>';
        $styles .= '<borders count="2">';
        $styles .= '<border><left/><right/><top/><bottom/></border>'; // 0: None
        $styles .= '<border>';
        $styles .= '<left style="thin"><color rgb="FFCBD5E1"/></left>';
        $styles .= '<right style="thin"><color rgb="FFCBD5E1"/></right>';
        $styles .= '<top style="thin"><color rgb="FFCBD5E1"/></top>';
        $styles .= '<bottom style="thin"><color rgb="FFCBD5E1"/></bottom>';
        $styles .= '</border>'; // 1: Thin Gray Border
        $styles .= '</borders>';
        $styles .= '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>';
        $styles .= '<cellXfs count="4">';
        $styles .= '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"/>'; // 0: Normal with border
        $styles .= '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>'; // 1: Header (Navy, White, Bold)
        $styles .= '<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1"/>'; // 2: Zebra Alt Row
        $styles .= '<xf numFmtId="0" fontId="2" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1"/>'; // 3: Bold with border
        $styles .= '</cellXfs>';
        $styles .= '</styleSheet>';
        $files['xl/styles.xml'] = $styles;

        // 5. xl/workbook.xml
        $wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $wb .= '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
        $wb .= '<sheets>';
        foreach ($this->sheets as $idx => $s) {
            $wb .= '<sheet name="' . htmlspecialchars($s['name'], ENT_XML1) . '" sheetId="' . ($idx + 1) . '" r:id="rId' . ($idx + 1) . '"/>';
        }
        $wb .= '</sheets>';
        $wb .= '</workbook>';
        $files['xl/workbook.xml'] = $wb;

        // 6. xl/worksheets/sheetN.xml
        foreach ($this->sheets as $idx => $s) {
            $ws = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
            $ws .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';

            // Column Widths
            if (!empty($s['widths'])) {
                $ws .= '<cols>';
                foreach ($s['widths'] as $cIdx => $w) {
                    $cNum = $cIdx + 1;
                    $ws .= '<col min="' . $cNum . '" max="' . $cNum . '" width="' . max(10, (float)$w) . '" customWidth="1"/>';
                }
                $ws .= '</cols>';
            } elseif (!empty($s['headers'])) {
                // Auto estimate widths
                $ws .= '<cols>';
                foreach ($s['headers'] as $cIdx => $h) {
                    $cNum = $cIdx + 1;
                    $len = mb_strlen((string)$h);
                    $ws .= '<col min="' . $cNum . '" max="' . $cNum . '" width="' . max(12, $len + 4) . '" customWidth="1"/>';
                }
                $ws .= '</cols>';
            }

            $ws .= '<sheetData>';
            $rNum = 1;

            // Header row
            if (!empty($s['headers'])) {
                $ws .= '<row r="' . $rNum . '" customHeight="1" ht="26">';
                $colIdx = 0;
                foreach ($s['headers'] as $h) {
                    $cellRef = $this->colLetter($colIdx) . $rNum;
                    $ws .= '<c r="' . $cellRef . '" t="inlineStr" s="1"><is><t>' . htmlspecialchars((string)$h, ENT_XML1) . '</t></is></c>';
                    $colIdx++;
                }
                $ws .= '</row>';
                $rNum++;
            }

            // Data rows
            foreach ($s['rows'] as $rowIdx => $row) {
                $styleId = ($rowIdx % 2 === 1) ? 2 : 0; // subtle zebra alternating fill
                $ws .= '<row r="' . $rNum . '" customHeight="1" ht="20">';
                $colIdx = 0;
                foreach ($row as $val) {
                    $cellRef = $this->colLetter($colIdx) . $rNum;
                    if (is_numeric($val) && !preg_match('/^0[0-9]+/', (string)$val)) {
                        $ws .= '<c r="' . $cellRef . '" s="' . $styleId . '"><v>' . (float)$val . '</v></c>';
                    } else {
                        $ws .= '<c r="' . $cellRef . '" t="inlineStr" s="' . $styleId . '"><is><t>' . htmlspecialchars((string)($val ?? ''), ENT_XML1) . '</t></is></c>';
                    }
                    $colIdx++;
                }
                $ws .= '</row>';
                $rNum++;
            }

            $ws .= '</sheetData>';
            $ws .= '</worksheet>';
            $files['xl/worksheets/sheet' . ($idx + 1) . '.xml'] = $ws;
        }

        return $this->createZip($files);
    }

    /**
     * Send download response with proper headers.
     */
    public function download(string $filename = 'Export_Data.xlsx'): void
    {
        $content = $this->build();
        $safeFilename = str_replace('"', '', $filename);

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $safeFilename . '"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate');
        header('Pragma: public');
        header('Expires: 0');

        echo $content;
        exit;
    }

    protected function colLetter(int $index): string
    {
        $letters = '';
        while ($index >= 0) {
            $letters = chr(65 + ($index % 26)) . $letters;
            $index = intdiv($index, 26) - 1;
        }
        return $letters;
    }

    protected function createZip(array $files): string
    {
        $zipData = '';
        $cd = '';
        $offset = 0;

        foreach ($files as $name => $content) {
            $compressed = gzdeflate($content);
            $uncompressedSize = strlen($content);
            $compressedSize = strlen($compressed);
            $crc = crc32($content);

            // Local file header
            $header = "\x50\x4b\x03\x04";
            $header .= "\x14\x00"; // min version
            $header .= "\x00\x00"; // flags
            $header .= "\x08\x00"; // compression (deflate)
            $header .= "\x00\x00\x00\x00"; // time/date
            $header .= pack('V', $crc);
            $header .= pack('V', $compressedSize);
            $header .= pack('V', $uncompressedSize);
            $header .= pack('v', strlen($name));
            $header .= pack('v', 0); // extra field len
            $header .= $name;

            $zipData .= $header . $compressed;

            // Central directory entry
            $cdEntry = "\x50\x4b\x01\x02";
            $cdEntry .= "\x14\x00"; // version made by
            $cdEntry .= "\x14\x00"; // version needed
            $cdEntry .= "\x00\x00"; // flags
            $cdEntry .= "\x08\x00"; // compression (deflate)
            $cdEntry .= "\x00\x00\x00\x00";
            $cdEntry .= pack('V', $crc);
            $cdEntry .= pack('V', $compressedSize);
            $cdEntry .= pack('V', $uncompressedSize);
            $cdEntry .= pack('v', strlen($name));
            $cdEntry .= pack('v', 0); // extra field len
            $cdEntry .= pack('v', 0); // comment len
            $cdEntry .= pack('v', 0); // disk number
            $cdEntry .= pack('v', 0); // internal attr
            $cdEntry .= pack('V', 32); // external attr
            $cdEntry .= pack('V', $offset);
            $cdEntry .= $name;

            $cd .= $cdEntry;
            $offset = strlen($zipData);
        }

        $cdLen = strlen($cd);
        $cdOffset = strlen($zipData);

        // End of central directory record
        $eocd = "\x50\x4b\x05\x06";
        $eocd .= "\x00\x00"; // disk num
        $eocd .= "\x00\x00"; // disk start
        $eocd .= pack('v', count($files));
        $eocd .= pack('v', count($files));
        $eocd .= pack('V', $cdLen);
        $eocd .= pack('V', $cdOffset);
        $eocd .= "\x00\x00"; // comment len

        return $zipData . $cd . $eocd;
    }
}

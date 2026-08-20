<?php

namespace App\Exports;

use ZipArchive;

/**
 * Generator file .xlsx minimal tanpa dependency eksternal.
 * Menghasilkan file OOXML Spreadsheet asli (bukan HTML/CSV) dengan
 * gaya laporan keuangan profesional: judul, subjudul, header berwarna,
 * grid border, kolom lebar yang disesuaikan, dan format angka Rupiah.
 */
class SpreadsheetExport
{
    /**
     * Buat konten biner .xlsx dari definisi laporan.
     *
     * @param array $config
     *  - sheetName: string
     *  - title: string|null
     *  - subtitle: string|null
     *  - columns: string[]
     *  - widths: int[]|null  (lebar tiap kolom)
     *  - currencyColumns: int[]  (indeks kolom berformat Rupiah)
     *  - rows: array[] (baris data)
     *  - footer: array[] (baris ringkasan, dicetak tebal)
     * @return string
     */
    public static function build(array $config)
    {
        $sheetName = $config['sheetName'] ?? 'Sheet1';
        $title = $config['title'] ?? null;
        $subtitle = $config['subtitle'] ?? null;
        $columns = $config['columns'] ?? [];
        $widths = $config['widths'] ?? null;
        $currencyColumns = array_flip($config['currencyColumns'] ?? []);
        $rows = $config['rows'] ?? [];
        $footer = $config['footer'] ?? [];

        $sheetXml = self::buildSheetXml($title, $subtitle, $columns, $widths, $currencyColumns, $rows, $footer);

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';

        $rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';

        $safeSheetName = htmlspecialchars(self::sanitizeSheetName($sheetName), ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . $safeSheetName . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';

        $workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';

        $zip = new ZipArchive();
        $tmp = tempnam(sys_get_temp_dir(), 'silka_');
        if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Tidak dapat membuat file xlsx.');
        }

        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rootRels);
        $zip->addFromString('xl/workbook.xml', $workbook);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRels);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->addFromString('xl/styles.xml', self::stylesXml());
        $zip->close();

        $content = file_get_contents($tmp);
        @unlink($tmp);

        return $content;
    }

    protected static function sanitizeSheetName($name)
    {
        return preg_replace('/[\[\]:*?\/\\\\]/', '', (string) $name);
    }

    /**
     * Map gaya cell:
     *  0 default, 1 judul, 2 subjudul, 3 header, 4 teks,
     *  5 angka, 6 angka Rupiah, 7 teks total, 8 angka total, 9 angka Rupiah total.
     */
    protected static function buildSheetXml($title, $subtitle, array $columns, $widths, array $currencyColumns, array $rows, array $footer)
    {
        $colCount = max(1, count($columns));
        $lastCol = self::columnLetter($colCount);
        $rowIndex = 1;
        $body = '';
        $mergeCells = [];

        // Judul (merged)
        if ($title !== null) {
            $body .= self::cellRow(self::padCells($colCount, [self::cell($title, 1)]), $rowIndex);
            $mergeCells[] = 'A' . $rowIndex . ':' . $lastCol . $rowIndex;
            $rowIndex++;
        }

        // Subjudul (merged)
        if ($subtitle !== null) {
            $body .= self::cellRow(self::padCells($colCount, [self::cell($subtitle, 2)]), $rowIndex);
            $mergeCells[] = 'A' . $rowIndex . ':' . $lastCol . $rowIndex;
            $rowIndex++;
        }

        // Baris header
        $headerCells = [];
        foreach ($columns as $column) {
            $headerCells[] = self::cell($column, 3);
        }
        $body .= self::cellRow($headerCells, $rowIndex);
        $rowIndex++;

        // Baris data
        foreach ($rows as $row) {
            $cells = [];
            foreach ($row as $col => $value) {
                $style = isset($currencyColumns[$col]) ? 6 : (is_int($value) || is_float($value) ? 5 : 4);
                $cells[] = self::cell($value, $style);
            }
            $body .= self::cellRow($cells, $rowIndex);
            $rowIndex++;
        }

        // Baris footer/total
        foreach ($footer as $row) {
            $cells = [];
            foreach ($row as $col => $value) {
                $style = isset($currencyColumns[$col]) ? 9 : (is_int($value) || is_float($value) ? 8 : 7);
                $cells[] = self::cell($value, $style);
            }
            $body .= self::cellRow($cells, $rowIndex);
            $rowIndex++;
        }

        // Kolom lebar
        $colsXml = '';
        if (is_array($widths) && count($widths) > 0) {
            $colsXml = '<cols>';
            foreach ($widths as $i => $width) {
                $colsXml .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . (float) $width . '" customWidth="1"/>';
            }
            $colsXml .= '</cols>';
        }

        $mergeXml = '';
        if (count($mergeCells) > 0) {
            $mergeXml = '<mergeCells count="' . count($mergeCells) . '">';
            foreach ($mergeCells as $ref) {
                $mergeXml .= '<mergeCell ref="' . $ref . '"/>';
            }
            $mergeXml .= '</mergeCells>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . $colsXml
            . '<sheetData>' . $body . '</sheetData>'
            . $mergeXml
            . '</worksheet>';
    }

    protected static function padCells($count, array $cells)
    {
        while (count($cells) < $count) {
            $cells[] = self::emptyCell();
        }
        return $cells;
    }

    protected static function cell($value, $style)
    {
        return [$value, $style];
    }

    protected static function emptyCell()
    {
        return [null, 0];
    }

    protected static function cellRow(array $cells, $rowIndex)
    {
        $xml = '';
        foreach ($cells as $i => $cell) {
            [$value, $style] = $cell;
            $ref = self::columnLetter($i + 1) . $rowIndex;

            if ($value === null) {
                $xml .= '<c r="' . $ref . '" s="' . $style . '"/>';
                continue;
            }

            $value = self::sanitizeFormula($value);

            if (is_float($value)) {
                $xml .= '<c r="' . $ref . '" s="' . $style . '" t="n"><v>' . $value . '</v></c>';
            } elseif (is_int($value)) {
                $xml .= '<c r="' . $ref . '" s="' . $style . '" t="n"><v>' . $value . '</v></c>';
            } else {
                $escaped = htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $xml .= '<c r="' . $ref . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">' . $escaped . '</t></is></c>';
            }
        }
        return '<row r="' . $rowIndex . '">' . $xml . '</row>';
    }

    protected static function columnLetter($index)
    {
        $letter = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $index = intdiv($index - 1, 26);
        }
        return $letter;
    }

    protected static function sanitizeFormula($value)
    {
        if (is_string($value) && strlen($value) > 0) {
            $first = $value[0];
            if (in_array($first, ['=', '+', '-', '@'])) {
                return "'" . $value;
            }
        }
        return $value;
    }

    protected static function stylesXml()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="1"><numFmt numFmtId="164" formatCode="#,##0.00"/></numFmts>'
            . '<fonts count="7">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="14"/><color rgb="FF1F4E78"/><name val="Calibri"/></font>'
            . '<font><i/><sz val="10"/><color rgb="FF6B7280"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FF1F4E78"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="5">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF1F4E78"/><bgColor indexed="64"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFDDEBF7"/><bgColor indexed="64"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFF2F2F2"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="1">'
            . '<border><left style="thin"><color rgb="FFB0B7C3"/></left>'
            . '<right style="thin"><color rgb="FFB0B7C3"/></right>'
            . '<top style="thin"><color rgb="FFB0B7C3"/></top>'
            . '<bottom style="thin"><color rgb="FFB0B7C3"/></bottom>'
            . '<diagonal/></border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="10">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="3" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="4" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="4" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '<xf numFmtId="164" fontId="4" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyNumberFormat="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="5" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="5" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '<xf numFmtId="164" fontId="5" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyNumberFormat="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }
}
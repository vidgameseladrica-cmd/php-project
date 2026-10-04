<?php
/**
 * simple_xlsx_reader.php
 * Composer-free XLSX reader for simple tabular Excel files.
 * Requires PHP ZipArchive + SimpleXML.
 *
 * This is intentionally limited to .xlsx files and reads the first worksheet.
 */

declare(strict_types=1);

function xlsx_column_to_number(string $column): int {
    $column = preg_replace('/[^A-Z]/i', '', strtoupper($column));
    $number = 0;
    for ($i = 0, $len = strlen($column); $i < $len; $i++) {
        $number = $number * 26 + (ord($column[$i]) - 64);
    }
    return $number;
}

function xlsx_xml(string $xml): SimpleXMLElement {
    $old = libxml_use_internal_errors(true);
    $obj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOCDATA);
    libxml_clear_errors();
    libxml_use_internal_errors($old);

    if ($obj === false) {
        throw new RuntimeException('Invalid Excel XML data.');
    }
    return $obj;
}

function xlsx_read(string $filename): array {
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException('PHP ZipArchive extension is not enabled. Enable extension=zip in php.ini.');
    }

    if (!function_exists('simplexml_load_string')) {
        throw new RuntimeException('PHP SimpleXML extension is not enabled.');
    }

    $zip = new ZipArchive();
    if ($zip->open($filename) !== true) {
        throw new RuntimeException('Unable to open XLSX file.');
    }

    $sharedStrings = [];

    $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($sharedXml !== false) {
        $shared = xlsx_xml($sharedXml);
        foreach ($shared->si as $si) {
            $text = '';
            if (isset($si->t)) {
                $text = (string)$si->t;
            } else {
                foreach ($si->r as $run) {
                    $text .= (string)$run->t;
                }
            }
            $sharedStrings[] = $text;
        }
    }

    // Locate the first worksheet from workbook.xml + relationships.
    $workbookXml = $zip->getFromName('xl/workbook.xml');
    $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

    if ($workbookXml === false || $relsXml === false) {
        $zip->close();
        throw new RuntimeException('Invalid XLSX workbook structure.');
    }

    $workbook = xlsx_xml($workbookXml);
    $rels = xlsx_xml($relsXml);

    $ns = $workbook->getNamespaces(true);
    $rNs = $ns['r'] ?? 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    $sheets = $workbook->sheets->sheet;
    if (count($sheets) === 0) {
        $zip->close();
        throw new RuntimeException('The Excel file has no worksheet.');
    }

    $firstSheet = $sheets[0];
    $rid = (string)$firstSheet->attributes($rNs)['id'];

    $target = null;
    foreach ($rels->Relationship as $rel) {
        if ((string)$rel['Id'] === $rid) {
            $target = (string)$rel['Target'];
            break;
        }
    }

    if (!$target) {
        $zip->close();
        throw new RuntimeException('Could not locate the first worksheet.');
    }

    $target = ltrim(str_replace('\\', '/', $target), '/');
    if (strpos($target, 'xl/') !== 0) {
        $target = 'xl/' . $target;
    }

    $sheetXml = $zip->getFromName($target);
    $zip->close();

    if ($sheetXml === false) {
        throw new RuntimeException('Worksheet XML was not found.');
    }

    $sheet = xlsx_xml($sheetXml);
    $rows = [];

    foreach ($sheet->sheetData->row as $xmlRow) {
        $cells = [];
        foreach ($xmlRow->c as $cell) {
            $ref = (string)$cell['r'];
            preg_match('/^([A-Z]+)/i', $ref, $m);
            $col = isset($m[1]) ? xlsx_column_to_number($m[1]) : count($cells) + 1;

            $type = (string)$cell['t'];
            $value = '';

            if ($type === 'inlineStr') {
                $value = isset($cell->is->t) ? (string)$cell->is->t : '';
            } elseif ($type === 's') {
                $index = (int)$cell->v;
                $value = $sharedStrings[$index] ?? '';
            } else {
                $value = isset($cell->v) ? (string)$cell->v : '';
            }

            $cells[$col] = $value;
        }

        if ($cells) {
            ksort($cells);
            $max = max(array_keys($cells));
            $row = array_fill(1, $max, '');
            foreach ($cells as $col => $value) {
                $row[$col] = $value;
            }
            $rows[] = $row;
        }
    }

    return $rows;
}

<?php

$file = $argv[1] ?? null;
$sheetName = $argv[2] ?? 'Parameters';

if (! $file || ! is_file($file)) {
    fwrite(STDERR, "Usage: php tools/inspect_cells.php <file.xlsx> [sheet]\n");
    exit(1);
}

$zip = new ZipArchive();
$zip->open($file);

$sharedStrings = [];
$sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');

if ($sharedStringsXml) {
    $shared = simplexml_load_string($sharedStringsXml);

    foreach ($shared->si as $item) {
        $text = '';

        foreach ($item->xpath('.//t') as $node) {
            $text .= (string) $node;
        }

        $sharedStrings[] = $text;
    }
}

$workbook = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
$relationships = simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));
$relationshipMap = [];

foreach ($relationships->Relationship as $relationship) {
    $relationshipMap[(string) $relationship['Id']] = (string) $relationship['Target'];
}

foreach ($workbook->sheets->sheet as $sheet) {
    if ((string) $sheet['name'] !== $sheetName) {
        continue;
    }

    $attributes = $sheet->attributes('r', true);
    $target = 'xl/'.ltrim($relationshipMap[(string) $attributes['id']], '/');
    $worksheet = simplexml_load_string($zip->getFromName($target));

    foreach ($worksheet->sheetData->row as $row) {
        $rowNumber = (int) $row['r'];

        if ($rowNumber < 1 || $rowNumber > 25) {
            continue;
        }

        foreach ($row->c as $cell) {
            $value = (string) $cell->v;

            if ((string) $cell['t'] === 's') {
                $value = $sharedStrings[(int) $value] ?? $value;
            }

            if ($value !== '') {
                echo $cell['r'].': '.$value.PHP_EOL;
            }
        }
    }
}

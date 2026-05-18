<?php

$file = $argv[1] ?? null;
$sheetName = $argv[2] ?? 'BES';

if (! $file || ! is_file($file)) {
    fwrite(STDERR, "Usage: php tools/inspect_formulas.php <file.xlsx> [sheet]\n");
    exit(1);
}

$zip = new ZipArchive();
$zip->open($file);

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
    $relationshipId = (string) $attributes['id'];
    $target = 'xl/'.ltrim($relationshipMap[$relationshipId], '/');
    $worksheet = simplexml_load_string($zip->getFromName($target));

    foreach ($worksheet->sheetData->row as $row) {
        $rowNumber = (int) $row['r'];

        if ($rowNumber < 10 || $rowNumber > 20) {
            continue;
        }

        echo "Row {$rowNumber}\n";

        foreach ($row->c as $cell) {
            $formula = (string) $cell->f;
            $value = (string) $cell->v;

            if ($formula !== '') {
                echo '  '.$cell['r'].' = '.$formula.' -> '.$value.PHP_EOL;
            }
        }
    }
}

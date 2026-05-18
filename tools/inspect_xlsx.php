<?php

$file = $argv[1] ?? null;

if (! $file || ! is_file($file)) {
    fwrite(STDERR, "Usage: php tools/inspect_xlsx.php <file.xlsx>\n");
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
    $attributes = $sheet->attributes('r', true);
    $relationshipId = (string) $attributes['id'];
    $target = 'xl/'.ltrim($relationshipMap[$relationshipId], '/');
    $name = (string) $sheet['name'];

    echo "SHEET: {$name} ({$target})\n";

    $worksheet = simplexml_load_string($zip->getFromName($target));
    $rowNumber = 0;

    foreach ($worksheet->sheetData->row as $row) {
        $values = [];

        foreach ($row->c as $cell) {
            $value = (string) $cell->v;

            if ((string) $cell['t'] === 's') {
                $value = $sharedStrings[(int) $value] ?? $value;
            }

            $values[] = $value;
        }

        echo implode(' | ', $values)."\n";

        if (++$rowNumber >= 12) {
            break;
        }
    }

    echo "---\n";
}

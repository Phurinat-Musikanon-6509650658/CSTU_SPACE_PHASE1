<?php
$path = __DIR__ . '/../public/templates/project_import_template.xlsx';
$zip = new ZipArchive();
$zip->open($path);
echo "=== workbook.xml ===\n";
echo $zip->getFromName('xl/workbook.xml') . "\n\n";
echo "=== workbook.xml.rels ===\n";
echo $zip->getFromName('xl/_rels/workbook.xml.rels') . "\n\n";
echo "=== All zip entries ===\n";
for ($i = 0; $i < $zip->numFiles; $i++) {
    echo $zip->getNameIndex($i) . "\n";
}
$zip->close();

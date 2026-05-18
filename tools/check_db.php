<?php

$db = new PDO('sqlite:database/database.sqlite');

foreach (['audit_imports', 'school_grade_audits', 'audit_sheets', 'users'] as $table) {
    echo $table.': '.$db->query('select count(*) from '.$table)->fetchColumn().PHP_EOL;
}

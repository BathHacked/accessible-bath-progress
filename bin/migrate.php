<?php

require __DIR__ . '/../vendor/autoload.php';

$dbSettings = require __DIR__ . '/../config/db/settings.php';

$db = new mysqli($dbSettings['host'], $dbSettings['username'], $dbSettings['password'], $dbSettings['dbname']);

$migrations = ['setup'];

$db->begin_transaction();

try
{
    foreach($migrations as $migration)
    {
        $sql = @file_get_contents(__DIR__ . "/../migrate/{$migration}.sql");

        if($sql === false) die("Could not open migration {$migration}" . PHP_EOL);

        $db->multi_query($sql);
    }

    $db->commit();
}
catch(Exception $e)
{
    $db->rollback();

    echo "Migration error: ", $e->getMessage(), PHP_EOL;

    exit;
}

echo 'Migration completed successfully', PHP_EOL;
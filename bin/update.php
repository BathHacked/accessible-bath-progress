<?php

set_time_limit(10 * 60);

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../config/db/init.php';

$wheelmapConfig = include __DIR__ . '/../config/wheelmap.php';

try
{
    $api = new \Wheelmap\Api($wheelmapConfig);

    $categories = new BathHacked\Categories();

    $categories->updateFromWheelmap($api->getCategories());

    echo "Updated categories", PHP_EOL;

    $nodeTypes = new BathHacked\NodeTypes();

    $nodeTypes->updateFromWheelmap($api->getNodeTypes());

    echo "Updated node types", PHP_EOL;

    $nodes = new BathHacked\Nodes();

    echo "Fetching nodes from Wheelmap", PHP_EOL;

    $apiNodes = $api->getNodes();

    echo "Updating nodes", PHP_EOL;

    $status = $nodes->updateFromWheelmap($apiNodes);

    echo "Updated nodes", PHP_EOL;

    \FileSystemCache::$cacheDir = __DIR__ . '/../storage/cache';

    $key = \FileSystemCache::generateCacheKey('stats');

    \FileSystemCache::invalidate($key);

    if($status['created'] > 0 || $status['updated'] > 0 || $status['deleted'] > 0)
    {
        \BathHacked\Helpers::logger()->info('Update completed', $status);
    }
}
catch (\Exception $e)
{
    \BathHacked\Helpers::logger()->error($e->getMessage(), ['action' => 'update']);

    throw $e;
}



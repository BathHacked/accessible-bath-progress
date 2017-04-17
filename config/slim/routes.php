<?php

/**
 * Routes
 */

$app->get('/', function ($request, $response) {

    \FileSystemCache::$cacheDir = __DIR__ . '/../../storage/cache';

    $key = \FileSystemCache::generateCacheKey('stats');

    $stats = \FileSystemCache::retrieve($key);

    if(!$stats)
    {
        $statsBuilder = new \BathHacked\StatsBuilder();
        $stats = $statsBuilder->getStats();

        \FileSystemCache::store($key, $stats, 30 * 60);
    }

    return $this->view->render($response, 'index.twig', [
        'stats' => $stats,
        'appConfig' => $this['appConfig'],
    ]);

})->setName('home');

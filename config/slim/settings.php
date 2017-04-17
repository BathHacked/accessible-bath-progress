<?php
return [
    'settings' => [
        'displayErrorDetails' => false, // set to false in production, true in development
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../../templates/',
            'cache_path' => __DIR__ . '/../../storage/view_cache',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../../storage/logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
    ],
];

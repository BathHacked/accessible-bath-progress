<?php

// Instantiate the app
$slimSettings = require __DIR__ . '/settings.php';
$app = new \Slim\App($slimSettings);

// Set up dependencies
require __DIR__ . '/dependencies.php';

// Register middleware
require __DIR__ . '/middleware.php';

// Register routes
require __DIR__ . '/routes.php';

// Run app
$app->run();

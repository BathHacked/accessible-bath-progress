<?php

// DIC configuration
$container = $app->getContainer();

// monolog
$container['logger'] = function ($container) {
    $settings = $container->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

$container['view'] = function ($container) {
    $settings = $container->get('settings')['renderer'];
    $view = new \Slim\Views\Twig($settings['template_path'], [
        'cache' => $container['settings']['displayErrorDetails'] ? false : $settings['cache_path'],
        'debug' => $container['settings']['displayErrorDetails'],
        'auto_reload' => true,
    ]);

    $view->addExtension(new Twig_Extension_Debug());

    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    return $view;
};

$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        return $container['view']->render($response, '404.twig', [])->withStatus(404);
    };
};

if(!$container['settings']['displayErrorDetails']) {
    $container['errorHandler'] = function ($container) {
        return function ($request, $response, $exception) use ($container) {

            $container['logger']->error($exception->getMessage());

            return $container['view']->render($response, '500.twig', [])->withStatus(500);
        };
    };
}

$container['wheelmap'] = function($container) {
    $config = include __DIR__ . '/../wheelmap.php';

    return new \Wheelmap\Api($config);
};

$container['appConfig'] = function($container) {
    $config = include __DIR__ . '/../app.php';

    return $config;
};


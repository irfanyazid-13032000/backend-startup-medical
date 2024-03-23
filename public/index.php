<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Router;

try {
    $rootPath = realpath('..');
    require_once $rootPath . '/vendor/autoload.php';

    // Load environment variables
    Dotenv::createImmutable($rootPath)->load();

    // Initialize Phalcon Dependency Injection
    $di = new FactoryDefault();
    $di->offsetSet('rootPath', function () use ($rootPath) {
        return $rootPath;
    });

    // Register Service Providers
    $providersPath = $rootPath . '/config/providers.php';
    if (!file_exists($providersPath) || !is_readable($providersPath)) {
        throw new Exception('File providers.php does not exist or is not readable.');
    }
    $providers = include_once $providersPath;
    foreach ($providers as $provider) {
        $di->register(new $provider());
    }

    // Initialize Router
    $router = new Router();
    $router->setDefaults([
        'controller' => 'index', // Default controller
        'action'     => 'index'  // Default action
    ]);

    // Define route for GET request to IndexController
    $router->add('/', [
        'controller' => 'IndexController', // Controller name
        'action'     => 'indexAction'  // Action name
    ]);

    // Initialize MVC Application
    $application = new Application($di);

    // Handle request
    $response = $application->handle($_SERVER['REQUEST_URI']);

    // Send response
    $response->send();
} catch (Exception $e) {
    echo $e->getMessage() . '<br>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}

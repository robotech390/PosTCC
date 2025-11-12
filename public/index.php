<?php

declare(strict_types=1);

use Laminas\Mvc\Application;
use Laminas\Stdlib\ArrayUtils;

chdir(dirname(__DIR__));

if (file_exists('vendor/autoload.php')) {
    $loader = include 'vendor/autoload.php';
} else {
    throw new RuntimeException('Não foi possível encontrar "vendor/autoload.php". Execute "composer install".');
}

$appConfig = require 'config/application.config.php';
if (file_exists('config/development.config.php')) {
    $appConfig = ArrayUtils::merge($appConfig, require 'config/development.config.php');
}

try {
    Application::init($appConfig)->run();
} catch (Throwable $e) {
    http_response_code(500);
    echo '<pre>';
    echo '<strong>Erro ao iniciar a aplicação:</strong><br/>';
    echo $e->getMessage() . '<br/><br/>';
    echo $e->getTraceAsString();
    echo '</pre>';
}
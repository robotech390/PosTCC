<?php

use Laminas\Mvc\Application;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

$appConfig = require 'config/application.config.php';
$app = Application::init($appConfig);

$app->getServiceManager()->get('ModuleManager')->getEventManager()->trigger('clear-config-cache');

echo "Configuration cache cleared successfully.\n";
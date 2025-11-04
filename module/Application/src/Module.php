<?php

declare(strict_types=1);

namespace Application;

use Laminas\Authentication\AuthenticationService;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\MvcEvent;

class Module
{

    public function init(ModuleManager $moduleManager)
    {
        $moduleManager->getEventManager()->attach('loadModule', function ($e) {
            $moduleName = $e->getModuleName();
            error_log("Module loaded: " . $moduleName);
        });
    }

    public function getConfig(): array
    {
        /** @var array $config */
        $config = include __DIR__ . '/../config/module.config.php';
        return $config;
    }

    public function onBootstrap(MvcEvent $e)
    {
        $app = $e->getApplication();
        $eventManager = $app->getEventManager();
        $serviceManager = $app->getServiceManager();

        $eventManager->attach(MvcEvent::EVENT_ROUTE, function (MvcEvent $e) use ($serviceManager) {

            $authService = $serviceManager->get(AuthenticationService::class);
            $routeName = $e->getRouteMatch()->getMatchedRouteName();

            $whitelist = ['home', 'logout'];

            if ($authService->hasIdentity()) {
                if ($routeName == 'home') {
                    return $this->redirectToRoute($e, 'paciente');
                }
            } else {
                if (!in_array($routeName, $whitelist)) {
                    return $this->redirectToRoute($e, 'home');
                }
            }
        }, -100);
    }

    private function redirectToRoute(MvcEvent $e, string $routeName)
    {
        $url = $e->getRouter()->assemble([], ['name' => $routeName]);
        $response = $e->getResponse();
        $response->getHeaders()->addHeaderLine('Location', $url);
        $response->setStatusCode(302);
        $response->sendHeaders();
        return $response;
    }
}

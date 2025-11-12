<?php

declare(strict_types=1);

namespace Application;

use Application\Plugin\Login\AuthManager;
use Exception;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\MvcEvent;

class Module
{
    public function getConfig(): array
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function onBootstrap(MvcEvent $event): void
    {
        $application = $event->getApplication();
        $eventManager = $application->getEventManager();
        $sharedEventManager = $eventManager->getSharedManager();

        $sharedEventManager->attach(
            AbstractActionController::class,
            MvcEvent::EVENT_DISPATCH,
            [$this, 'onDispatch'],
            100
        );

//        $sessionManager = $application->getServiceManager()->get('Laminas\Session\SessionManager');
//        $this->forgetInvalidSession($sessionManager);
    }

    protected function forgetInvalidSession($sessionManager): void
    {
        try {
            $sessionManager->start();
            return;
        } catch (Exception $e) {
        }

        session_unset();
    }

    public function onDispatch(MvcEvent $event)
    {
        $authManager = $event->getApplication()->getServiceManager()->get(AuthManager::class);


        if (! $authManager->hasIdentity() && $event->getRouteMatch()->getMatchedRouteName() !== 'login') {
            return $event->getTarget()->redirect()->toUrl('/login');
        }
        return $event;
    }
}

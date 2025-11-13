<?php

namespace Application\Plugin\Login;

use Interop\Container\Containerinterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AuthManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AuthManager
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $authenticationService = $container->get(AuthenticationService::class);

        return new AuthManager($authenticationService, $entityManager);
    }
}

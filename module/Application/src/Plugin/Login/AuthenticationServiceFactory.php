<?php

namespace Application\Plugin\Login;

use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Storage\Session;
use Psr\Container\ContainerInterface;

class AuthenticationServiceFactory
{
    public function __invoke(ContainerInterface $container): AuthenticationService
    {
        $adapter = $container->get(AuthAdapter::class);
        $storage = new Session('Auth');

        return new AuthenticationService($storage, $adapter);
    }

}
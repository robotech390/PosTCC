<?php
namespace Application\Controller\Factory;

use Application\Controller\AuthController;
use Laminas\Authentication\AuthenticationService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AuthControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $authService = $container->get(\Laminas\Authentication\AuthenticationService::class);
        return new AuthController($authService);
    }
}
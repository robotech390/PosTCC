<?php
namespace Application\Controller\Factory;

use Application\Controller\AuthController;
use Application\Plugin\Login\AuthManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AuthControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $authManager = $container->get(AuthManager::class);

        return new AuthController($entityManager, $authManager);
    }
}
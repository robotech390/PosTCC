<?php
namespace Application\Form\Factory;

use Application\Form\LeitoForm;
use Doctrine\ORM\EntityManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class LeitoFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        // Passa o EntityManager para o construtor do LeitoForm
        return new LeitoForm($entityManager);
    }
}
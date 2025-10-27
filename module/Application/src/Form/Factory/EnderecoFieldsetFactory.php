<?php

namespace Application\Form\Factory;

use Application\Form\EnderecoFieldset;
use Doctrine\ORM\EntityManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class EnderecoFieldsetFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {

        var_dump('*** A FACTORY DO ENDERECOFIELDSET FOI EXECUTADA! ***');
        die();
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        return new EnderecoFieldset($entityManager);
    }
}
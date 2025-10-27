<?php

namespace Application\Command\Factory;

use Application\Command\LoadFixturesCommand;
use Doctrine\ORM\EntityManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class LoadFixturesCommandFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): LoadFixturesCommand
    {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $config = $container->get('config');
        $fixturesPath = $config['doctrine']['fixtures']['Application'];

        return new LoadFixturesCommand($entityManager, $fixturesPath);
    }
}
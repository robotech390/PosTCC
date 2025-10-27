<?php
namespace Application\Command\Factory;

use Application\Command\ListenMqttCommand;
use Doctrine\ORM\EntityManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ListenMqttCommandFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ListenMqttCommand
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $config = $container->get('config');
        $mqttConfig = $config['mqtt'] ?? [
            'server'    => 'localhost',
            'port'      => 1883,
            'topic'     => 'hospital/leitos/pacientes',
            'client_id' => 'laminas_listener_' . bin2hex(random_bytes(5)),
        ];

        return new ListenMqttCommand($entityManager, $mqttConfig);
    }
}
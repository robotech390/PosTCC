<?php

declare(strict_types=1);

namespace Application;

use Application\Controller\AuthController;
use Application\Controller\Factory\AuthControllerFactory;
use Application\Controller\Factory\LeitoControllerFactory;
use Application\Controller\Factory\PacienteControllerFactory;
use Application\Controller\LeitoController;
use Application\Controller\PacienteController;
use Application\Form\EnderecoFieldset;
use Application\Form\Factory\EnderecoFieldsetFactory;
use Application\Form\Factory\LeitoFormFactory;
use Application\Form\LeitoForm;
use Application\Form\PessoaForm;
use Application\Form\ResponsavelFieldset;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'login',
                    ],
                ],
            ],
            'logout' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/logout',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'logout',
                    ],
                ],
            ],
            'application' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/application[/:action]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'api-cidades' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/cidades/:id_estado',
                    'constraints' => [
                        'id_estado' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'getCidades',
                    ],
                ],
            ],
            'api-pinos' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/api/pinos/:esp32_id',
                    'constraints' => [
                        'esp32_id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\LeitoController::class,
                        'action'     => 'getPinos',
                    ],
                ],
            ],
            'paciente' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/paciente[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\PacienteController::class,
                        'action'     => 'listar',
                    ],
                ],
            ],
            'leitos' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/leitos[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\LeitoController::class,
                        'action'     => 'listar',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            PacienteController::class => PacienteControllerFactory::class,
            LeitoController::class => LeitoControllerFactory::class,
            AuthController::class => AuthControllerFactory::class,
        ],
    ],
    'doctrine' => [
        'authenticationadapter' => [
            'orm_default' => [
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'identity_class' => \Application\Entity\Usuario::class,
                'identity_property' => 'email',
                'credential_property' => 'password',
                'credential_callable' => function (\Application\Entity\Usuario $user, $passwordGiven) {
                    return $user->verifyPassword($passwordGiven);
                },
            ],
        ],
        'fixtures' => [
            'Application' => __DIR__ . '/../src/DataFixtures',
        ],
    ],
    'laminas-cli' => [
        'commands' => [
            'app:fixtures:load' => Command\LoadFixturesCommand::class,
            'app:mqtt:listen' => Command\ListenMqttCommand::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Command\LoadFixturesCommand::class => Command\Factory\LoadFixturesCommandFactory::class,
            Command\ListenMqttCommand::class => Command\Factory\ListenMqttCommandFactory::class,
            \Laminas\Authentication\AuthenticationService::class => function (\Psr\Container\ContainerInterface $container) {
                $config = $container->get('config');
                $adapterConfig = $config['doctrine']['authenticationadapter']['orm_default'] ?? [];

                $entityManager = $container->get($adapterConfig['object_manager'] ?? 'doctrine.entitymanager.orm_default');

                $options = new \DoctrineModule\Options\Authentication($adapterConfig);
                $options->setObjectManager($entityManager); // Garante que o EM está setado

                $adapter = new \DoctrineModule\Authentication\Adapter\ObjectRepository($options);

                return new \Laminas\Authentication\AuthenticationService(null, $adapter);
            },
        ],

        'form_elements' => [
            'factories' => [
                PessoaForm::class => InvokableFactory::class,
                LeitoForm::class => LeitoFormFactory::class,
                EnderecoFieldset::class => EnderecoFieldsetFactory::class,
                ResponsavelFieldset::class => InvokableFactory::class,
            ],
        ],
    ],
    'mqtt' => [
        'server'    => 'localhost',
        'port'      => 1883,
        'topic'     => 'hospital/leitos/pacientes',
        'client_id' => 'laminas_listener_' . bin2hex(random_bytes(5)),
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
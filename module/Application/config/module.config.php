<?php

declare(strict_types=1);

namespace Application;

use Application\Controller\Factory\PacienteControllerFactory;
use Application\Form\EnderecoFieldset;
use Application\Form\Factory\EnderecoFieldsetFactory;
use Application\Form\PessoaForm;
use Application\Form\ResponsavelFieldset;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Application\Command;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
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
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => InvokableFactory::class,
            Controller\PacienteController::class => PacienteControllerFactory::class,
        ],
    ],
    'doctrine' => [
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
        ],

        'form_elements' => [
            'factories' => [
                PessoaForm::class => InvokableFactory::class,
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
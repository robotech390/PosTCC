<?php

declare(strict_types=1);

namespace Application;

use Application\Controller\AuthController;
use Application\Controller\Factory\AuthControllerFactory;
use Application\Controller\Factory\LeitoControllerFactory;
use Application\Controller\Factory\PacienteControllerFactory;
use Application\Controller\LeitoController;
use Application\Controller\PacienteController;
use Application\Plugin\Login\AuthAdapter;
use Application\Plugin\Login\AuthAdapterFactory;
use Application\Plugin\Login\AuthenticationServiceFactory;
use Application\Plugin\Login\AuthManager;
use Application\Plugin\Login\AuthManagerFactory;
use Application\View\ViewRouteMatchFactory;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Laminas\Authentication\AuthenticationService;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'login' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/login',
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
    'service_manager' => [
        'factories' => [
            AuthenticationService::class => AuthenticationServiceFactory::class,
            AuthManager::class => AuthManagerFactory::class,
            AuthAdapter::class => AuthAdapterFactory::class
        ],
    ],
    'view_helpers' => [
        'aliases' => [
            "routeMatch" => View\ViewRouteMatch::class
        ],
        'factories' => [
            View\ViewRouteMatch::class => ViewRouteMatchFactory::class

        ],
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
    'doctrine' => [
        'driver' => [
            __NAMESPACE__ . '_driver' => [
                'class' => AttributeDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/Entity',
                ]
            ],
            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver',
                ]
            ],
        ],
    ],
];
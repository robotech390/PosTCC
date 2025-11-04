<?php

declare(strict_types=1);

namespace Application;

use Application\Controller\AuthController;
use Application\Controller\Factory\AuthControllerFactory;
use Application\Controller\Factory\LeitoControllerFactory;
use Application\Controller\Factory\PacienteControllerFactory;
use Application\Controller\LeitoController;
use Application\Controller\PacienteController;
use Application\View\ViewRouteMatchFactory;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

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
];
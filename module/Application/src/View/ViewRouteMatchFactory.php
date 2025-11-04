<?php

namespace Application\View;

use Laminas\Router\Http\RouteMatch;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ViewRouteMatchFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ViewRouteMatch
    {
        $application = $container->get('Application');

        $mvcEvent = $application->getMvcEvent();

        /** @var RouteMatch $routeMatch */
        $routeMatch = $mvcEvent->getRouteMatch();

        return new ViewRouteMatch($routeMatch);
    }
}

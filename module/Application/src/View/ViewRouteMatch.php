<?php

namespace Application\View;

use Laminas\Router\Http\RouteMatch;
use Laminas\View\Helper\HelperInterface;
use Laminas\View\Renderer\RendererInterface as Renderer;

class ViewRouteMatch implements HelperInterface
{
    protected ?Renderer $view;
    private ?RouteMatch $routeMatch = null;

    public function setView(Renderer $view): HelperInterface|static
    {
        $this->view = $view;
        return $this;
    }

    public function getView(): ?Renderer
    {
        return $this->view;
    }

    public function __construct(?RouteMatch $routeMatch = null)
    {
        $this->routeMatch = $routeMatch;
    }

    public function __invoke(): ?RouteMatch
    {
        return $this->routeMatch;
    }
}

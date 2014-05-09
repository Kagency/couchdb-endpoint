<?php

namespace Kagency\CouchdbEndpoint\Endpoint\Symfony;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Routing;
use Symfony\Component\HttpKernel;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

class Application
{
    /**
     * Collection of active routes
     *
     * @var RouteCollection
     */
    private $routes;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->routes = new RouteCollection();
    }

    /**
     * Add route
     *
     * @param string $method
     * @param string $path
     * @param Callback $callback
     * @return void
     */
    public function addRoute($method, $path, $callback)
    {
        $this->routes->add(
            count($this->routes) . '_' . $path,
            new Route(
                $path,
                array('_controller' => $callback),
                array('_method' => $method)
            )
        );
    }

    /**
     * Create HTTP kernel
     *
     * @return HttpKernel\HttpKernel
     */
    public function createHttpKernel()
    {
        $context = new Routing\RequestContext();
        $matcher = new Routing\Matcher\UrlMatcher($this->routes, $context);

        $errorHandler = array(new ErrorController, 'exception');

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new HttpKernel\EventListener\ExceptionListener($errorHandler));
        $dispatcher->addSubscriber(new HttpKernel\EventListener\RouterListener($matcher));
        $dispatcher->addSubscriber(new HttpKernel\EventListener\ResponseListener('UTF-8'));

        $resolver = new HttpKernel\Controller\ControllerResolver();

        return new HttpKernel\HttpKernel($dispatcher, $resolver);
    }
}


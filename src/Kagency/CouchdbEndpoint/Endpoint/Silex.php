<?php

namespace Kagency\CouchdbEndpoint\Endpoint;

use Kagency\CouchdbEndpoint\Endpoint;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Silex extends Endpoint
{
    /**
     * Silex application
     *
     * @var \Silex\Application
     */
    protected $app;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->app = new \Silex\Application();

        // @TODO: Configure routes
    }

    /**
     * Run endpoint
     *
     * @return void
     */
    public function run()
    {
        $this->app->run();
    }

    /**
     * Test run
     *
     * Method used to not fully run Silex, but execute the tests with a given
     * Request and return the created Reponse object for comparision.
     *
     * @param Request $request
     * @return Response
     */
    public function testRun(Request $request)
    {
        return $this->app->handle($request);
    }
}

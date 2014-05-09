<?php

namespace Kagency\CouchdbEndpoint\Endpoint;

use Kagency\CouchdbEndpoint\Endpoint;
use Kagency\CouchdbEndpoint\Container;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Symfony extends Endpoint
{
    /**
     * Silex application
     *
     * @var Symfony\Application
     */
    protected $application;

    /**
     * Dependency Injection Container
     *
     * @var Container
     */
    protected $container;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct(Container $container, $name = "storage")
    {
        $this->application = new Symfony\Application();
        $controller = new Symfony\Controller(
            $container->get('Kagency.CouchdbEndpoint.Replicator')
        );

        $this->application->addRoute('GET', "/{database}/", array($controller, 'getDatabaseStatus'));
        $this->application->addRoute('GET', "/{database}/_local/{revision}", array($controller, 'hasChange'));
        $this->application->addRoute('PUT', "/{database}/_local/{revision}", array($controller, 'storeSyncedChange'));
        $this->application->addRoute('POST', "/{database}/_revs_diff", array($controller, 'revisionDiff'));
        $this->application->addRoute('POST', "/{database}/_bulk_docs", array($controller, 'insertBulk'));
        $this->application->addRoute('POST', "/{database}/_ensure_full_commit", array($controller, 'commit'));
    }

    /**
     * Run endpoint
     *
     * @return void
     */
    public function run()
    {
        $request = Request::createFromGlobals();
        $response = $this->runRequest($request);
        $response->send();
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
    public function runRequest(Request $request)
    {
        $kernel = $this->application->createHttpKernel($request);
        return $kernel->handle($request);
    }
}

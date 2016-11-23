<?php

namespace Kagency\CouchdbEndpoint\Endpoint;

use Kagency\CouchdbEndpoint\Endpoint;
use Kagency\CouchdbEndpoint\Replicator;

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
     * Replicator
     *
     * @var Replicator
     */
    protected $replicator;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct(Replicator $replicator, $name = "storage")
    {
        $this->application = new Symfony\Application();
        $controller = new Symfony\Controller($replicator);

        $this->application->addRoute('GET', "/{database}/", array($controller, 'getDatabaseStatus'));
        $this->application->addRoute('GET', "/{database}", array($controller, 'getDatabaseStatus'));
        $this->application->addRoute('PUT', "/{database}/", array($controller, 'getDatabaseStatus'));
        $this->application->addRoute('PUT', "/{database}", array($controller, 'getDatabaseStatus'));
        $this->application->addRoute('GET', "/{database}/_local/{revision}", array($controller, 'hasChange'));
        $this->application->addRoute('PUT', "/{database}/_local/{revision}", array($controller, 'storeSyncedChange'));
        $this->application->addRoute('POST', "/{database}/_revs_diff", array($controller, 'revisionDiff'));
        $this->application->addRoute('POST', "/{database}/_bulk_docs", array($controller, 'insertBulk'));
        $this->application->addRoute('POST', "/{database}/_ensure_full_commit", array($controller, 'commit'));
        $this->application->addRoute('GET', "/{database}/_changes", array($controller, 'getChanges'));
        $this->application->addRoute('GET', "/{database}/_all_docs", array($controller, 'getAllDocs'));

        // Should be last, obviously
        $this->application->addRoute('GET', "/{database}/{document}", array($controller, 'getDocument'));
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

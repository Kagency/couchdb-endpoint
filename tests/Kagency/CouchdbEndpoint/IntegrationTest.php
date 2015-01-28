<?php

namespace Kagency\CouchdbEndpoint;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Kagency\HttpReplay\ResponseFilter;
use Kagency\HttpReplay\Reader;
use Kagency\HttpReplay\MessageHandler;

abstract class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Get replicator
     *
     * @return Replicator
     */
    abstract protected function getReplicator();

    /**
     * Get base path
     *
     * @return string
     */
    abstract protected function getBasePath();

    /**
     * Get fixtures
     *
     * @return array
     */
    public function getFixtures()
    {
        return array_map(
            function ($file) {
                return array(pathinfo($file, PATHINFO_FILENAME));
            },
            glob($this->getBasePath() . '/*.tns')
        );
    }

    /**
     * getRequests
     *
     * @param mixed $fixtureFile
     * @return void
     */
    protected function getRequests($finalFixtureFile)
    {
        $aggregate = array();
        $reader = new Reader\MitmDump(new MessageHandler\Symfony2());
        foreach (glob($this->getBasePath() . '/*.tns') as $fixtureFile) {
            $aggregate = array_merge(
                $aggregate,
                $reader->readInteractions($fixtureFile)
            );

            if (pathinfo($fixtureFile, PATHINFO_FILENAME) === $finalFixtureFile) {
                return $aggregate;
            }
        }

        throw new \RuntimeException("Unknown fixture file $finalFixtureFile");
    }

    /**
     * @dataProvider getFixtures
     */
    public function testReplayReplication($fixtureFile)
    {
        $filter = $this->getRequestFilter();
        $messageHandler = new MessageHandler\Symfony2();

        $dumps = $this->getRequests($fixtureFile);
        $replicator = $this->getReplicator();
        foreach ($dumps as $nr => $dump) {
            $request = $dump->request;
            $expectedResponse = $dump->response;

            $endpoint = new Endpoint\Symfony($replicator, "master");
            $actualResponse = $endpoint->runRequest($request);

            $this->assertEquals(
                $filter->filterResponse($messageHandler->simplifyResponse($request, $expectedResponse)),
                $filter->filterResponse($messageHandler->simplifyResponse($request, $actualResponse)),
                "Failed to respond to #$nr: $request"
            );
        }
    }

    /**
     * Get request filter
     *
     * @return ResponseFilter
     */
    protected function getRequestFilter()
    {
        return new ResponseFilter\Dispatcher(array(
            new ResponseFilter\Json(),
            new ResponseFilter\MultipartMixed(),
            new ResponseFilter\Headers(array(
                'server',
                'date',
                'content-length',
                'cache-control',
                'location',
                'etag',
                'transfer-encoding',
            )),
            new ResponseFilter\ConditionalPathRegexp(
                '(^/(?:master|api)/(?:\\?.*)?$)',
                new ResponseFilter\JsonFilter(array('data_size', 'disk_size', 'instance_start_time', 'disk_format_version'))
            ),
            new ResponseFilter\ConditionalPathRegexp(
                '(^/(?:master|api)/_ensure_full_commit$)',
                new ResponseFilter\JsonFilter(array('instance_start_time'))
            ),
            new ResponseFilter\ConditionalPathRegexp(
                '(^/(?:master|api)/_local/[a-zA-Z0-9=]+$)',
                new ResponseFilter\JsonFilter(array('rev', '_rev'))
            ),
        ));
    }
}

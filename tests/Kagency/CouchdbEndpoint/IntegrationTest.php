<?php

namespace Kagency\CouchdbEndpoint;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Kagency\HttpReplay\ResponseFilter;
use Kagency\HttpReplay\Reader;
use Kagency\HttpReplay\MessageHandler;

/**
 * @group integration
 */
class IntegrationTest extends \PHPUnit_Framework_TestCase
{
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
            glob(__DIR__ . '/_fixtures/*.tns')
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
        foreach (glob(__DIR__ . '/_fixtures/*.tns') as $fixtureFile) {
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
        $container = new Container();
        $replicator = $container->get('Kagency.CouchdbEndpoint.Replicator.Test');
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
                '(^/master/$)',
                new ResponseFilter\JsonFilter(array('data_size', 'disk_size', 'instance_start_time', 'disk_format_version'))
            ),
            new ResponseFilter\ConditionalPathRegexp(
                '(^/master/_ensure_full_commit$)',
                new ResponseFilter\JsonFilter(array('instance_start_time'))
            ),
            new ResponseFilter\ConditionalPathRegexp(
                '(^/master/_local/[a-f0-9]{32}$)',
                new ResponseFilter\JsonFilter(array('rev', '_rev'))
            ),
        ));
    }
}

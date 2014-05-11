<?php

namespace Kagency\CouchdbEndpoint;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Interaction {
    public $request;
    public $response;

    /**
     * __construct
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}

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
        $dumps = array();
        $aggregate = array();
        $decoder = new \TNetstring_Decoder();
        foreach (glob(__DIR__ . '/_fixtures/*.tns') as $fixtureFile) {
            $aggregate = array_merge(
                $aggregate,
                array_map(
                    function (array $interaction) {
                        return new Interaction(
                            Request::create(
                                $interaction['request']['path'],
                                $interaction['request']['method'],
                                array(),
                                array(),
                                array(),
                                $this->mapHeaders($interaction['request']['headers']),
                                $interaction['request']['content']
                            ),
                            Response::create(
                                $interaction['response']['content'],
                                $interaction['response']['code'],
                                $this->mapHeaders($interaction['response']['headers'], '')
                            )
                        );
                    },
                    $decoder->decode(file_get_contents($fixtureFile))
                )
            );
            $dumps[] = array($aggregate);
        }

        return $dumps;
    }

    /**
     * Map headers
     *
     * Maps HTTP headers from the real names to the naems PHP would use in the
     * SERVER array, so that Symfony2 can map them back.
     *
     * @param array $headers
     * @return array
     */
    protected function mapHeaders(array $headers, $prefix = 'HTTP_')
    {
        $phpHeaders = array();
        foreach ($headers as $headerPair) {
            list($name, $value) = $headerPair;
            $phpHeaders[$prefix . str_replace('-', '_', strtoupper($name))] = $value;
        }

        return $phpHeaders;
    }

    /**
     * @dataProvider getFixtures
     */
    public function testReplayReplication(array $dumps)
    {
        $container = new Container();
        foreach ($dumps as $nr => $dump) {
            // This clone is ESSENTIAL.
            //
            // Symfony2 stores the matched controller inside the request. Since
            // we are replaying the requests from the first test in the second
            // tests and this uses the same request object, the route would not
            // be matched again. This causes the cotnroller from the first test
            // run being re-used, which also means that the first few requests
            // run against the storage from the first run.
            $request = clone $dump->request;
            $expectedResponse = $dump->response;

            $endpoint = new Endpoint\Symfony($container, "master");
            $response = $endpoint->runRequest($request);

            $this->assertEquals(
                $this->simplifyResponse($request->getPathInfo(), $expectedResponse),
                $this->simplifyResponse($request->getPathInfo(), $response),
                "Failed to respond to #$nr: $request"
            );
        }
    }

    /**
     * Simplify response
     *
     * simplifies responses for easier comparision. Also strips away headers,
     * which are not relevant to us.
     *
     * @param string $path
     * @param Response $response
     * @return array
     */
    protected function simplifyResponse($path, Response $response)
    {
        $headerBlackList = array(
            'server' => true,
            'date' => true,
            'content-length' => true,
            'cache-control' => true,
            'location' => true,
            'etag' => true,
            'transfer-encoding' => true,
        );

        $headers = array();
        foreach ($response->headers as $name => $value) {
            if (isset($headerBlackList[$name])) {
                continue;
            }

            $headers[$name] = reset($value);
        }

        switch (true) {
            case $response->headers->get('Content-Type') === 'application/json':
                $body = json_decode($response->getContent(), true);
                break;

            case preg_match(
                '(^multipart/mixed; boundary="(?P<boundary>[a-f0-9]+)"$)',
                $response->headers->get('Content-Type'),
                $match
            ):
                $body = str_replace($match['boundary'], '<boundary>', $response->getContent());
                $headers['content-type'] = str_replace($match['boundary'], '<boundary>', $headers['content-type']);
                break;
        }

        return array(
            'status' => $response->getStatusCode(),
            'headers' => $headers,
            'content' => $this->filterResponseData($path, $body),
        );
    }

    /**
     * Filter response data
     *
     * Based on the path, return filtered response data. Some bits of the data
     * are just useless to compare.
     *
     * @param string $path
     * @param mixed $data
     * @return array
     */
    protected function filterResponseData($path, $data)
    {
        switch (true) {
            case is_array($data) && $path === '/master/':
                return array_diff_key(
                    $data,
                    array_flip(array('data_size', 'disk_size', 'instance_start_time', 'disk_format_version'))
                );

            case is_array($data) && $path === '/master/_ensure_full_commit':
                return array_diff_key(
                    $data,
                    array_flip(array('instance_start_time'))
                );

            case is_array($data) && $path === '/master/_local/8e3cbbec10195c58326d22c4a4e64fb4':
            case is_array($data) && $path === '/master/_local/a09c2604722ab3602b17fb93c4d8af89':
                return array_diff_key(
                    $data,
                    array_flip(array('rev', '_rev'))
                );

            default:
                return $data;
        }
    }
}

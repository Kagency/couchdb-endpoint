<?php

namespace Kagency\CouchdbEndpoint;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $decoder = new \TNetstring_Decoder();
        foreach (glob(__DIR__ . '/_fixtures/*.tns') as $fixtureFile) {
            $dumps[] = array(
                array_map(
                    function (array $interaction) {
                        return array(
                            'request' => Request::create(
                                $interaction['request']['path'],
                                $interaction['request']['method'],
                                array(),
                                array(),
                                array(),
                                $this->mapHeaders($interaction['request']['headers']),
                                $interaction['request']['content']
                            ),
                            'response' => Response::create(
                                $interaction['response']['content'],
                                $interaction['response']['code'],
                                $this->mapHeaders($interaction['response']['headers'], '')
                            ),
                        );
                    },
                    $decoder->decode(file_get_contents($fixtureFile))
                )
            );
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
        foreach ($dumps as $dump) {
            $request = $dump['request'];
            $expectedResponse = $dump['response'];

            $endpoint = new Endpoint\Silex(new Container(), "master");
            $response = $endpoint->testRun($request);

            $this->assertEquals(
                $this->simplifyResponse($request->getPathInfo(), $expectedResponse),
                $this->simplifyResponse($request->getPathInfo(), $response),
                "Failed to respond to: " . $request
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
        );

        $headers = array();
        foreach ($response->headers as $name => $value) {
            if (isset($headerBlackList[$name])) {
                continue;
            }

            $headers[$name] = reset($value);
        }

        return array(
            'status' => $response->getStatusCode(),
            'headers' => $headers,
            'content' => $this->filterResponseData(
                $path,
                json_decode($response->getContent(), true)
            ),
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
                    array_flip(array('disk_size', 'instance_start_time', 'disk_format_version'))
                );

            default:
                return $data;
        }
    }
}

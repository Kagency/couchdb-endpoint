<?php

namespace Kagency\CouchdbEndpoint\Endpoint\Symfony\Response;

use Symfony\Component\HttpFoundation\Response;

class MultipartMixed extends Response
{
    /**
     * Aggregated responses
     *
     * @var Response[]
     */
    protected $responses;

    /**
     * Boundary
     *
     * @var string
     */
    protected $boundary;

    /**
     * Constructor.
     *
     * @param mixed   $data    The response data
     * @param int     $status  The response status code
     * @param array   $headers An array of response headers
     */
    public function __construct(array $responses = array(), $status = 200, $headers = array())
    {
        parent::__construct('', $status, $headers);

        foreach ($responses as $response) {
            $this->addResponse($response);
        }
    }

    /**
     * Add response
     *
     * @param Response $response
     * @return void
     */
    public function addResponse(Response $response)
    {
        $this->responses[] = $response;
        $this->update();
    }

    /**
     * Get boundary
     *
     * @return string
     */
    protected function getBoundary()
    {
        if (!$this->boundary) {
            $this->boundary = md5(microtime());
        }

        return $this->boundary;
    }

    /**
     * {@inheritdoc}
     */
    public static function create($data = '', $status = 200, $headers = array())
    {
        throw new \Exception(
            "Defining interfaces for constructors or factory methods does not make any fucking sense. " .
            "Dependencies of objects vary."
        );
    }

    /**
     * Updates the content and headers according to the json data and callback.
     *
     * @return JsonResponse
     */
    protected function update()
    {
        $boundary = $this->getBoundary();
        $this->headers->set('Content-Type', "multipart/mixed; boundary=\"{$boundary}\"");

        $content = '';
        foreach ($this->responses as $response) {
            $content .= "--$boundary\r\n";
            $content .= "Content-Type: " . $response->headers->get('Content-Type') . "\r\n\r\n";
            $content .= $response->getContent() . "\r\n";
        }
        $content .= "--$boundary--";

        $this->setContent($content);
    }
}

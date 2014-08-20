<?php

namespace Kagency\CouchdbEndpoint\Endpoint\Symfony;

use Kagency\CouchdbEndpoint\Replicator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ErrorController
{
    /**
     * Exception
     *
     * @param \Exception $exception
     * @return JsonResponse
     */
    public function exception($exception)
    {
        return new JsonResponse(
            new Replicator\Error(
                'internal',
                $exception->getMessage()
            ),
            500
        );
    }
}

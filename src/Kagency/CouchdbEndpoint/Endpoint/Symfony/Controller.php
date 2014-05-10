<?php

namespace Kagency\CouchdbEndpoint\Endpoint\Symfony;

use Kagency\CouchdbEndpoint\Replicator;
use Kagency\CouchdbEndpoint\Endpoint\Symfony\Response\MultipartMixed;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class Controller
{
    /**
     * Replicator
     *
     * @var Replicator
     */
    protected $replicator;

    /**
     * __construct
     *
     * @param Replicator $replicator
     * @return void
     */
    public function __construct(Replicator $replicator)
    {
        $this->replicator = $replicator;
    }

    /**
     * Get database status
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getDatabaseStatus(Request $request)
    {
        return new JsonResponse(
            $this->replicator->getDatabaseStatus(
                $request->get('database')
            )
        );
    }

    /**
     * Get document
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getDocument(Request $request)
    {
        return new MultipartMixed(
            array(
                new JsonResponse(
                    $this->replicator->getDocument(
                        $request->get('document')
                    )
                ),
            )
        );
    }

    /**
     * Check if change exists
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function hasChange(Request $request)
    {
        $result = $this->replicator->hasChange(
            $request->get('database'),
            $request->get('revision')
        );

        return new JsonResponse(
            $result,
            $result instanceof Replicator\Error ? 404 : 200
        );
    }

    /**
     * Stored synced change
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storeSyncedChange(Request $request)
    {
        $revisionDocument = json_decode($request->getContent(), true);
        return new JsonResponse(
            $this->replicator->storeSyncedChange(
                $revisionDocument
            ),
            201
        );
    }

    /**
     * Calculate revision diff
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function revisionDiff(Request $request)
    {
        return new JsonResponse(
            $this->replicator->revisionDiff(
                json_decode($request->getContent(), true)
            )
        );
    }

    /**
     * Insert bulk
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function insertBulk(Request $request)
    {
        return new JsonResponse(
            $this->replicator->insertBulk(
                json_decode($request->getContent(), true)
            ),
            201
        );
    }

    /**
     * Get changes
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getChanges(Request $request)
    {
        // @TODO: Handle feed and style options, I gues those are
        // output options.
        //
        // @TODO: Should we do something with the heartbeat parameter?

        return new JsonResponse(
            $this->replicator->getChanges(
                $request->get('since')
            ),
            200
        );
    }

    /**
     * Commit
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function commit(Request $request)
    {
        return new JsonResponse(
            $this->replicator->commit(),
            201
        );
    }
}

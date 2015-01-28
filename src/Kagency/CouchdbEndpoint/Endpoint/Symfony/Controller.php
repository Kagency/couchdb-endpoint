<?php

namespace Kagency\CouchdbEndpoint\Endpoint\Symfony;

use Kagency\CouchdbEndpoint\Replicator;
use Kagency\CouchdbEndpoint\Endpoint\Symfony\Response\MultipartMixed;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class: Controller
 *
 * Symfony2 specific controller
 *
 * This class should not do ANYTHING besides mapping the incoming request to
 * replicator calls and creating the outgoing response. There MUST NOT be any
 * logic besides that.
 *
 * @version $Revision$
 */
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
        $result = $this->replicator->getDocuments(
            $request->get('document'),
            $request->get('rev', null),
            $request->get('revs', false),
            json_decode($request->get('latest', 'false')),
            json_decode($request->get('open_revs', '[]'))
        );

        if ($result instanceof Replicator\Error) {
            return new JsonResponse($result, 404);
        }

        return new MultipartMixed(
            array_map(
                function ($document) {
                    return new JsonResponse($document);
                },
                $result
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
        $result = $this->replicator->getSyncedChange(
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
     * @TODO: Should we do something with the heartbeat parameter?
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getChanges(Request $request)
    {
        switch ($feed = $request->get('feed', 'normal')) {
            case 'normal':
                return $this->getNormalChanges($request);
            case 'longpoll':
                return $this->getLongpollChanges($request);

            default:
                throw new \OutOfBoundsException("Unknown feed style $feed");
        }
    }

    /**
     * Get changes in style "longpoll"
     *
     * @param Request $request
     * @return JsonResponse
     */
    protected function getLongpollChanges(Request $request)
    {
        $since = $request->get('since', 0);
        $tries = 0;
        $sleepTime = 250 * 1000;
        $maxTries = $request->get('timeout', 60000) * 1000 / $sleepTime;

        do {
            if ($tries++) {
                usleep($sleepTime);
            }
            $changes = $this->replicator->getChanges($since);
        } while (!count($changes->results) && ($tries < $maxTries));

        return new JsonResponse($changes, 200);
    }

    /**
     * Get changes in style "normal"
     *
     * @param Request $request
     * @return JsonResponse
     */
    protected function getNormalChanges(Request $request)
    {
        return new JsonResponse(
            $this->replicator->getChanges(
                $request->get('since', 0)
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

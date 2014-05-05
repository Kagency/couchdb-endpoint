<?php

namespace Kagency\CouchdbEndpoint;

class Replicator
{
    /**
     * Get database status
     *
     * @param string $database
     * @return Replicator\DatabaseStatus
     */
    public function getDatabaseStatus($database)
    {
        return new Replicator\DatabaseStatus($database);
    }

    /**
     * Check if change exists
     *
     * @param string $database
     * @param string $revision
     * @return JsonResponse
     */
    public function hasChange($database, $revision)
    {
        return new Replicator\Error('not_found', 'missing');
    }

    /**
     * Build revision diff
     *
     * @param string $database
     * @param string $revision
     * @return JsonResponse
     */
    public function revisionDiff(array $documents)
    {
        return array_map(
            function ($revision) {
                return array(
                    'missing' => $revision,
                );
            },
            $documents
        );
    }
}

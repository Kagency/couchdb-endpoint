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
     * @return Replicator\Error
     */
    public function hasChange($database, $revision)
    {
        return new Replicator\Error('not_found', 'missing');
    }

    /**
     * Build revision diff
     *
     * @param array $documentRevisions
     * @param string $revision
     * @return array
     */
    public function revisionDiff(array $documentRevisions)
    {
        return array_map(
            function ($revision) {
                return array(
                    'missing' => $revision,
                );
            },
            $documentRevisions
        );
    }

    /**
     * Insert bulk
     *
     * @param array $documents
     * @return void
     */
    public function insertBulk(array $documents)
    {
        return null;
    }

    /**
     * Commit
     *
     * @return Replicator\OK
     */
    public function commit()
    {
        return new Replicator\OK();
    }

    /**
     * Store synced change
     *
     * @param array $revisionDocument
     * @return void
     */
    public function storeSyncedChange(array $revisionDocument)
    {
        return new Replicator\DocumentCreated(
            $revisionDocument['_id'],
            1
        );
    }
}

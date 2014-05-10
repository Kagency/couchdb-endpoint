<?php

namespace Kagency\CouchdbEndpoint;

class Replicator
{
    /**
     * Storage
     *
     * @var Storage
     */
    public $storage;

    /**
     * __construct
     *
     * @param Storage $storage
     * @return void
     */
    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Get database status
     *
     * @param string $database
     * @return Replicator\DatabaseStatus
     */
    public function getDatabaseStatus($database)
    {
        $status = new Replicator\DatabaseStatus($database);
        $status->doc_count = $this->storage->getDocumentCount();
        $status->update_seq = $this->storage->getUpdateSequence();
        $status->committed_update_seq = $status->update_seq;

        return $status;
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
     * @param array $updates
     * @return void
     */
    public function insertBulk(array $updates)
    {
        $this->storage->updateDocuments($updates['new_edits'] ?: array());
        $this->storage->storeDocuments($updates['docs'] ?: array());
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

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
     * Get document
     *
     * @param string $documentId
     * @param mixed $revision
     * @param bool $getRevisions
     * @param bool $getLatest
     * @param array $revisions
     * @return array
     */
    public function getDocuments($documentId, $revision, $getRevisions, $getLatest, array $revisions)
    {
        try {
            return array(
                $this->storage->getDocument($documentId),
            );
        } catch (\OutOfBoundsException $e) {
            return new Replicator\Error('not_found', 'missing');
        }
    }

    /**
     * Check if change exists
     *
     * @param string $database
     * @param string $revision
     * @return Replicator\Error
     */
    public function getSyncedChange($database, $revision)
    {
        try {
            return $this->storage->getSyncedChange($revision);
        } catch (\OutOfBoundsException $e) {
            return new Replicator\Error('not_found', 'missing');
        }
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
        return $this->storage->calculateRevisionDiff($documentRevisions);
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
     * Get changes
     *
     * @param string $since
     * @return array
     */
    public function getChanges($since)
    {
        return new Replicator\Changes(
            $this->storage->getChanges($since),
            $this->storage->getUpdateSequence()
        );
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
        $this->storage->storeSyncedChange($revisionDocument);
        return new Replicator\DocumentCreated(
            $revisionDocument['_id'],
            1
        );
    }
}

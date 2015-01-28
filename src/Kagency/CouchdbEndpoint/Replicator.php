<?php

namespace Kagency\CouchdbEndpoint;

/**
 * Class: Replicator
 *
 * @version $Revision$
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * @return Document[]
     */
    public function getDocuments($documentId, $revision, $getRevisions, $getLatest, array $revisions)
    {
        try {
            $documents = array();
            if ($revision) {
                return array(
                    $this->storage->getDocument($documentId, $revision),
                );
            }

            if ($revisions) {
                return array_map(
                    function ($revision) use ($documentId) {
                        return $this->storage->getDocument($documentId, $revision);
                    },
                    $revisions
                );
            }

            $revision = $this->storage->getLastRevision($documentId);
            return array($this->storage->getDocument($documentId, $revision));
        } catch (\OutOfBoundsException $e) {
            return new Replicator\Error('not_found', 'missing');
        }
    }

    /**
     * Get all documents
     *
     * @param bool $includeDocs
     * @param array $keys
     * @param int $skip
     * @param int $limit
     * @return Document[]
     */
    public function getAllDocuments($includeDocs, array $keys, $skip = 0, $limit = null)
    {
        return new Replicator\Result(
            array_map(
                function ($documentId) {
                    $revision = $this->storage->getLastRevision($documentId);
                    $document = $this->storage->getDocument($documentId, $revision);
                    return new Replicator\Row(
                        $documentId,
                        $documentId,
                        array('rev' => $revision),
                        $document
                    );
                },
                $keys
            )
        );
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
        $this->storage->storeDocuments(
            array_map(
                function (array $document) {
                    return new Document($document);
                },
                $updates['docs'] ?: array()
            )
        );

        return array();
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
        return new Replicator\Commit();
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
            $this->storage->storeSyncedChange($revisionDocument)
        );
    }
}

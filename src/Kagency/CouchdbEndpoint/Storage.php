<?php

namespace Kagency\CouchdbEndpoint;

/**
 * Abstract storage base
 *
 * Base class for storage implementations. When implementing a custom storage
 * you probably want to use RevisionDiffer, ConflictDecider and
 * RevisionCalculator.
 *
 * @version $Revision$
 */
abstract class Storage
{
    /**
     * Get document count
     *
     * @return void
     */
    abstract public function getDocumentCount();

    /**
     * Get update sequence
     *
     * @return void
     */
    abstract public function getUpdateSequence();

    /**
     * Get document
     *
     * @param string $document
     * @return string
     */
    abstract public function getDocument($documentId, $revision);

    /**
     * Get last revision
     *
     * @param string $documentId
     * @return string
     */
    abstract public function getLastRevision($documentId);

    /**
     * Store documents
     *
     * @param Document[] $documents
     * @return void
     */
    abstract public function storeDocuments(array $documents);

    /**
     * Update documents
     *
     * @param array $documents
     * @return void
     */
    abstract public function updateDocuments(array $documents);

    /**
     * Get changes
     *
     * @param string $since
     * @return Storage\Update[]
     */
    abstract public function getChanges($since);

    /**
     * Calculate revision diff
     *
     * @param array $requestedRevisions
     * @return array
     */
    abstract public function calculateRevisionDiff(array $requestedRevisions);

    /**
     * Get synced change
     *
     * @param array $revision
     * @return void
     */
    abstract public function getSyncedChange($revision);

    /**
     * Store synced change
     *
     * Returns the revision of the created document.
     *
     * @param array $revisionDocument
     * @return string
     */
    abstract public function storeSyncedChange(array $revisionDocument);
}

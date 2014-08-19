<?php

namespace Kagency\CouchdbEndpoint\Storage;

use Kagency\CouchdbEndpoint\Storage;

use Kagency\CouchdbEndpoint\RevisionDiffer;
use Kagency\CouchdbEndpoint\ConflictDecider;
use Kagency\CouchdbEndpoint\RevisionCalculator;
use Kagency\CouchdbEndpoint\Document;

/**
 * In-Memory storage
 *
 * This is a storage implementation which only stores the documents in PHP
 * variables, aka: in memory.
 *
 * This class is only supposed to be used in tests.
 *
 * @version $Revision$
 */
class Memory extends Storage
{
    /**
     * Data
     *
     * @var array
     */
    private $data = array();

    /**
     * Updates
     *
     * @var int
     */
    private $updates = array();

    /**
     * Synced revisions
     *
     * @var array
     */
    private $syncedRevisions = array();

    /**
     * Revision differ
     *
     * @var RevisionDiffer
     */
    private $revisionDiffer;

    /**
     * Conflict decider
     *
     * @var ConflictDecider
     */
    private $conflictDecider;

    /**
     * Revision calculator
     *
     * @var RevisionCalculator
     */
    private $revisionCalculator;

    /**
     * __construct
     *
     * @param RevisionDiffer $revisionDiffer
     * @param ConflictDecider $conflictDecider
     * @return void
     */
    public function __construct(
        RevisionDiffer $revisionDiffer,
        ConflictDecider $conflictDecider,
        RevisionCalculator $revisionCalculator
    ) {
        $this->revisionDiffer = $revisionDiffer;
        $this->conflictDecider = $conflictDecider;
        $this->revisionCalculator = $revisionCalculator;
    }

    /**
     * Get document count
     *
     * @return void
     */
    public function getDocumentCount()
    {
        return count($this->data);
    }

    /**
     * Get update sequence
     *
     * @return void
     */
    public function getUpdateSequence()
    {
        if (!count($this->updates)) {
            return 0;
        }

        return max(array_keys($this->updates));
    }

    /**
     * Get document
     *
     * @param string $document
     * @return string
     */
    public function getDocument($documentId, $revision)
    {
        if (!isset($this->data[$documentId]) ||
            !isset($this->data[$documentId][$revision])) {
            throw new \OutOfBoundsException("No document with ID $document and revision $revision");
        }

        $document = clone $this->data[$documentId][$revision];

        if (isset($document->_conflict)) {
            unset($document->_conflict);
        }
        return $document;
    }

    /**
     * Get last revision
     *
     * @param string $documentId
     * @return string
     */
    public function getLastRevision($documentId)
    {
        if (!$revision = $this->getLastRevisionSilent($documentId)) {
            throw new \OutOfBoundsException("No document with ID $documentId");
        }

        return $revision;
    }

    /**
     * Get last revision
     *
     * Returns null, if no revision is available.
     *
     * @param string $documentId
     * @return mixed
     */
    protected function getLastRevisionSilent($documentId)
    {
        if (!isset($this->data[$documentId])) {
            return null;
        }

        $revisions = array_keys($this->data[$documentId]);
        foreach (array_reverse($revisions) as $revision) {
            if (!isset($this->data[$documentId][$revision]->_conflict)) {
                return $revision;
            }
        }

        return null;
    }

    /**
     * Store documents
     *
     * @param Document[] $documents
     * @return void
     */
    public function storeDocuments(array $documents)
    {
        foreach ($documents as $document) {
            $this->storeDocument($document);
        }
    }

    /**
     * Store document
     *
     * @param Document $document
     * @return void
     */
    protected function storeDocument(Document $document)
    {
        $lastRevision = $this->getLastRevisionSilent($document->_id);
        $this->data[$document->_id][$document->_rev] = $document;

        if ($this->revisionCalculator->getSequence($document->_rev) ===
            $this->revisionCalculator->getSequence($lastRevision)) {
            $this->conflictDecider->select($document, $this->data[$document->_id][$lastRevision]);
        }

        $sequence = count($this->updates) + 1;
        $this->updates[$sequence] = new Update(
            $sequence,
            $document->_id,
            array(
                new Revision($document->_rev),
            )
        );
    }

    /**
     * Update documents
     *
     * @param array $documents
     * @return void
     */
    public function updateDocuments(array $documents)
    {
        return null;
    }

    /**
     * Get changes
     *
     * @param string $since
     * @return Update[]
     */
    public function getChanges($since)
    {
        $filter = new ChangesFilter\Dispatcher(
            array(
                new ChangesFilter\Since($since),
                new ChangesFilter\ConflictMerger($this->revisionCalculator),
                new ChangesFilter\Dublicates(),
            )
        );

        return $filter->filterChanges($this->updates);
    }

    /**
     * Calculate revision diff
     *
     * @param array $requestedRevisions
     * @return array
     */
    public function calculateRevisionDiff(array $requestedRevisions)
    {
        $missingRevisions = array();
        foreach ($requestedRevisions as $documentId => $revisions) {
            $missingRevisions[$documentId] = $this->revisionDiffer->calculate(
                $revisions,
                isset($this->data[$documentId]) ? array_keys($this->data[$documentId]) : array()
            );
        }

        return array_filter($missingRevisions);
    }

    /**
     * Get synced change
     *
     * @param array $revision
     * @return void
     */
    public function getSyncedChange($revision)
    {
        if (!isset($this->syncedRevisions[$revision])) {
            throw new \OutOfBoundsException("Revision $revision not synchronized.");
        }

        $revisionDocument = $this->syncedRevisions[$revision];
        unset($revisionDocument['_revisions']);
        return $revisionDocument;
    }

    /**
     * Store synced change
     *
     * @param array $revisionDocument
     * @return void
     */
    public function storeSyncedChange(array $revisionDocument)
    {
        $id = substr($revisionDocument['_id'], strpos($revisionDocument['_id'], '/') + 1);
        $revisionDocument['_rev'] = $this->revisionCalculator->getNextRevision($revisionDocument);
        $this->syncedRevisions[$id] = $revisionDocument;
    }
}

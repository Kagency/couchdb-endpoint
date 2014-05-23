<?php

namespace Kagency\CouchdbEndpoint;

class Storage
{
    /**
     * data = array()
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
    public function __construct(RevisionDiffer $revisionDiffer, ConflictDecider $conflictDecider, RevisionCalculator $revisionCalculator)
    {
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

        return $this->data[$documentId][$revision];
    }

    /**
     * Get last revision
     *
     * @param string $documentId
     * @return string
     */
    public function getLastRevision($documentId)
    {
        if (!isset($this->data[$documentId])) {
            throw new \OutOfBoundsException("No document with ID $documentId");
        }

        $revisions = array_keys($this->data[$documentId]);
        return end($revisions);
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
        $documentId = $document->_id;
        $revision = $document->_rev;

        $this->data[$document->_id][$document->_rev] = $document;

        $sequence = count($this->updates) + 1;
        $this->updates[$sequence] = new Storage\Update(
            $sequence,
            $document->_id,
            array(
                array(
                    'rev' => $document->_rev,
                )
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
     * @return Storage\Update[]
     */
    public function getChanges($since)
    {
        $filter = new Storage\ChangesFilter\Dispatcher(
            array(
                new Storage\ChangesFilter\Since($since),
                new Storage\ChangesFilter\ConflictMerger($this->revisionCalculator),
                new Storage\ChangesFilter\Dublicates(),
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

<?php

namespace Kagency\CouchdbEndpoint\Storage;

use Kagency\CouchdbEndpoint\Storage;

use Kagency\CouchdbEndpoint\RevisionDiffer;
use Kagency\CouchdbEndpoint\ConflictDecider;
use Kagency\CouchdbEndpoint\RevisionCalculator;
use Kagency\CouchdbEndpoint\Document;

/**
 * MySQL storage
 *
 * This is an experimental implementation of a MySQL storage. It basically just
 * stores the documents as serialized documents in one dumb table.
 *
 * To make this usable the storage should allow to store certain docuemnts in
 * related tables.
 *
 * @version $Revision$
 */
class MySQL extends Storage
{
    /**
     * PDO database connection
     *
     * @var \PDO
     */
    private $database;

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
        RevisionCalculator $revisionCalculator,
        \PDO $database
    ) {
        $this->revisionDiffer = $revisionDiffer;
        $this->conflictDecider = $conflictDecider;
        $this->revisionCalculator = $revisionCalculator;
        $this->database = $database;
    }

    /**
     * Get document count
     *
     * @return void
     */
    public function getDocumentCount()
    {
        return (int) $this->database->query("SELECT COUNT(DISTINCT(d_id)) count FROM document;")->fetchColumn();
    }

    /**
     * Get update sequence
     *
     * @return void
     */
    public function getUpdateSequence()
    {
        return (int) $this->database->query("SELECT MAX(du_sequence) FROM document_update;")->fetchColumn();
    }

    /**
     * Get document
     *
     * @param string $document
     * @return string
     */
    public function getDocument($documentId, $revision)
    {
        $query = $this->database->prepare(
            "SELECT d_document document FROM document WHERE d_id = :id AND d_revision = :revision;"
        );
        $query->execute(array('id' => $documentId, 'revision' => $revision));
        $documents = $query->fetchAll();
        if (!count($documents)) {
            throw new \OutOfBoundsException("No document with ID $documentId and revision $revision");
        }

        return unserialize($documents[0]['document']);
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
        $query = $this->database->prepare(
            "SELECT d_revision revision FROM document_update WHERE d_id = :id ORDER BY du_sequence DESC LIMIT 1;"
        );
        $query->execute(array('id' => $documentId));
        $documents = $query->fetchAll();
        if (!count($documents)) {
            return null;
        }

        return $documents[0]['revision'];
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
        $query = $this->database->prepare(
            "INSERT INTO document (d_id, d_revision, d_document) VALUES (:id, :revision, :document);"
        );
        $query->execute(
            array(
                'id' => $document->_id,
                'revision' => $document->_rev,
                'document' => serialize($document),
            )
        );

        $lastRevision = $this->getLastRevisionSilent($document->_id);
        if ($this->revisionCalculator->getSequence($document->_rev) ===
            $this->revisionCalculator->getSequence($lastRevision)) {
            $this->conflictDecider->select($document, $this->getDocument($document->_id, $lastRevision));
        }

        $sequence = $this->getUpdateSequence() + 1;
        $query = $this->database->prepare(
            "INSERT INTO document_update (du_sequence, d_id, d_revision) VALUES (:sequence, :id, :revision);"
        );
        $query->execute(
            array(
                'sequence' => $sequence,
                'id' => $document->_id,
                'revision' => $document->_rev,
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
        $query = $this->database->prepare(
            "SELECT
                du_sequence sequence,
                d_id id,
                d_revision revision
            FROM
                document_update
            WHERE
                du_sequence > :sequence
            ORDER BY
                du_sequence ASC;"
        );
        $query->execute(array('sequence' => $since));
        $updates = $query->fetchAll();
        $updates = array_map(
            function ($row) {
                return new Update(
                    $row['sequence'],
                    $row['id'],
                    array(new Revision($row['revision']))
                );
            },
            $updates
        );

        $filter = new ChangesFilter\Dispatcher(
            array(
                new ChangesFilter\ConflictMerger($this->revisionCalculator),
                new ChangesFilter\Dublicates(),
            )
        );

        return $filter->filterChanges($updates);
    }

    /**
     * Calculate revision diff
     *
     * Returns an array of revisions for each document which are not in the
     * database yet.
     *
     * @param array $requestedRevisions
     * @return array
     */
    public function calculateRevisionDiff(array $requestedRevisions)
    {
        $result = $this->database->query(
            "SELECT
                d_id id,
                d_revision revision
            FROM
                document
            WHERE
                d_id IN (" .
            implode(
                ', ',
                array_map(
                    array($this->database, 'quote'),
                    array_keys($requestedRevisions)
                )
            ) .
            ");"
        )->fetchAll();
        $availableRevisions = array();
        foreach ($result as $row) {
            if (!isset($availableRevisions[$row['id']])) {
                $availableRevisions[$row['id']] = array();
            }
            $availableRevisions[$row['id']][] = $row['revision'];
        }

        $missingRevisions = array();
        foreach ($requestedRevisions as $documentId => $revisions) {
            $missingRevisions[$documentId] = $this->revisionDiffer->calculate(
                $revisions,
                isset($availableRevisions[$documentId]) ? $availableRevisions[$documentId] : array()
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
        $query = $this->database->prepare(
            "SELECT r_revision revision FROM revision WHERE r_id = :id;"
        );
        $query->execute(array('id' => $revision));
        $revisions = $query->fetchAll();
        if (!count($revisions)) {
            throw new \OutOfBoundsException("Revision $revision not synchronized.");
        }

        $revisionDocument = unserialize($revisions[0]['revision']);
        unset($revisionDocument['_revisions']);
        return $revisionDocument;
    }

    /**
     * Store synced change
     *
     * Returns the revision of the created document.
     *
     * @param array $revisionDocument
     * @return string
     */
    public function storeSyncedChange(array $revisionDocument)
    {
        $revisionId = substr($revisionDocument['_id'], strpos($revisionDocument['_id'], '/') + 1);
        $revisionDocument['_rev'] = $this->revisionCalculator->getNextRevision($revisionDocument);

        $query = $this->database->prepare(
            "INSERT INTO
                revision (r_id, r_revision)
            VALUES
                (:id, :revision)
            ON DUPLICATE KEY UPDATE
                r_revision = VALUES(r_revision);"
        );
        $query->execute(
            array(
                'id' => $revisionId,
                'revision' => serialize($revisionDocument),
            )
        );

        return $revisionDocument['_rev'];
    }
}

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
     * @return mixed
     */
    public function getDocument($document)
    {
        if (!isset($this->data[$document])) {
            throw new \OutOfBoundsException("No document with ID $document");
        }

        return $this->data[$document];
    }

    /**
     * Store documents
     *
     * @param array $documents
     * @return void
     */
    public function storeDocuments(array $documents)
    {
        foreach ($documents as $document) {
            $this->data[$document['_id']] = $document;

            $sequence = count($this->updates) + 1;
            $this->updates[$sequence] = array(
                'id' => $document['_id'],
                'sequence' => $sequence,
                'revision' => $document['_rev'],
            );
        }
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
        return array_values(
            array_map(
                function ($update) {
                    return new Storage\Update(
                        $update['sequence'],
                        $update['id'],
                        array(
                            array(
                                'rev' => $update['revision'],
                            )
                        )
                    );
                },
                $this->updates
            )
        );
    }
}

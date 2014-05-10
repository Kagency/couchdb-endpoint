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
        return $this->getDocumentCount();
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
}

<?php

namespace Kagency\CouchdbEndpoint\Replicator;

/**
 * Class: DocumentCreated
 *
 * CouchDB message for created docuemnts
 *
 * @version $Revision$
 */
class DocumentCreated extends OK
{
    /**
     * ID
     *
     * @var string
     */
    public $id = null;

    /**
     * Revision
     *
     * @var string
     */
    public $rev = null;

    /**
     * __construct
     *
     * @param string $id
     * @param string $revision
     * @return void
     */
    public function __construct($id, $revision)
    {
        $this->id = $id;
        $this->rev = $revision;
    }
}

<?php

namespace Kagency\CouchdbEndpoint\RevisionDiffer;

use Kore\DataObject\DataObject;

class Missing extends DataObject
{
    /**
     * Missing revision
     *
     * @var array
     */
    public $missing = array();

    /**
     * __construct
     *
     * @param array $missingRevisions
     * @return void
     */
    public function __construct(array $missingRevisions)
    {
        $this->missing = $missingRevisions;
    }
}

<?php

namespace Kagency\CouchdbEndpoint\RevisionDiffer;

use Kagency\CouchdbEndpoint\Struct;

class Missing extends Struct
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

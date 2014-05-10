<?php

namespace Kagency\CouchdbEndpoint\Replicator;

use Kagency\CouchdbEndpoint\Struct;

/**
 * Class: Changes
 *
 * @version $Revision$
 */
class Changes extends Struct
{
    public $results = null;
    public $last_seq = 0;

    /**
     * __construct
     *
     * @param array $changes
     * @param mixed $lastSequence
     * @return void
     */
    public function __construct(array $changes, $lastSequence)
    {
        $this->results = $changes;
        $this->last_seq = $lastSequence;
    }
}

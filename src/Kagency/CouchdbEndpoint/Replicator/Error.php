<?php

namespace Kagency\CouchdbEndpoint\Replicator;

use Kore\DataObject\DataObject;

/**
 * Class: Error
 *
 * CouchDB error
 *
 * @version $Revision$
 */
class Error extends DataObject
{
    /**
     * Error type
     *
     * @var string
     */
    public $error;

    /**
     * Error reason
     *
     * @var string
     */
    public $reason;

    /**
     * __construct
     *
     * @param string $error
     * @param string $reason
     * @return void
     */
    public function __construct($error, $reason)
    {
        $this->error = $error;
        $this->reason = $reason;
    }
}

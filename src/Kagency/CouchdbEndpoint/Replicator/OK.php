<?php

namespace Kagency\CouchdbEndpoint\Replicator;

use Kore\DataObject\DataObject;

/**
 * Class: OK
 *
 * CouchDB success message
 *
 * @version $Revision$
 */
class OK extends DataObject
{
    /**
     * Everything is OK
     *
     * @var bool
     */
    public $ok = true;
}

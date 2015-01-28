<?php

namespace Kagency\CouchdbEndpoint\Replicator;

use Kore\DataObject\DataObject;

/**
 * Class: Result
 *
 * CouchDB success message
 *
 * @version $Revision$
 */
class Row extends DataObject
{
    public $id;
    public $key;
    public $value;
    public $doc;

    /**
     * __construct
     *
     * @param string $id
     * @param string $key
     * @param object $value
     * @param object $doc
     * @return void
     */
    public function __construct($id, $key, $value, $doc = null)
    {
        $this->id = $id;
        $this->key = $key;
        $this->value = $value;
        $this->doc = $doc;
    }
}

<?php

namespace Kagency\CouchdbEndpoint\Storage;

use Kore\DataObject\DataObject;

/**
 * Class: Update
 *
 * @version $Revision$
 */
class Update extends DataObject
{
    /**
     * Sequence
     *
     * @var int
     */
    public $seq;

    /**
     * ID
     *
     * @var string
     */
    public $id;

    /**
     * Changes
     *
     * @var array
     */
    public $changes = array();

    /**
     * __construct
     *
     * @param string $id
     * @param string $revision
     * @return void
     */
    public function __construct($sequence, $id, array $changes = array())
    {
        $this->seq = $sequence;
        $this->id = $id;
        $this->changes = $changes;
    }
}

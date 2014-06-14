<?php

namespace Kagency\CouchdbEndpoint\Storage;

use Kore\DataObject\DataObject;

/**
 * Class: Revision
 *
 * @version $Revision$
 */
class Revision extends DataObject
{
    /**
     * Revision
     *
     * @var string
     */
    public $rev;

    /**
     * __construct
     *
     * @param string $revision
     * @return void
     */
    public function __construct($revision)
    {
        $this->rev = $revision;
    }
}

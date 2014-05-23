<?php

namespace Kagency\CouchdbEndpoint\Storage;

use Kagency\CouchdbEndpoint\Struct;

/**
 * Class: Revision
 *
 * @version $Revision$
 */
class Revision extends Struct
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

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
class Result extends DataObject
{
    public $total_rows = 0;
    public $offset = 0;
    public $rows = array();

    /**
     * __construct
     *
     * @param array $rows
     * @param int $offset
     * @return void
     */
    public function __construct(array $rows, $offset = 0)
    {
        $this->total_rows = count($rows);
        $this->rows = $rows;
    }
}

<?php

namespace Kagency\CouchdbEndpoint\Replicator;

use Kagency\CouchdbEndpoint\Struct;

/**
 * Class: DatabaseStatus
 *
 * Representation for the data which is returned by CouchDB if a
 * database is requested directly.
 *
 * @TODO: Evaluate which data is really required by replication endpoints.
 *
 * @version $Revision$
 */
class DatabaseStatus extends Struct
{
    public $db_name = null;
    public $doc_count = 0;
    public $doc_del_count = 0;
    public $update_seq = 0;
    public $purge_seq = 0;
    public $compact_running = false;
    public $disk_size = 0;
    public $data_size = 0;
    public $instance_start_time = 0;
    public $disk_format_version = 0;
    public $committed_update_seq = 0;

    /**
     * __construct
     *
     * @param mixed $database
     * @return void
     */
    public function __construct($database)
    {
        $this->db_name = $database;
    }
}

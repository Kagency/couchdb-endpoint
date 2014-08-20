<?php

namespace Kagency\CouchdbEndpoint\Replicator;

class Commit extends OK
{
    /**
     * Time since the DB is open in microseconds
     *
     * @var string
     */
    public $instance_start_time = 0;
}

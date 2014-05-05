<?php

namespace Kagency\CouchdbEndpoint;

class Container extends \Pimple
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this['replicator'] = new Replicator();
    }
}

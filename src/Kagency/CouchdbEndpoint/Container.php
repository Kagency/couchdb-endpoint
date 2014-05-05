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

        $this['storage'] = new Storage();
        $this['replicator'] = new Replicator(
            $this['storage']
        );
    }
}

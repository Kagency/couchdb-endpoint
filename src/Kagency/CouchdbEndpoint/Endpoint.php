<?php

namespace Kagency\CouchdbEndpoint;

abstract class Endpoint
{
    /**
     * Run endpoint
     *
     * @return void
     */
    abstract public function run();
}

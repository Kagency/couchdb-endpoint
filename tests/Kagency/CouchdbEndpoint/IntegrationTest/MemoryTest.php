<?php

namespace Kagency\CouchdbEndpoint\IntegrationTest;

use Kagency\CouchdbEndpoint\IntegrationTest;

use Kagency\CouchdbEndpoint\Container;

require_once __DIR__ . '/../IntegrationTest.php';

/**
 * @group integration
 */
class MemoryTest extends IntegrationTest
{
    /**
     * Get replicator
     *
     * @return Replicator
     */
    protected function getReplicator()
    {
        $container = new Container();
        return $container->get('Kagency.CouchdbEndpoint.Replicator.Test');
    }
}

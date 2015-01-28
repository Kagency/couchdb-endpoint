<?php

namespace Kagency\CouchdbEndpoint;

use Kagency\CouchdbEndpoint\IntegrationTest;

use Kagency\CouchdbEndpoint\Container;

require_once __DIR__ . '/../IntegrationTest.php';

/**
 * @group integration
 */
class MySQLTest extends IntegrationTest
{
    /**
     * Container
     *
     * @var Container
     */
    protected $container;

    /**
     * Set up
     *
     * @return void
     */
    public function setUp()
    {
        $this->getContainer()->get('PDO')->query('TRUNCATE document;');
        $this->getContainer()->get('PDO')->query('TRUNCATE document_update;');
        $this->getContainer()->get('PDO')->query('TRUNCATE revision;');
    }

    /**
     * Get container
     *
     * @return Container
     */
    protected function getContainer()
    {
        if (!isset($this->container)) {
            $this->container = new Container();
        }

        return $this->container;
    }

    /**
     * Get replicator
     *
     * @return Replicator
     */
    protected function getReplicator()
    {
        return $this->getContainer()->get('Kagency.CouchdbEndpoint.Replicator');
    }

    /**
     * Get base path
     *
     * @return string
     */
    protected function getBasePath()
    {
        return __DIR__ . '/../_fixtures/couchdb/';
    }
}

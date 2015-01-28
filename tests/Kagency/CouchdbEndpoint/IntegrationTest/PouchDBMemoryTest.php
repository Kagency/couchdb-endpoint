<?php

namespace Kagency\CouchdbEndpoint\IntegrationTest;

use Kagency\CouchdbEndpoint\IntegrationTest;

use Kagency\CouchdbEndpoint\Container;

require_once __DIR__ . '/../IntegrationTest.php';

/**
 * @group integration
 */
class PouchDBMemoryTest extends IntegrationTest
{
    /**
     * Replicator
     *
     * @var Replicator
     */
    protected $replicator;

    /**
     * Get replicator
     *
     * @return Replicator
     */
    protected function getReplicator()
    {
        if (!$this->replicator) {
            $container = new Container();
            $this->replicator = $container->get('Kagency.CouchdbEndpoint.Replicator.Test');
        }

        return $this->replicator;
    }

    /**
     * Get base path
     *
     * @return string
     */
    protected function getBasePath()
    {
        return __DIR__ . '/../_fixtures/pouchdb/';
    }

    /**
     * Set up
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $replicator = $this->getReplicator();
        $replicator->insertBulk(
            array(
                'new_edits' => array(),
                'docs' => array(
                    array(
                        '_id' => 'test-1',
                        '_rev' => '1-57d57cf6f5e561d8c795ce22fbb3589f',
                        'data' => 'Version 1',
                    )
                ),
            )
        );
    }
}

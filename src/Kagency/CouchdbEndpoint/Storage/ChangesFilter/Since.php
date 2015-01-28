<?php

namespace Kagency\CouchdbEndpoint\Storage\ChangesFilter;

use Kagency\CouchdbEndpoint\Storage\ChangesFilter;
use Kagency\CouchdbEndpoint\Storage\Update;

class Since extends ChangesFilter
{
    /**
     * Since
     *
     * @var string
     */
    private $since;

    /**
     * __construct
     *
     * @param string $since
     * @return void
     */
    public function __construct($since)
    {
        $this->since = $since;
    }

    /**
     * Filter changes
     *
     * @param Update[] $changes
     * @return Update[]
     */
    public function filterChanges(array $changes)
    {
        return array_values(
            array_filter(
                $changes,
                function (Update $update) {
                    return $update->seq > $this->since;
                }
            )
        );
    }
}

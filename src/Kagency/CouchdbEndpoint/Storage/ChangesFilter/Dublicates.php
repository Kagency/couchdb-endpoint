<?php

namespace Kagency\CouchdbEndpoint\Storage\ChangesFilter;

use Kagency\CouchdbEndpoint\Storage\ChangesFilter;
use Kagency\CouchdbEndpoint\Storage\Update;

class Dublicates extends ChangesFilter
{
    /**
     * Filter changes
     *
     * @param Update[] $changes
     * @return Update[]
     */
    public function filterChanges(array $changes)
    {
        $sequenceMap = array();
        foreach ($changes as $update) {
            $sequenceMap[$update->id][] = $update->seq;
        }

        $sequenceMap = array_map(
            function ($sequences) {
                return array_slice($sequences, 0, -1);
            },
            $sequenceMap
        );

        return array_values(
            array_filter(
                $changes,
                function ($change) use ($sequenceMap) {
                    return !in_array(
                        $change->seq,
                        $sequenceMap[$change->id]
                    );
                }
            )
        );
    }
}

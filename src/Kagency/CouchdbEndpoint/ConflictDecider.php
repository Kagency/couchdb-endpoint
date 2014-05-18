<?php

namespace Kagency\CouchdbEndpoint;

class ConflictDecider
{
    /**
     * Select conflict winner
     *
     * @param array $doc1
     * @param array $doc2
     * @return array
     */
    public function select(array $doc1, array $doc2)
    {
        return $doc1;
    }
}

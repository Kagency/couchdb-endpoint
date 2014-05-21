<?php

namespace Kagency\CouchdbEndpoint;

class RevisionCalculator
{
    /**
     * Get revision sequence
     *
     * @param string $revision
     * @return int
     */
    public function getSequence($revision)
    {
        if (preg_match('(^(?P<sequence>\\d+)-[a-f0-9]+$)', $revision, $match)) {
            return (int) $match['sequence'];
        }

        return PHP_INT_MAX;
    }

    /**
     * Get next revision
     *
     * @param mixed array $document
     * @return void
     */
    public function getNextRevision(array $document)
    {
        $revision = isset($document['_rev']) ? $document['_rev'] : '0-0';

        $hash = md5(json_encode($document));
        $sequence = $this->getSequence($revision) + 1;
        return "$sequence-$hash";
    }
}

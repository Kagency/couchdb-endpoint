<?php

namespace Kagency\CouchdbEndpoint;

class RevisionDiffer
{
    /**
     * Calculate revision diff
     *
     * Calculate the revision diff from a given set of missing revisions and
     * the optional last revision currently stored.
     *
     * Currently we never have multiple "old" revisions available, so that we
     * can only like an always compacted CouchDB. This should be sufficient,
     * but it might be sensible to implement this in a different way for a
     * different storage.
     *
     * @param array $missingRevision
     * @param string $lastRevision
     * @return RevisionDiffer\Missing
     */
    public function calculate(array $missingRevisions, $lastRevision)
    {
        if (!$lastRevision) {
            return new RevisionDiffer\Missing($missingRevisions);
        }

        $lowestMissingRevisionSequence = min(
            array_map(
                array($this, 'getRevisionSequence'),
                $missingRevisions
            )
        );

        if ($lowestMissingRevisionSequence <= $this->getRevisionSequence($lastRevision)) {
            return new RevisionDiffer\Missing($missingRevisions);
        }

        return new RevisionDiffer\PotentialAncestor(
            array($lastRevision),
            $missingRevisions
        );
    }

    /**
     * Get revision sequence
     *
     * @param string $revision
     * @return int
     */
    protected function getRevisionSequence($revision)
    {
        if (preg_match('(^(?P<sequence>\\d+)-[a-f0-9]+$)', $revision, $match)) {
            return (int) $match['sequence'];
        }

        return PHP_INT_MAX;
    }
}

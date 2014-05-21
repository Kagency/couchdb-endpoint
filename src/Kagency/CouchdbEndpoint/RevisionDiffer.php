<?php

namespace Kagency\CouchdbEndpoint;

class RevisionDiffer
{
    /**
     * Revision calculator
     *
     * @var RevisionCalculator
     */
    private $revisionCalculator;

    /**
     * __construct
     *
     * @param RevisionCalculator $revisionCalculator
     * @return void
     */
    public function __construct(RevisionCalculator $revisionCalculator)
    {
        $this->revisionCalculator = $revisionCalculator;
    }

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
        if ($missingRevisions === array($lastRevision)) {
            return null;
        }

        if (!$lastRevision) {
            return new RevisionDiffer\Missing($missingRevisions);
        }

        $lowestMissingRevisionSequence = min(
            array_map(
                array($this->revisionCalculator, 'getSequence'),
                $missingRevisions
            )
        );

        if ($lowestMissingRevisionSequence <= $this->revisionCalculator->getSequence($lastRevision)) {
            return new RevisionDiffer\Missing($missingRevisions);
        }

        return new RevisionDiffer\PotentialAncestor(
            array($lastRevision),
            $missingRevisions
        );
    }
}

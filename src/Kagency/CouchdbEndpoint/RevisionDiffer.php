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
     * the locally stored revisiosns.
     *
     * @param array $requestedRevisions
     * @param array $localRevisions
     * @return RevisionDiffer\Missing
     */
    public function calculate(array $requestedRevisions, array $localRevisions)
    {
        $missingRevisions = array_diff($requestedRevisions, $localRevisions);
        if (!count($missingRevisions)) {
            return null;
        }

        if (!count($localRevisions)) {
            return new RevisionDiffer\Missing($missingRevisions);
        }

        $lowestMissingRevisionSequence = min(
            array_map(
                array($this->revisionCalculator, 'getSequence'),
                $missingRevisions
            )
        );

        $highestLocalRevisionSequence = max(
            array_map(
                array($this->revisionCalculator, 'getSequence'),
                $localRevisions
            )
        );

        if ($lowestMissingRevisionSequence <= $highestLocalRevisionSequence) {
            return new RevisionDiffer\Missing($missingRevisions);
        }

        return new RevisionDiffer\PotentialAncestor(
            array(end($localRevisions)),
            $missingRevisions
        );
    }
}

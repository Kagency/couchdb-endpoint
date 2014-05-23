<?php

namespace Kagency\CouchdbEndpoint\Storage\ChangesFilter;

use Kagency\CouchdbEndpoint\Storage\ChangesFilter;
use Kagency\CouchdbEndpoint\Storage\Update;
use Kagency\CouchdbEndpoint\RevisionCalculator;

class ConflictMerger extends ChangesFilter
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
     * Filter changes
     *
     * @param Update[] $changes
     * @return Update[]
     */
    public function filterChanges(array $changes)
    {
        $sequenceMap = array();
        foreach ($changes as $index => $update) {
            $revisionSequence = $this->revisionCalculator->getSequence(
                $update->changes[0]['rev']
            );
            $sequenceMap[$update->id][$revisionSequence][] = $index;
        }

        foreach ($sequenceMap as $documentId => $documentChanges) {
            foreach ($documentChanges as $revisionSequence => $revisionChanges) {
                if (count($revisionChanges) > 1) {
                    $firstChange = array_shift($revisionChanges);
                    foreach ($revisionChanges as $index) {
                        $changes[$firstChange] = clone $changes[$firstChange];
                        $changes[$firstChange]->changes = array_merge(
                            $changes[$firstChange]->changes,
                            $changes[$index]->changes
                        );
                        $changes[$firstChange]->seq = max(
                            $changes[$firstChange]->seq,
                            $changes[$index]->seq
                        );
                        unset($changes[$index]);
                    }
                }
            }
        }

        return array_values($changes);
    }
}

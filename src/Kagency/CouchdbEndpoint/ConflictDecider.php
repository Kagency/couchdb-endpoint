<?php

namespace Kagency\CouchdbEndpoint;

class ConflictDecider
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
     * Select conflict winner
     *
     * @param array $doc1
     * @param array $doc2
     * @return array
     */
    public function select(array $doc1, array $doc2)
    {
        $doc1Sequence = $this->revisionCalculator->getSequence($doc1['_rev']);
        $doc2Sequence = $this->revisionCalculator->getSequence($doc2['_rev']);

        if (!$doc1Sequence !== $doc2Sequence) {
            return $doc1Sequence > $doc2Sequence ? $doc1 : $doc2;
        }

        $doc1Size = strlen(json_encode($doc1));
        $doc2Size = strlen(json_encode($doc2));

        if (!$doc1Size !== $doc2Size) {
            return $doc1Size > $doc2Size ? $doc1 : $doc2;
        }

        // We do not care
        return $doc1;
    }
}

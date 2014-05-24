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
     * @param Document $new
     * @param Document $existing
     * @return void
     */
    public function select(Document $new, Document $existing)
    {
        $newSize = strlen(json_encode($new));
        $existingSize = strlen(json_encode($existing));

        if ($newSize !== $existingSize) {
            return $this->flagWinner(
                $newSize > $existingSize ? $new : $existing,
                $newSize > $existingSize ? $existing : $new
            );
        }

        // @TODO: Just select one of the documents randomly. This requires more
        // checking.
        return $this->flagWinner($new, $existing);
    }

    /**
     * Flag winner document
     *
     * @param Document $winner
     * @param Document $looser
     * @return void
     */
    protected function flagWinner(Document $winner, Document $looser)
    {
        $looser->_conflict = true;
    }
}

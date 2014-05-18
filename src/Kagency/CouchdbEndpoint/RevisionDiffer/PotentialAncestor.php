<?php

namespace Kagency\CouchdbEndpoint\RevisionDiffer;

class PotentialAncestor extends Missing
{
    /**
     * Potential ancestors
     *
     * @var array
     */
    public $possible_ancestors = array();

    /**
     * __construct
     *
     * @param array $missingRevisions
     * @param array $potentialAncestors
     * @return void
     */
    public function __construct(array $potentialAncestors, array $missingRevisions)
    {
        parent::__construct($missingRevisions);
        $this->possible_ancestors = $potentialAncestors;
    }
}

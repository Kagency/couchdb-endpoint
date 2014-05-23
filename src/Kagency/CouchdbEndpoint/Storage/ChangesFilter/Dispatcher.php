<?php

namespace Kagency\CouchdbEndpoint\Storage\ChangesFilter;

use Kagency\CouchdbEndpoint\Storage\ChangesFilter;

class Dispatcher extends ChangesFilter
{
    /**
     * Filters
     *
     * @var ChangesFilter[]
     */
    private $filters;

    /**
     * __construct
     *
     * @param array $filters
     * @return void
     */
    public function __construct(array $filters = array())
    {
        foreach ($filters as $filter) {
            $this->appendFilter($filter);
        }
    }

    /**
     * Append filter
     *
     * @param ChangesFilter $filter
     * @return void
     */
    public function appendFilter(ChangesFilter $filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * Filter changes
     *
     * @param Update[] $changes
     * @return Update[]
     */
    public function filterChanges(array $changes)
    {
        foreach ($this->filters as $filter) {
            $changes = $filter->filterChanges($changes);
        }

        return $changes;
    }
}

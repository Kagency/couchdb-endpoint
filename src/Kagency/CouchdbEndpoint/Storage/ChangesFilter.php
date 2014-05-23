<?php

namespace Kagency\CouchdbEndpoint\Storage;

abstract class ChangesFilter
{
    /**
     * Filter changes
     *
     * @param Update[] $changes
     * @return Update[]
     */
    abstract public function filterChanges(array $changes);
}

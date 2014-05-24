<?php

namespace Kagency\CouchdbEndpoint;

class Document extends Struct
{
    // @codingStandardsIgnoreStart
    /**
     * Document ID
     *
     * @var string
     */
    public $_id;

    /**
     * Document Revision
     *
     * @var string
     */
    public $_rev;
    // @codingStandardsIgnoreEnd

    /**
     * Allows to set new properties, except those reserved by CouchDB
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        if ((strpos($name, '_') === 0) &&
            !in_array($name, array('_revisions', '_conflict'))) {
            throw new \UnexpectedValueException("Invalid potentially reserved property $name");
        }

        $this->$name = $value;
    }
}

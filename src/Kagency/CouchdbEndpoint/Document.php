<?php

namespace Kagency\CouchdbEndpoint;

use Kore\DataObject\DataObject;

class Document extends DataObject
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
            !in_array($name, array('_revisions', '_conflict', '_attachments'))) {
            throw new \UnexpectedValueException("Invalid potentially reserved property $name");
        }

        $this->$name = $value;
    }
}

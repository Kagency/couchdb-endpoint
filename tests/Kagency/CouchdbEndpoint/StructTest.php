<?php

namespace Kagency\CouchdbEndpoint;

class TestStruct extends Struct {
    public $property;
}

/**
 * @covers \Kagency\CouchdbEndpoint\Struct
 */
class StructTest extends \PHPUnit_Framework_TestCase
{
    public function testGetValue()
    {
        $struct = new TestStruct();

        $this->assertNull($struct->property);
    }

    public function testConstructor()
    {
        $struct = new TestStruct(
            array(
                'property' => 42,
            )
        );

        $this->assertSame(42, $struct->property);
    }

    public function testSetValue()
    {
        $struct = new TestStruct();
        $struct->property = 42;

        $this->assertSame(42, $struct->property);
    }

    public function testUnsetValue()
    {
        $struct = new TestStruct();
        $struct->property = 42;
        unset($struct->property);

        $this->assertFalse(isset($struct->property));
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testGetUnknownValue()
    {
        $struct = new TestStruct();

        $this->assertNull($struct->unknown);
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testConstructorUnknwonValue()
    {
        $struct = new TestStruct(
            array(
                'unknown' => 42,
            )
        );
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testSetUnknownValue()
    {
        $struct = new TestStruct();
        $struct->unknown = 42;
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testUnsetUnknownValue()
    {
        $struct = new TestStruct();
        unset($struct->unknown);
    }
}

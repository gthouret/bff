<?php

namespace BFF\Test;

use BFF\Registry;
use BFF\Test\Registry\TestObj;
use PHPUnit\Framework\TestCase;

class RegistryTest extends TestCase
{
    public function setUp()
    {
        Registry::removeAll();
    }

    public function testSet()
    {
        $obj = new TestObj();
        $obj->setTest(1);

        $result = Registry::set('testobj', $obj);
        $this->assertTrue($result);
    }

    public function testIsSet()
    {
        $this->assertFalse(Registry::isset('testobj'));
        $obj = new TestObj();
        $obj->setTest(1);

        Registry::set('testobj', $obj);

        $this->assertTrue(Registry::isset('testobj'));
    }

    public function testGet()
    {
        $obj = new TestObj();
        $obj->setTest(1);

        Registry::set('testobj', $obj);

        $newobj = Registry::get('testobj');

        $this->assertEquals($obj, $newobj);

        $obj->setTest(123);
        $this->assertEquals(123, $newobj->getTest());
    }

    public function testRemove()
    {
        $obj = new TestObj();
        $obj->setTest(1);

        Registry::set('testobj', $obj);
        Registry::set('testobj2', $obj);

        $this->assertTrue(Registry::isset('testobj'));
        $this->assertTrue(Registry::isset('testobj2'));

        Registry::remove('testobj');

        $this->assertFalse(Registry::isset('testobj'));
        $this->assertTrue(Registry::isset('testobj2'));
    }

    public function testRemoveAll()
    {
        $obj = new TestObj();
        $obj->setTest(1);

        Registry::set('testobj', $obj);
        Registry::set('testobj2', $obj);

        $this->assertTrue(Registry::isset('testobj'));
        $this->assertTrue(Registry::isset('testobj2'));

        Registry::removeAll();

        $this->assertFalse(Registry::isset('testobj'));
        $this->assertFalse(Registry::isset('testobj2'));
    }

    public function testReplace()
    {
        $obj = new TestObj();
        $obj->setTest(1);

        Registry::set('testobj', $obj);
        Registry::set('testobj2', $obj);

        $obj2 = new TestObj();
        $obj2->setTest(2);

        Registry::replace('testobj2', $obj2);

        $objget = Registry::get('testobj');
        $obj2get = Registry::get('testobj2');

        $this->assertEquals(1, $objget->getTest());
        $this->assertEquals(2, $obj2get->getTest());
    }
}
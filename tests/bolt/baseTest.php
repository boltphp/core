<?php

class BoltBaseTest extends Test {

    public function setup() {
        $this->base = new bolt\base();
        $this->mock = realpath(__DIR__."/../mock");
    }

    public function test_getRegexFiles() {
        $this->assertEquals(["{$this->mock}/testClass.php"], $this->base->getRegexFiles($this->mock));
        $this->assertEquals(["{$this->mock}/test.txt"], $this->base->getRegexFiles($this->mock, '^.+\.txt$'));
    }

    public function test_requireFromPath()  {
        $this->base->requireFromPath($this->mock);
        $this->assertTrue(class_exists("TestClass"));
    }

    public function test_getDefinedClasses() {
        $this->assertEquals(get_declared_classes(), $this->base->getDefinedClasses());
    }

    public function test_getReflectionClass() {
        require_once($this->mock."/testClass.php");
        $this->assertEquals(new \ReflectionClass('TestClass'), $this->base->getReflectionClass('TestClass'));
    }

    public function test_normalizeClassName() {
        $this->assertEquals('base\class', $this->base->normalizeClassName('\base\class'));
    }

    public function test_getClassImplements() {
        require_once($this->mock."/testClass.php");
        $this->assertEquals([new \ReflectionClass('TestClass')], $this->base->getClassImplements('TestClassInterface'));
    }

}
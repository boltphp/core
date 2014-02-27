<?php

class helpers_classesTest extends Test {

    public function setUp() {
        $this->c = new \bolt\helpers\classes();
    }

    public function test_getReflectionClass() {
        $ref = $this->c->getReflectionClass('helpers_classesTest_class1');
        $this->assertInstanceOf('ReflectionClass', $ref);
        $c = new helpers_classesTest_class1();
        $ref = $this->c->getReflectionClass($c);
        $this->assertInstanceOf('ReflectionClass', $ref);
    }

    public function test_normalizeClassName() {
        $this->eq('test', $this->c->normalizeClassName('test'));
        $this->eq('test', $this->c->normalizeClassName('\\test'));
        $this->eq('test\\test1\\', $this->c->normalizeClassName('\\test\\test1\\'));
    }

    public function test_getDeclaredClasses() {
        $this->assertTrue(is_array($this->c->getDeclaredClasses()));
    }

    public function test_getClassImplements() {
        $r = $this->c->getClassImplements('helpers_classesTest_inf1');
        $this->eq(1, count($r));
        $this->assertInstanceOf('ReflectionClass', $r[0]);
        $this->eq('helpers_classesTest_class3', $r[0]->name);
    }

    public function test_getSubClassOf() {
        $r = $this->c->getSubClassOf('helpers_classesTest_class1');
        $this->eq(1, count($r));
        $this->assertInstanceOf('ReflectionClass', $r[0]);
        $this->eq('helpers_classesTest_class2', $r[0]->name);
    }

}

class helpers_classesTest_class1 {


}

class helpers_classesTest_class2 extends helpers_classesTest_class1 {

}

interface helpers_classesTest_inf1 {

}

class helpers_classesTest_class3 implements helpers_classesTest_inf1 {

}
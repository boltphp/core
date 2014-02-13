<?php

class modelTest extends Test {

    public function setUp() {
        $this->a = new bolt\application();
        $this->s = new modelTest_Source();
        $this->m = new bolt\models($this->a, [
                'source' => $this->s
            ]);
    }

    public function test_inherits() {
        $i = class_implements($this->m);
        $this->assertTrue(in_array('bolt\plugin\singleton', $i));
        $this->assertTrue(in_array('ArrayAccess', $i));
    }

    public function test_constructWithNoSource() {
        $this->setExpectedException('Exception');
        new bolt\models($this->a);
    }

    public function test_constructorWithBadClass() {
        $this->setExpectedException('Exception');
        $c = new StdClass();
        new bolt\models($this->a, ['source' => $c]);
    }

    public function test_constructorEntityManager() {
        $this->assertTrue($this->s->getEmCalled);
    }

    public function test_getEntityManager(){
        $this->assertInstanceOf('modelTest_EntityManager', $this->m->getEntityManager());
    }

}

class modelTest_Source implements bolt\source\face {

    public $getEmCalled = false;

    public function getModelEntityManager() {
        $this->getEmCalled = true;
        return new modelTest_EntityManager();
    }

}

class modelTest_EntityManager {

}
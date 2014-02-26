<?php

class models_resultsTest extends Test {


    public function setUp() {
        $this->m = new bolt\models($this->getApp(), [
                'source' => new modelTest_Results_Source()
            ]);
        $this->p =  new modelsTest_Results_result($this->m, 'modelsTest_Results_entity');
    }

    public function test_implements() {
        $this->assertInstanceOf('SplDoublyLinkedList', $this->p);
    }

    public function test_constructWithItems() {
        $e = new modelsTest_Results_entity();
        $p = new modelsTest_Results_result($this->m, 'modelsTest_Results_entity', [
                $e
            ]);
        $this->eq(1, $p->count());
    }

    public function test_pushWithNotObject() {
        $this->setExpectedException('Exception');
        $this->p->push('NOT_AN_OBJECT');
    }

    public function test_pushBadObject() {
        $this->setExpectedException('Exception');
        $this->p->push(new StdClass);
    }

    public function test_push() {
        $this->eq(0, $this->p->count());
        $e = new modelsTest_Results_entity();
        $this->eq($this->p, $this->p->push($e));
        $this->eq(1, $this->p->count());
    }

    public function test_unshiftWithNotObject() {
        $this->setExpectedException('Exception');
        $this->p->unshift('NOT_AN_OBJECT');
    }

    public function test_unshift() {
        $this->eq(0, $this->p->count());
        $e = new modelsTest_Results_entity();
        $this->eq($this->p, $this->p->unshift($e));
        $this->eq(1, $this->p->count());
    }

    public function test_first() {
        $this->assertNull($this->p->first());
        $e1 = new modelsTest_Results_entity();
        $e2 = new modelsTest_Results_entity();
        $this->p->push($e1)->push($e2);
        $this->eq($e1, $this->p->first());
    }

    public function test_last() {
        $this->assertNull($this->p->last());
        $e1 = new modelsTest_Results_entity();
        $e2 = new modelsTest_Results_entity();
        $this->p->push($e1)->push($e2);
        $this->eq($e2, $this->p->last());
    }

}

class modelsTest_Results_result extends bolt\models\result {}

class modelsTest_Results_entity extends \bolt\models\entity {

    public $id = false;

    public function __construct() {
        $this->id = uniqid();
    }

}



class modelTest_Results_Source implements bolt\source\face {

    public function getModelEntityManager() {

    }

}
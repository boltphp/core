<?php

class models_collectionTest extends Test {

    public function setUp() {
        $this->m = new bolt\models($this->getApp(), [
                'source' => new modelTest_Collection_Source()
            ]);
        $this->p =  new modelsTest_Collection_collection($this->m, 'modelsTest_Collection_entity');
    }

    public function test_implements() {
        $this->assertInstanceOf('bolt\helpers\collection', $this->p);
    }

    public function test_constructWithItems() {
        $e = new modelsTest_Collection_entity();
        $p = new modelsTest_Collection_collection($this->m, 'modelsTest_Collection_entity', [
                $e
            ]);
        $this->eq(1, $p->count());
    }

    public function test_constructBadClass() {
        $this->setExpectedException('Exception');
        new modelsTest_Collection_collection($this->m, 'NOT_A_CLASS');
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
        $e = new modelsTest_Collection_entity();
        $this->eq($this->p, $this->p->push($e));
        $this->eq(1, $this->p->count());
    }

    public function test_unshiftWithNotObject() {
        $this->setExpectedException('Exception');
        $this->p->unshift('NOT_AN_OBJECT');
    }

    public function test_unshift() {
        $this->eq(0, $this->p->count());
        $e = new modelsTest_Collection_entity();
        $this->eq($this->p, $this->p->unshift($e));
        $this->eq(1, $this->p->count());
    }

    public function test_first() {
        $this->assertNull($this->p->first());
        $e1 = new modelsTest_Collection_entity();
        $e2 = new modelsTest_Collection_entity();
        $this->p->push($e1)->push($e2);
        $this->eq($e1, $this->p->first());
    }

    public function test_last() {
        $this->assertNull($this->p->last());
        $e1 = new modelsTest_Collection_entity();
        $e2 = new modelsTest_Collection_entity();
        $this->p->push($e1)->push($e2);
        $this->eq($e2, $this->p->last());
    }

    public function test_magicGetSetParams() {
        $this->eq(null, $this->p->nope);
        $this->p->nope = 9;
        $this->eq(9, $this->p->nope);
    }

    public function test_asArray() {
        $e1 = new modelsTest_Collection_entity();
        $e2 = new modelsTest_Collection_entity();
        $this->p->push($e1)->push($e2);

        $this->eq([
                ['id' => $e1->id],
                ['id' => $e2->id]
            ],
            $this->p->asArray());
    }

}

class modelsTest_Collection_collection extends bolt\models\collection {}

class modelsTest_Collection_entity extends \bolt\models\entity {

    protected $id = false;

    public function __construct() {
        $this->id = uniqid();
    }

}



class modelTest_Collection_Source implements bolt\source\sourceInterface {

    public function getModelEntityManager() {

    }

}
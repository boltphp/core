<?php

class models_entityTest extends Test {

    public function setUp() {
        $this->m = new bolt\models($this->getApp(),[
                'source' => new modelTest_Entity_Source()
            ]);
        $this->e = new modelsTest_Entity();
    }

    public function test_getsetmanager() {
        $this->assertNull($this->e->getManager());
        $this->eq($this->e, $this->e->setManager($this->m));
        $this->eq($this->m, $this->e->getManager());
    }

    public function test_getsetloaded() {
        $this->assertFalse($this->e->loaded());
        $this->assertFalse($this->e->isLoaded());
        $this->eq($this->e, $this->e->setLoaded(true));
        $this->assertTrue($this->e->loaded());
        $this->assertTrue($this->e->isLoaded());
        $this->eq($this->e, $this->e->setLoaded(false));
        $this->assertFalse($this->e->loaded());
        $this->assertFalse($this->e->isLoaded());
    }

    public function test_setLoadedBad() {
        $this->setExpectedException('Exception');
        $this->e->setLoaded('string');
    }

    public function test_magicGetSetNoOp() {
        $this->assertNull($this->e->test);
        $this->e->test = 99;
        $this->eq(99, $this->e->test);
    }

    public function test_magicGetSetWithOp() {
        $this->eq(-1, $this->e->test1);
        $this->e->test1 = 98;
        $this->eq(99, $this->e->test1);
    }

    public function test_magicGetSetWithOpDash() {
        $this->eq(-1, $this->e->test_name_dash);
        $this->e->test_name_dash = 98;
        $this->eq(99, $this->e->test_name_dash);
    }

}

class modelsTest_Entity extends bolt\models\entity {

    protected $test = null;
    protected $test1 = 0;

    protected $test_name_dash = 0;

    public function getTest1Attr() {
        return $this->test - 1;
    }

    public function setTest1Attr($value) {
        $this->test = $value + 2;
        return $this;
    }

    public function getTestNameDashAttr() {
        return $this->test_name_dash - 1;
    }

    public function setTestNameDashAttr($value) {
        $this->test_name_dash = $value + 2;
    }

}

class modelTest_Entity_Source implements bolt\source\face {


    public function getModelEntityManager() {

    }

}
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

    public function test_getApp() {
        $this->e->setManager($this->m);
        $this->eq($this->getApp(), $this->e->getApp());
    }

    public function test_set() {
        $data = ['test' => 1, 'test1' => 9];
        $this->e->set($data);
        $this->eq(1, $this->e->test);
        $this->eq(10, $this->e->test1);
    }

    public function test_propertyWithAsArray() {
        $o = new modelTest_Entity_hasAsArray();
        $this->e->test = $o;
        $this->eq(false, $o->called);
        $n = $this->e->normalize();
        $this->eq(true, $n['test']['asarray']);
        $this->eq(true, $o->called);
    }

    public function test_beforeNormalize() {
        $e = new modelsTest_EnityGoodNormalize();
        $this->eq(false, $e->before);
        $n = $e->normalize();
        $this->eq(true, $e->before);
        $this->eq(9, $n['testbefore']);
    }

    public function test_afterNormalize() {
        $e = new modelsTest_EnityGoodNormalize();
        $this->eq(false, $e->after);
        $n = $e->normalize();
        $this->eq(true, $e->after);
        $this->eq(9, $n['testafter']);
    }

    public function test_badAfterNormalize() {
        $this->setExpectedException('Exception');
        $e = new modelsTest_EnityBadNormalize();
        $e->normalize();
    }

    public function test_asArray() {
        $this->eq(['test' => null, 'test1' => 0, 'test_name_dash' => 0], $this->e->asArray());
    }

    public function test_jsonSerialize() {
        $this->eq(['test' => null, 'test1' => 0, 'test_name_dash' => 0], $this->e->jsonSerialize());
    }

    public function test_toString() {
        $this->eq(json_encode(['test' => null, 'test1' => 0, 'test_name_dash' => 0]), (string)$this->e);
    }

    public function test_save() {
        $m = new modelTest_Entity_Mananger($this->getApp(), [
                'source' => new modelTest_Entity_Source()
            ]);
        $this->eq(false, $m->saved);
        $this->e->setManager($m);
        $this->eq($this->e, $this->e->save());
        $this->eq(true, $m->saved);
    }

    public function test_delete() {
        $m = new modelTest_Entity_Mananger($this->getApp(), [
                'source' => new modelTest_Entity_Source()
            ]);
        $this->eq(false, $m->deleted);
        $this->e->setManager($m);
        $this->eq($this->e, $this->e->delete());
        $this->eq(true, $m->deleted);
    }

    public function test_isset() {
        $this->eq(false, isset($this->e->idonotexist));
        $this->eq(true, isset($this->e->test));
    }

}

class modelsTest_Entity extends bolt\models\entity {

    protected $test = null;
    protected $test1 = 0;

    protected $test_name_dash = 0;

    public $after = false;
    public $before = false;

    public function getTest1Attr() {
        return $this->test1 - 1;
    }

    public function setTest1Attr($value) {
        $this->test1 = $value + 2;
        return $this;
    }

    public function getTestNameDashAttr() {
        return $this->test_name_dash - 1;
    }

    public function setTestNameDashAttr($value) {
        $this->test_name_dash = $value + 2;
    }


}

class modelsTest_EnityGoodNormalize extends modelsTest_Entity {

    public function afterNormalize(array $array) {
        $this->after = true;
        $array['testafter'] = 9;
        return $array;
    }

    public function beforeNormalize() {
        $this->before = true;
        return ['testbefore' => 9];
    }
}

class modelsTest_EnityBadNormalize extends modelsTest_Entity {
    public function afterNormalize(array $array) {
        return false;
    }
}

class modelTest_Entity_Source implements bolt\source\face {


    public function getModelEntityManager() {

    }

}

class modelTest_Entity_Mananger extends \bolt\models {

    public $saved = false;
    public $deleted = false;

    public function save($entity) {
        $this->saved = true;
    }

    public function delete($entity) {
        $this->deleted = true;
    }

}

class modelTest_Entity_hasAsArray {

    public $called = false;

    public function asArray() {
        $this->called = true;
        return ['asarray' => true];
    }

}
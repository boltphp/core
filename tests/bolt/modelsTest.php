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

    public function test_getApp() {
        $this->assertInstanceOf('\bolt\application', $this->m->getApp());
        $this->eq($this->a, $this->m->getApp());
    }

    public function test_getCollectionGood() {
        $this->assertInstanceOf('bolt\models\collection', $this->m->getCollection('modelTest_entityNoAlias'));
    }

    public function test_generateEntity(){
        $this->assertInstanceOf('modelTest_entityNoAlias', $this->m->generateEntity('modelTest_entityNoAlias', []));
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

    public function test_constructorWithAlias() {
        $this->assertTrue(array_key_exists('test', $this->m->getAliases()));
        $this->assertFalse(array_key_exists('test1', $this->m->getAliases()));
    }

    public function test_getEntityManager(){
        $this->assertInstanceOf('modelTest_EntityManager', $this->m->getEntityManager());
    }

    public function test_getByAlias() {
        $this->assertInstanceOf('bolt\models\proxy', $this->m->get('test'));
    }

    public function test_getByClass() {
        $this->assertInstanceOf('bolt\models\proxy', $this->m->get('modelTest_entityNoAlias'));
    }

    public function test_getNoClass() {
        $this->setExpectedException('Exception');
        $this->m->get('NOT_A_CLASS');
    }

    public function test_registerAlias() {
        $this->assertFalse(array_key_exists('test1a', $this->m->getAliases()));
        $this->eq($this->m, $this->m->alias('test1a', 'modelTest_entityNoAlias'));
        $this->assertTrue(array_key_exists('test1a', $this->m->getAliases()));
    }

    public function test_registerAliasBadClass() {
        $this->setExpectedException('Exception');
        $this->m->alias('test1', 'NOT_A_CLASS');
    }

    public function test_getRepoForEntity() {
        $m = new modelTest_testModel($this->a, [
                'source' => $this->s
            ]);
        $this->assertInstanceOf('modelTest_Repo', $m->test_getRepository('modelTest_Repo'));
    }

    public function test_getRepoForEntityAlias() {
        $m = new modelTest_testModel($this->a, [
                'source' => $this->s
            ]);
        $this->assertInstanceOf('modelTest_Repo', $m->test_getRepository('test'));
    }

    public function test_getRepoFroEntityBad() {
        $this->setExpectedException('Exception');
        $m = new modelTest_testModel($this->a, [
                'source' => $this->s
            ]);
        $m->test_getRepository('NOT_A_CLASS');
    }

    public function test_offsetSet() {
        $this->assertFalse(array_key_exists('test1', $this->m->getAliases()));
        $this->m['test1'] = 'modelTest_entityNoAlias';
        $this->assertTrue(array_key_exists('test1', $this->m->getAliases()));
    }

    public function test_offsetExists(){
        $this->assertFalse(isset($this->m['test111']));
        $this->assertTrue(isset($this->m['test']));
    }

    public function test_offsetUnset() {
        $this->assertTrue(array_key_exists('test', $this->m->getAliases()));
        unset($this->m['test']);
        $this->assertFalse(array_key_exists('test', $this->m->getAliases()));
    }

    public function test_offsetGetFound() {
        $this->assertInstanceOf('bolt\models\proxy', $this->m['test']);
    }

    public function test_offsetGetBad() {
        $this->setExpectedException('Exception');
        $this->m['test11'];
    }


    public function test_findGood() {
        $e = $this->m->find('modelTest_entityNoAlias', new modelTest_entityNoAlias());
        $this->assertInstanceOf('modelTest_entityNoAlias', $e);
        $this->assertTrue($e->loaded());
        $this->eq($this->m, $e->getManager());
    }

    public function test_findBad() {
        $e = $this->m->find('modelTest_entityNoAlias', false);
        $this->assertInstanceOf('modelTest_entityNoAlias', $e);
        $this->assertFalse($e->loaded());
        $this->eq($this->m, $e->getManager());
    }

    public function test_findAll() {
        $this->assertInstanceOf('bolt\models\collection', $this->m->findAll('modelTest_entityAlias'));
    }

    public function test_findBy() {
        $this->assertInstanceOf('bolt\models\collection', $this->m->findBy('modelTest_entityAlias', []));
    }

    public function test_findOneBy() {
        $e = $this->m->findOneBy('modelTest_entityNoAlias', [new modelTest_entityNoAlias()]);
        $this->assertInstanceOf('modelTest_entityNoAlias', $e);
        $this->assertTrue($e->loaded());
        $this->eq($this->m, $e->getManager());
    }

    public function test_findOneByBad() {
        $e = $this->m->findOneBy('modelTest_entityNoAlias', [false]);
        $this->assertInstanceOf('modelTest_entityNoAlias', $e);
        $this->assertFalse($e->loaded());
        $this->eq($this->m, $e->getManager());
    }

    public function test_generateNoClass() {
        $this->setExpectedException('Exception');
        bolt\models::generate("NO_CLASS", []);
    }

    public function test_generate() {
        $ref = bolt\models::generate('modelTest_entityNoAlias', ['test' => 9], $this->m);
        $this->assertInstanceOf('modelTest_entityNoAlias', $ref);
        $this->eq(9, $ref->test);
        $this->eq($this->m, $ref->getManager());
    }

}

class modelTest_Source implements bolt\source\sourceInterface {

    public $getEmCalled = false;

    public function getModelEntityManager() {
        $this->getEmCalled = true;
        return new modelTest_EntityManager();
    }

}

class modelTest_testModel extends bolt\models {

    public function test_getRepository() {
        return call_user_func_array([$this, 'getRepoForEntity'], func_get_args());
    }

}

class modelTest_EntityManager {

    public function getRepository() {
        return new modelTest_Repo();
    }


}

class modelTest_Repo {

    public function find($what) {
        return $what;
    }

    public function findAll() {

    }

    public function findBy() {

    }

    public function findOneBy($what) {
        return $what[0];
    }

    public function getClassMetadata() {
        return new modelTest_RepoClassMetadata();
    }

}

class modelTest_RepoClassMetadata {
    public function getFieldNames() {
        return ['test'];
    }
    public function getFieldMapping() {

    }
}

class modelTest_entityNoAlias extends \bolt\models\entity {

    protected $test = false;

}

class modelTest_entityAlias extends \bolt\models\entity {

    const ALIAS = "test";

}
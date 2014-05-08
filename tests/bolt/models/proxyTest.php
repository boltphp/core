<?php

class models_proxyTest extends Test {


    public function setUp() {
        $this->m = new bolt\models($this->getApp(), [
                'source' => new modelTest_Proxy_Source()
            ]);
        $this->p = new bolt\models\proxy($this->m, 'modelTest_Proxy_Entity');
    }

    public function test_call() {
        $p = new bolt\models\proxy($this->m, 'modelTest_Proxy_Entity');
        $this->assertInstanceOf('modelTest_Proxy_EntityManager', $this->p->getEntityManager());
    }

    public function test_callBad() {
        $this->assertNull($this->p->NOT_A_FUNC());
    }

    public function test_getClassName() {
        $this->eq('modelTest_Proxy_Entity', $this->p->getClassName());
    }

}


class modelTest_Proxy_Source implements bolt\source\sourceInterface {

    public function getModelEntityManager() {
        return new modelTest_Proxy_EntityManager();
    }
}

class modelTest_Proxy_EntityManager {

    public function getRepository($class) {
        $r = new modelTest_Proxy_Repo();
        $r->class = $class;
        return $r;
    }

}
class modelTest_Proxy_Entity extends \bolt\models\entity {


}
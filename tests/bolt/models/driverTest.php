<?php

use \Doctrine\ORM\Mapping\ClassMetadata;

class models_driverTest extends Test {

    public function setUp() {
        $m = new bolt\models($this->getApp(), [
                'source' => new modelTest_Driver_Source()
            ]);
        $this->d = new bolt\models\driver($m);
    }

    public function test_implements() {
        $this->assertTrue(in_array('Doctrine\Common\Persistence\Mapping\Driver\MappingDriver', class_implements($this->d)));
    }

    public function test_loadMetadataBadClass() {
        $mc = new ClassMetadata('modelTest_Driver_Entity');
        $this->assertNull($this->d->loadMetadataForClass('NOT_A_CLASS', $mc));
    }

    public function test_loadMetaData() {
        $mc = new ClassMetadata('modelTest_Driver_Entity');
        $this->d->loadMetadataForClass('modelTest_Driver_Entity', $mc);
        $this->assertInstanceOf('Doctrine\ORM\Mapping\ClassMetadata', $mc);
        $this->eq(['id'], $mc->getFieldNames());
    }

    public function test_getAllClassNames(){
        $c = [
            'modelsTest_Collection_entity',
            'modelTest_Driver_Entity',
            'modelsTest_Entity',
            'modelTest_Proxy_Entity',
            'modelTest_entityNoAlias',
            'modelTest_entityAlias'
        ];

        $this->eq($c, $this->d->getAllClassNames());
    }

    public function test_isTransient() {
        $this->assertTrue($this->d->isTransient('bolt\models\entity'));
        $this->assertNull($this->d->isTransient('NOT_A_CLASS'));
    }

}


class modelTest_Driver_Source implements bolt\source\face {

    public $getEmCalled = false;

    public function getModelEntityManager() {
        $this->getEmCalled = true;
        return new modelTest_Driver_EntityManager();
    }

}

class modelTest_Driver_EntityManager {

    public function getRepository() {

    }

    public function getEntities() {
        return 9;
    }

}

class modelTest_Driver_Entity extends \bolt\models\entity {

    public static function struct($md) {
        $md->mapField([
                'id' => true,
                'type' => 'integer',
                'fieldName' => 'id'
            ]);
    }

}

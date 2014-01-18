<?php

namespace bolt\model;
use \b;

use \Doctrine\Common\Persistence\Mapping\ClassMetadata;

class driver implements \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver {

    private $_manager;

    public function __construct(manager $man) {
        $this->_man = $man;
    }

    public function loadMetadataForClass($className, ClassMetadata $metadata) {
        if (class_exists($className, true)) {
            $className::loadMetadata($metadata);
        }
        return false;
    }

    public function getAllClassNames() {
        return $this->_man->getEntities();
    }

    public function isTransient($className) {
        if ($className == 'bolt\model\entity') {return true;}
    }

}
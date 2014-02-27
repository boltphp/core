<?php

namespace bolt\models;
use \b;

use \Doctrine\Common\Persistence\Mapping\ClassMetadata,
    \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;


/**
 * mdoels driver
 */
class driver implements MappingDriver {

    /**
     * refrance to models manager
     *
     * @var bolt\models
     */
    private $_manager;

    /**
     * Constructor
     *
     * @param bolt\models $man
     */
    public function __construct(\bolt\models $man) {
        $this->_man = $man;
    }


    /**
     * load metdata for a model
     *
     * @param string $className
     * @param Doctrine\Common\Persistence\Mapping\ClassMetadata $metadata
     *
     * @return null
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata) {
        if (class_exists($className, true)) {
            $className::struct($metadata);
        }
        return null;
    }


    /**
     * get all classnames for entities
     *
     * @return array
     */
    public function getAllClassNames() {
        return [];
    }


    /**
     * is transient
     *
     * @param string $className
     *
     * @return bool
     */
    public function isTransient($className) {
        if ($className == 'bolt\models\entity') {return true;}
    }

}
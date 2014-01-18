<?php

namespace bolt\model;
use \b;

use \Doctrine\ORM\EntityManager,
    \Doctrine\ORM\Configuration,
    \Doctrine\ORM\Mapping\Driver\StaticPHPDriver;

class manager implements \bolt\plugin\singleton, \ArrayAccess {

    // em
    private $_em = false;

    private $_entities = [];

    public function __construct($config) {

        // set our source
        $this->_source = $config['source'];

        // handle
        $handle = $this->_source->getHandle();

        // configure
        $cfg = new Configuration();

        // set
        $cfg->setMetadataDriverImpl(new driver($this));

        $cfg->setProxyDir($config['tmp']);
        $cfg->setProxyNamespace('bolt\model\proxy');

        // create our entity manager
        $this->_em = EntityManager::create($handle, $cfg, $handle->getEventManager());

        // find all of our defined entities
        foreach (b::getClassExtends('\bolt\model\entity') as $class) {
            $this->add($class->getConstant('NAME'), $class->name);
        }

    }

    public function getEntityManager() {
        return $this->_em;
    }

    public function getEntities() {
        return array_values($this->_entities);
    }

    public function add($name, $class) {
        $this->_entities[$name] = $class;
        return $this;
    }

    public function find($entity, $id) {
        $o = $this->_em->getRepository($entity)->find($id);

        if ($o AND is_object($o)) {
            $o->setManager($this);
            $o->setLoaded(true);
            return $o;
        }

        // return blank entity
        return new $entity();

    }

    public function findBy($entity, $query) {
        $items = $this->_em->getRepository($entity)->findBy($query);

        return new result($items, $entity, $this);

    }

    public function findOneBy($entity, $query) {
        $o = $this->_em->getRepository($entity)->findOneBy($query);
        if ($o AND is_object($o)) {
            $o->setManager($this);
            $o->setLoaded(true);
            return $o;
        }

        // return blank entity
        return new $entity();
    }

    public function where($entity) {
        // $q = $this->_em->createQueryBuilder()

    }


    public function offsetSet($name, $class) {
        $this->add($name, $class);
    }

    public function offsetExists($name) {
        return array_key_exists($name, $this->_entities);
    }

    public function offsetUnset($name) {
        unset($this->_entities[$name]);
    }

    public function offsetGet($name) {
        if (array_key_exists($name, $this->_entities)) {
            return new entity\proxy($this, $this->_entities[$name]);
        }
        return false;
    }

}
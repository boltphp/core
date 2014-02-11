<?php

namespace bolt;
use \b;

// doctrine stuff
use \Doctrine\ORM\EntityManager,
    \Doctrine\ORM\Configuration,
    \Doctrine\ORM\Mapping\Driver\StaticPHPDriver,
    \Doctrine\DBAL\Types\Type;


/**
 * model manager
 */
class models implements plugin\singleton, \ArrayAccess {

    private $_app;

    private $_config = [];

    private $_alias = [];

    private $_em;

    public static $types = [
        'timestamp' => 'bolt\models\types\timestamp'
    ];

    public function __construct(application $app, $config = []) {
        $this->_app = $app;

        if (isset($config['load'])) {
            $this->load($config['load']);
        }

        $handle = $config['source']->getHandle();

        // configure
        $cfg = new Configuration();

        // set
        $cfg->setMetadataDriverImpl(new models\driver($this));

        $cfg->setProxyDir("/tmp");
        $cfg->setProxyNamespace('bolt\models\proxy');

        // create our entity manager
        $this->_em = EntityManager::create($handle, $cfg, $handle->getEventManager());

        foreach (self::$types as $name => $class) {
            Type::addType($name, $class);
        }

    }


    /**
     * find an entity by primary key
     *
     * @param string $entity entity class name or alias
     * @param mixed $id primary key value
     *
     * @return bolt\models\entity
     */
    public function find($entity, $id) {
        $o = $this->_getRepoForEntity($entity)->find($id);

       if ($o AND is_object($o)) {
           $o->setManager($this);
           $o->setLoaded(true);
           return $o;
       }

       // return blank entity
       return new $entity();
    }

    /**
     * find all entities
     *
     * @param string $entity entity class name or alias
     *
     * @return bolt\models\result
     */
    public function findAll($entity) {
        return new models\result($this, $entity, $this->_getRepoForEntity($entity)->findAll());
    }

    /**
     * find entities by search $criteria
     *
     * @param string $entity entity class name or alias
     * @param array $criteria query
     * @param array $order order by
     * @param int $limit
     * @param init $offset
     *
     * @return bolt\models\result
     */
    public function findBy($entity, array $criteria, array $order = null, $limit = null, $offset = null) {
        return new models\result($this, $entity, $this->_getRepoForEntity($entity)->findBy($criteria, $order, $limit, $offset));
    }

    /**
     * find one enitity by query $criteria
     *
     * @param string $entity entity class name or alias
     * @param array $criteria query
     * @param array $order order
     *
     * @return bolt\models\entity
     */
    public function findOneBy($entity, array $criteria, array $order) {
        $o = $this->_getRepoForEntity($entity)->findBy($criteria, $order);

        if ($o AND is_object($o)) {
            $o->setManager($this);
            $o->setLoaded(true);
            return $o;
        }

        // return blank entity
        return new $entity();

    }



    private function _getRepoForEntity($entity) {
        if (array_key_exists($entity, $this->_alias)) {
            $entity = $this->_alias[$entity];
        }
        return $this->_em->getRepository($entity);
    }


    public function alias($name, $entity) {
        $this->_alias[$name] = $entity;
    }

    public function offsetSet($name, $class) {
        $this->add($name, $class);
    }

    public function offsetExists($name) {
        return array_key_exists($name, $this->_alias);
    }

    public function offsetUnset($name) {
        unset($this->_alias[$name]);
    }

    public function offsetGet($name) {
        if (array_key_exists($name, $this->_alias)) {
            return new models\proxy($this, $this->_alias[$name]);
        }
        else if (class_exists($name, true)) {
            return new models\proxy($this, $name);
        }

        return false;
    }


}
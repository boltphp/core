<?php

namespace bolt;
use \b;

// doctrine stuff
use \Doctrine\DBAL\Types\Type;


/**
 * model manager
 */
class models implements plugin\singleton, \ArrayAccess {

    /**
     * application
     *
     * @var bolt\application
     */
    private $_app;

    /**
     * configuration
     *
     * @var array
     */
    private $_config = [];

    /**
     * list of entity aliases
     *
     * @var array
     */
    private $_alias = [];

    /**
     * entity manager
     *
     * @var object
     */
    private $_em;

    /**
     * custom type map
     *
     * @var array
     */
    public static $types = [
        'timestamp' => 'bolt\models\types\timestamp',
        'string_array' => 'bolt\models\types\stringArray'
    ];


    /**
     * Construct
     *
     * @param bolt\application $app application
     * @param array $config
     *
     */
    public function __construct(application $app, $config = []) {
        $this->_app = $app;

        if (!isset($config['source'])) {
            throw new \Exception("Source instance must be provided in config");
        }

        // make sure it can implement a model handler
        if (!method_exists($config['source'], 'getModelEntityManager')) {
            throw new \Exception('source does not implement repositoy');
        }

        // get the entity manager
        $this->_em = $config['source']->getModelEntityManager($this, new models\driver($this));

        // add our custom types
        foreach (self::$types as $name => $class) {
            if (!Type::hasType($name)) {
                Type::addType($name, $class);
                unset(self::$types[$name]);
            }
        }

        // preload modules
        if (isset($config['preload'])) {

            // load each into context
            foreach ($config['preload'] as $file) {
                if (is_a($file, 'bolt\helpers\fs\glob')) {
                    foreach ($file as $_) {
                        require($_);
                    }
                }
            }

        }

        // find all aliases
        foreach (b::getSubClassOf('bolt\models\entity') as $entity) {
            if ($entity->hasConstant('ALIAS')) {
                $this->alias($entity->getConstant('ALIAS'), $entity->name);
            }
        }

    }


    /**
     * return the base app
     *
     * @return \bolt\application
     */
    public function getApp() {
        return $this->_app;
    }


    /**
     * get a model collection object
     *
     * @param string $entity
     *
     * @return bolt\models\collection
     */
    public function getCollection($entity) {
        return new \bolt\models\collection($this, $entity);
    }


    /**
     * generate an entity from
     *
     * @param string $entity
     * @param array $data
     *
     * @return object
     */
    public function generateEntity($entity, array $data) {
        return self::generate($entity, $data, $this);
    }

    /**
     * return the entity manager refrance
     *
     * @return object
     */
    public function getEntityManager() {
        return $this->_em;
    }


    /**
     * get a model
     *
     * @param string $name entity class name or alias
     *
     * @return bolt\model\proxy
     */
    public function get($name){
        if (array_key_exists($name, $this->_alias)) {
            $name = $this->_alias[$name];
        }
        if (class_exists($name, true)) {
            return new models\proxy($this, $name);
        }
        throw new \Exception("No entity class '$name' found");
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

       if ($o && is_object($o)) {
           $o->setManager($this);
           $o->setLoaded(true);
           return $o;
       }

       $empty = new $entity();
       $empty->setManager($this);

       // return blank entity
       return $empty;
    }


    /**
     * find all entities
     *
     * @param string $entity entity class name or alias
     *
     * @return bolt\models\collection
     */
    public function findAll($entity) {
        return new models\collection($this, $entity, $this->_getRepoForEntity($entity)->findAll());
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
     * @return bolt\models\collection
     */
    public function findBy($entity, array $criteria, array $order = null, $limit = null, $offset = null) {
        return new models\collection($this, $entity, $this->_getRepoForEntity($entity)->findBy($criteria, $order, $limit, $offset));
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
    public function findOneBy($entity, array $criteria, array $order = []) {
        $o = $this->_getRepoForEntity($entity)->findOneBy($criteria, $order);

        if ($o && is_object($o)) {
            $o->setManager($this);
            $o->setLoaded(true);
            return $o;
        }

        $empty = new $entity();
        $empty->setManager($this);

        // return blank entity
        return $empty;

    }


    /**
     * return a repository for a give entity or alias
     *
     * @param  string $entity entity class name or alias name
     *
     * @return object
     */
    protected function _getRepoForEntity($entity) {
        if (array_key_exists($entity, $this->_alias)) {
            $entity = $this->_alias[$entity];
        }
        if (!class_exists($entity, true)) {
            throw new \Exception("No entity class for '$entity' exists");
        }

        return $this->_em->getRepository($entity);
    }


    /**
     * register an entity alias
     *
     * @param  string $name alias name
     * @param  string $entity entity class name
     *
     * @return self
     */
    public function alias($name, $entity) {
        if (!class_exists($entity, true)) {
            throw new \Exception("Class $entity does not exist");
        }
        $this->_alias[$name] = $entity;
        return $this;
    }


    /**
     * get all registered aliases
     *
     * @return array
     */
    public function getAliases() {
        return $this->_alias;
    }


    /**
     * register an entity alias
     *
     * @param  string $name
     * @param  string $class
     * @see alias
     *
     * @return void
     */
    public function offsetSet($name, $class) {
        $this->alias($name, $class);
    }

    /**
     * check to see if a registered alias exists
     *
     * @param  string $name name of alias
     *
     * @return bool
     */
    public function offsetExists($name) {
        return array_key_exists($name, $this->_alias);
    }

    /**
     * remove an alias
     *
     * @param  string $name
     *
     * @return void
     */
    public function offsetUnset($name) {
        unset($this->_alias[$name]);
    }

    /**
     * create a new model from given alias or entity class
     *
     * @param  string $name
     * @see  get
     *
     * @return bolt\models\proxy
     */
    public function offsetGet($name) {
        return $this->get($name);
    }

    /**
     * generate an model
     *
     * @param string $class
     * @param array $data
     *
     * @return object
     */
    public static function generate($class, array $data, \bolt\models $manager = null) {
        if (!class_exists($class, true)) {
            throw new \Exception("Unable to load class '$class'");
        }

        // generate the blank entity
        $entity = new $class();

        // get a relection of this method
        $ref = b::getReflectionClass($class);

        foreach ($data as $name => $value) {
            if (!$ref->hasProperty($name)) {continue;}
            $prop = $ref->getProperty($name);
            $prop->setAccessible(true);
            $prop->setValue($entity, $value);
        }

        if ($manager) {
            $entity->setManager($manager);
        }

        $entity->setLoaded(true);

        return $entity;

    }


}
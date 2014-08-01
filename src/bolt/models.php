<?php

namespace bolt;
use \b;

// doctrine stuff
use \Doctrine\DBAL\Types\Type,
    \Doctrine\ORM\Mapping\ClassMetadata
;


/**
 * model manager
 */
class models implements plugin\singleton, \ArrayAccess {
    use helpers\loggable;

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
     * model entity diretories
     *
     * @var array
     */
    private $_dirs = [];

    /**
     * entity manager
     *
     * @var object
     */
    private $_em;


    /**
     * source holder
     *
     * @var \bolt\source\face
     */
    private $_source;


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

        if (!is_a($config['source'], '\bolt\source\sourceInterface')) {
            throw new \Exception("Source must be an interface of bolt\source\sourceInterface");
        }

        // make sure it can implement a model handler
        if (!method_exists($config['source'], 'getModelEntityManager')) {
            throw new \Exception('source does not implement repositoy');
        }

        // get the entity manager
        $this->_source = $config['source'];
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

        if (isset($config['dirs'])) {
            $this->_dirs = (array)$config['dirs'];
        }

        // find all aliases
        foreach (b::getSubClassOf('bolt\models\entity') as $entity) {
            if ($entity->hasConstant('ALIAS')) {
                $this->alias($entity->getConstant('ALIAS'), $entity->name);
            }
        }


    }


    /**
     * return the source driver
     *
     * @return bolt\source\face
     */
    public function getSource() {
        return $this->_source;
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
     * load all models in the config directories
     *
     * @return self
     */
    public function loadFromDirectories() {
        foreach ($this->_dirs as $dir) {
            $path = $this->_app->path($dir);
            b::requireFromPath($path);
        }
        return $this;
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
        try {
            $args = func_get_args(); array_shift($args);
            $o = call_user_func_array([$this->getRepoForEntity($entity), 'find'], $args);
        }
        catch(\Exception $e) { $o = null; }


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
        return new models\collection($this, $entity, $this->getRepoForEntity($entity)->findAll());
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
        $items = []; $total = 0;

        try {
            $items = $this->getRepoForEntity($entity)->findBy($criteria, $order, $limit, $offset, $total);
        }
        catch (\Exception $e) {};

        return new models\collection($this, $entity, $items, [
                'total' => $total,
                'offset' => $offset,
                'limit' => $limit,
                'criteria' => $criteria,
                'order' => $order
            ]);
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

        try {
            $o = $this->getRepoForEntity($entity)->findOneBy($criteria, $order);
        }
        catch (\Exception $e) {
            $o = null;
        }

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
     * save an entity using the entity manager
     *
     * @param  bolt\models\entity $entity
     *
     * @return self
     */
    public function save(models\entity $entity) {
        $before = clone $entity;

        try {
            $this->getEntityManager()->persist($entity);
            $this->getEntityManager()->flush();
        }
        catch (\Exception $e) {
            echo($e->getMessage()); die;
        }

        // fire off a save
        $this->_app->fire("models:save", [
                'before' => $before,
                'entity' => $entity
            ]);

        return $this;
    }


    /**
     * delete an entity using the entity manager
     *
     * @param  \bolt\models\entity $entity
     *
     * @return self
     */
    public function delete(models\entity $entity) {

        try {
            $this->getEntityManager()->remove($entity);
            $this->getEntityManager()->flush();
        }
        catch (\Exception $e) {
            echo($e->getMessage()); die;
        }

        return $this;
    }


    /**
     * return a repository for a give entity or alias
     *
     * @param  string $entity entity class name or alias name
     *
     * @return object
     */
    public function getRepoForEntity($entity) {
        if (array_key_exists($entity, $this->_alias)) {
            $entity = $this->_alias[$entity];
        }
        if (!class_exists($entity, true)) {
            throw new \Exception("No entity class for '$entity' exists");
        }

        return $this->_em->getRepository($entity);
    }


    /**
     * created an entity object
     *
     * @param  string  $entity
     * @param  array  $data
     * @param  boolean $partial
     *
     * @return \bolt\models\entity
     */
    public function create($entity, $data = [], $partial = false) {
        if ($partial) {
            return $this->_em->getPartialReference($entity, $data);
        }
        if (array_key_exists($entity, $this->_alias)) {
            $entity = $this->_alias[$entity];
        }
        return self::generateEntity($entity, $data, $this);
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
    public static function generate($class, array $data, \bolt\models $manager) {
        if (!class_exists($class, true)) {
            throw new \Exception("Unable to load class '$class'");
        }

        // generate the blank entity
        $entity = new $class();

        // get a relection of this method
        $ref = b::getReflectionClass($class);

        // TODO: make this better
        $p = new \Doctrine\DBAL\Platforms\MySqlPlatform();

        $map = $manager->getEntityManager()->getClassMetadata($class);

        // loop through each field name
        foreach ($map->getFieldNames() as $name) {
            $_ = $map->getFieldMapping($name);
            $value = b::param($name, null, $data);

            // target entity
            if (isset($_['targetEntity']) && isset($data[$name])) {
                // reach back to curl to get a repo for this entity
                $value = self::generate($_['targetEntity'], $data[$name], $manager);
            }

            // is a type value
            else if (Type::hasType($_['type'])) {
                $value = Type::getType($_['type'])->convertToPHPValue(b::param($name, null, $data), $p);
            }

            // nothing
            if (!$ref->hasProperty($name)) {
                continue;
            }


            $prop = $ref->getProperty($name);

            $prop->setAccessible(true);
            $prop->setValue($entity, $value);

        }

        $entity->setManager($manager);
        $entity->setLoaded(true);

        return $entity;

    }


}
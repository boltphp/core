<?php

namespace bolt\models;
use \b;

use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 *
 * @todo implement interface to make sure
 *          users are implementing the struct
 *          method
 */
abstract class entity {
    use \bolt\helpers\events;

    /**
     * models manager
     *
     * @var bolt\models
     */
    private $_manager;

    /**
     * is this model loaded
     *
     * @var bool
     */
    private $_loaded = false;

    private $_access;


    /**
     * get the base app
     *
     * @return bolt\application
     */
    final public function getApp() {
        return $this->_manager ? $this->_manager->getApp() : false;
    }

    /**
     * set the models manager
     *
     * @param bolt\models $manager
     *
     * @return self
     */
    final public function setManager(\bolt\models $manager) {
        $this->_manager = $manager;
        return $this;
    }


    /**
     * get the models manager
     *
     * @return bolt\models
     */
    final public function getManager() {
        return $this->_manager;
    }


    /**
     * set if the model is loaded
     *
     * @param bool $loaded
     *
     * @return self
     */
    final public function setLoaded($loaded) {
        if (!is_bool($loaded)) {
            throw new \Exception("Set loaded value must be a bool. $loaded provided");
        }
        $this->_loaded = $loaded;
        return $this;
    }


    /**
     * check if the model is loaded
     *
     * @return bool
     */
    final public function isLoaded() {
        return $this->_loaded;
    }


    /**
     * check if the model is loaded
     *
     * @return bool
     */
    final public function loaded() {
        return $this->_loaded;
    }


    /**
     * does this entity have an attribute oporator
     *
     * @param string $op name of oporator (get|set)
     * @param string $name name of attribute
     *
     * @return mixed
     */
    private function _hasOp($op, $name) {
        if (stripos($name, '_') !== false) {
            $name = implode("", array_map(function($part){
                return ucfirst($part);
            }, explode("_", $name)));
        }
        $func = $op.strtoupper($name)."Attr";
        return method_exists($this, $func) ? $func : false;
    }

    private function _getAccessor() {
        if (!$this->_access) {
            $this->_access = PropertyAccess::createPropertyAccessorBuilder()
                    ->disableExceptionOnInvalidIndex()
                    ->getPropertyAccessor();
        }
        return $this->_access;
    }

    public function getValue($var, $default = null) {
        try {
            return $this->_getAccessor()->getValue($this, $var, $default);
        }
        catch (\Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException $e) {
            return $default;
        }
        catch (\Symfony\Component\PropertyAccess\PropertyAccessor\NoSuchPropertyException $e) {
            return $default;
        }
    }

    /**
     * get an attribute value
     *
     * @return mixed
     */
    public function __get($name){
        $resp = null;


        if (($op = $this->_hasOp('get', $name)) !== false) {
            $resp = call_user_func([$this, $op]);
        }
        else {
            $resp = property_exists($this, $name) ? $this->{$name} : null;
        }

        if (is_object($resp) && stripos(get_class($resp), 'bolt\models\proxy') !== false) {
            $resp->setManager($this->_manager);
            $resp->setLoaded(true);
        }

        return $resp;
    }


    /**
     * set an attribute value
     *
     * @param string $name
     * @param mixed $value
     *
     * @return mixed
     */
    public function __set($name, $value) {
        $cVal = $this->{$name}; $resp = $this;

        if (($op = $this->_hasOp('set', $name)) !== false) {
            $resp = call_user_func([$this, $op], $value);
        }
        else {
            $this->{$name} = $value;
        }

        // fire any attached events
        $this->fire("change,{$name}Change", [
            'attr' => $name,
            'prev' => $cVal,
            'new' => $value
        ]);

        return $resp;
    }

    public function set(array $data) {
        foreach ($data as $key => $value) {
            if (property_exists($key, $value)) {
                $this->{$key} = $value;
            }
        }
        return $this;
    }


    public function afterNormalize($array) {
        return $array;
    }

    public function __isset($name) {
        return property_exists($this, $name);
    }

    public function asArray() {
        $ref = b::getReflectionClass(get_class($this));
        $array = [];

        foreach ($ref->getProperties() as $prop) {
            if ($prop->isProtected()) {
                $val = $this->{$prop->name};

                if (is_object($val) && method_exists($val, 'asArray')) {
                    $val = $val->asArray();
                }

                $array[$prop->name] = $val;
            }
        }

        $array = $this->afterNormalize($array);

        return $array;
    }

    public function save() {
        return $this->_manager->save($this);
    }

}
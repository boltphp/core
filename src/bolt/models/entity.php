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
abstract class entity implements \JsonSerializable {
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

    /**
     * property accessor instance
     *
     * @var Symfony\Component\PropertyAccess\PropertyAccess
     */
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
        $func = $op.ucfirst($name)."Attr";
        return method_exists($this, $func) ? $func : false;
    }


    /**
     * get or create the property accesstor instance
     *
     * @return Symfony\Component\PropertyAccess\PropertyAccess
     */
    private function _getAccessor() {
        if (!$this->_access) {
            $this->_access = PropertyAccess::createPropertyAccessorBuilder()
                    ->disableExceptionOnInvalidIndex()
                    ->getPropertyAccessor();
        }
        return $this->_access;
    }


    /**
     * get a value using the property accessor
     * or return the default val
     *
     * @param  string $var     attribute name
     * @param  mixed $default  default value
     *
     * @return mixed
     */
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


    /**
     * check if a value isset for the property
     * since our object properties are protected
     * by default, isset($obj->var) will always be false otherwise
     *
     * @param  string  $name name of property
     * @return boolean
     */
    public function __isset($name) {
        return property_exists($this, $name);
    }


    /**
     * set object values from given array
     *
     * @param array $data
     *
     * @return self
     */
    public function set(array $data) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->__set($key, $value);
            }
        }
        return $this;
    }


    /**
     * run after the object has been normalized
     *
     * @param  array $array object normalized as an array
     *
     * @return array
     */
    public function afterNormalize(array $array) {
        return $array;
    }


    /**
     * before normalize
     *
     * @return array
     */
    public function beforeNormalize() {
        return [];
    }

    /**
     * return a normalized array of this object
     *
     * @return array
     */
    public function normalize() {
        $ref = b::getReflectionClass(get_class($this));

        // run our before normalize even
        // must return an array
        $array = $this->beforeNormalize();
        if (!is_array($array)) {$array = []; }

        // loop through each property
        foreach ($ref->getProperties() as $prop) {
            if ($prop->isProtected()) {
                $val = $this->{$prop->name};

                if (is_object($val) && method_exists($val, 'asArray')) {
                    $val = $val->asArray();
                }

                $array[$prop->name] = $val;
            }
        }

        // normalize our array
        $array = $this->afterNormalize($array);

        if (!is_array($array)) {
            throw new \Exception("afterNormalize did not return an array (type: ".gettype($array));
        }

        return $array;
    }

    /**
     * return object as an array
     *
     * @return array
     */
    public function asArray() {
        return $this->normalize();
    }

    /**
     * serialize as json
     *
     * @return array
     */
    public function jsonSerialize() {
        return $this->normalize();
    }

    /**
     * return object as JSON string
     *
     * @return string
     */
    public function __toString() {
        return json_encode($this->normalize());
    }

    /**
     * save this object
     *
     * @see bolt\manager::save
     *
     * @return self
     */
    public function save() {
        $this->_manager->save($this);
        return $this;
    }


    /**
     * delete the object
     *
     * @see  bolt\manager::delete
     *
     * @return self
     */
    public function delete() {
        $this->_manager->delete($this);
        return $this;
    }

}
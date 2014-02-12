<?php

namespace bolt\source\curl;
use \b;

use \Doctrine\ORM\Mapping\ClassMetadata,
    \Doctrine\DBAL\Types\Type;

/**
 * curl model repository
 */
class repository {

    private $_curl;
    private $_entity;
    private $_driver;
    private $_map = false;

    /**
     * Constructor
     *
     * @param bolt\source\curl $curl
     * @param string $entity entity class
     * @param bolt\models\driver $driver
     */
    public function __construct(\bolt\source\curl $curl, $entity, \bolt\models\driver $driver) {
        $this->_curl = $curl;
        $this->_entity = $entity;
        $this->_driver = $driver;
    }


    /**
     * generate a class metadata map
     *
     * @return \Doctrine\ORM\Mapping\ClassMetadata
     */
    private function _map() {
        if (!$this->_map) {
            $map = new ClassMetadata($this->_entity);
            $this->_driver->loadMetadataForClass($this->_entity, $map);
            $this->_map = $map;
        }
        return $this->_map;
    }


    /**
     * generate a request url form $entity::curl or using the
     * default schema
     *
     * @param string $type request type
     * @param array $args
     *
     * @return array[string $url, array $query, array $headers]
     */
    private function _getRequestUri($type, $args) {
        if (method_exists($this->_entity, 'curl')) {
            var_dump('x'); die;
        }

        $map = $this->_map();

        switch($type) {
            case 'find':
                return [$map->getTableName()."/{$args[0]}", []];

            case 'findBy':
                return [$map->getTableName(), [
                        'query' => $args[0],
                        'order' => $args[1],
                        'limit' => $args[2],
                        'offset' => $args[3]
                    ],
                    []
                ];

        };
    }


    /**
     * transform the curl response to an item array
     *
     * @param string $type
     * @param array $data response from server
     *
     * @return [items => array, total => int]
     */
    private function _getTransform($type, $data) {
        return call_user_func([$this->_entity, 'transform'], $type, $data);
    }


    /**
     * generate a class of $entity from
     * the provided data
     *
     * @param array $item item data
     *
     * @return object
     */
    public function generateEntity($item) {
        $map = $this->_map();

        $entity = new $this->_entity;

        // get a relection of this method
        $ref = b::getReflectionClass($this->_entity);

        // loop through each field name
        foreach ($map->getFieldNames() as $name) {
            $_ = $map->getFieldMapping($name);
            $value = null;

            if (Type::hasType($_['type'])) {
                $value = Type::getType($_['type'])->convertToPHPValueSQL($item[$name]);
            }

            $prop = $ref->getProperty($name);

            $prop->setAccessible(true);
            $prop->setValue($entity, $value);

        }

        return $entity;
    }

    /**
     * find an entity
     *
     * @param mixed $id
     *
     * @return object
     */
    public function find($id) {

        // get our return url
        list($url, $query, $headers) = $this->_getRequestUri('find', func_get_args());

        // make our request
        $resp = $this->_curl->get($url, $headers, ['query' => $query])->send();

        // if we don't ahve a
        if ($resp->getStatusCode() !== 200) {
            return null;
        }

        // see if the enity wants to transform
        $data = $this->_getTransform('find', $resp->json());

        // generate an entity
        return $this->generateEntity($data);

    }


    /**
     * find an entity by query
     *
     * @param array $query
     * @param array $order
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function findBy($query, $order = [], $limit = false, $offset = 0) {

        // get our return url
        list($url, $query, $headers) = $this->_getRequestUri('findBy', func_get_args());

        // make our request
        $resp = $this->_curl->get($url, $headers, ['query' => $query])->send();

        // if we don't ahve a
        if ($resp->getStatusCode() !== 200) {
            return [];
        }

        // see if the enity wants to transform
        $data = $this->_getTransform('findBy', $resp->json());

        // items holder
        $items = [];

        // loop through and map our items
        foreach ($data['items'] as $item) {
            $items[] = $this->generateEntity($item);
        }

        return $items;

    }


    /**
     * find one entity by query
     *
     * @param array $query
     * @param array $order
     *
     * @return object
     */
    public function findOneBy($query, $order = []) {

        // get our return url
        list($url, $query, $headers) = $this->_getRequestUri('findOneBy', func_get_args());

        // make our request
        $resp = $this->_curl->get($url, $headers, ['query' => $query])->send();

        // if we don't ahve a
        if ($resp->getStatusCode() !== 200) {
            return [];
        }

        // see if the enity wants to transform
        $data = $this->_getTransform('findOneBy', $resp->json());


        return $this->generateEntity($data);

    }

}
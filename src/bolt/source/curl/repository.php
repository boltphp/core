<?php

namespace bolt\source\curl;
use \b;

use \Doctrine\ORM\Mapping\ClassMetadata,
    \Doctrine\DBAL\Types\Type;

/**
 * curl model repository
 */
class repository {

    protected $_curl;
    protected $_entity;
    protected $_driver;
    protected $_manager;
    protected $_map = false;

    /**
     * Constructor
     *
     * @param bolt\source\curl $curl
     * @param string $entity entity class
     * @param bolt\models\driver $driver
     */
    public function __construct(\bolt\source\curl $curl, $entity, \bolt\models $manager, \bolt\models\driver $driver) {
        $this->_curl = $curl;
        $this->_entity = $entity;
        $this->_manager = $manager;
        $this->_driver = $driver;
    }


    /**
     * generate a class metadata map
     *
     * @return \Doctrine\ORM\Mapping\ClassMetadata
     */
    protected function _map() {
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
    public function getRequestUri($type, $args) {
        if (method_exists($this->_entity, 'curlRequest')) {
            return call_user_func([$this->_entity, 'curlRequest'], $type, $args);
        }
        $map = $this->_map();
        switch($type) {
            case 'find':
                return [$map->getTableName()."/{$args[0]}.json", [], []];

            case 'findOneBy':
            case 'findBy':
                return [$map->getTableName().".json", [
                        'query' => $args[0],
                        'order' => $args[1],
                        'limit' => isset($args[2]) ? $args[2] : null,
                        'offset' => isset($args[3]) ? $args[3] : null
                    ],
                    []
                ];

            case 'create':
                return [$map->getTableName().".json", $args['data'], []];

            case 'update':
                return [$map->getTableName()."/{$args['id']}.json", $args['data'], []];

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
    public function getTransform($type, $data) {
        if (method_exists($this->_entity, 'transform')) {
            return call_user_func([$this->_entity, 'transform'], $type, $data);
        }
        else {
            return $data;
        }
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

        // we use a dummy platform
        $p = new \Doctrine\DBAL\Platforms\MySqlPlatform();

        // entity before
        if (method_exists($entity, 'curlBefore')) {
            $entity->curlBefore($map);
        }

        // loop through each field name
        foreach ($map->getFieldNames() as $name) {
            $_ = $map->getFieldMapping($name);
            $value = null;

            // target entity
            if (isset($_['targetEntity']) && isset($item[$name])) {
                // reach back to curl to get a repo for this entity
                $repo = $this->_curl->getRepository($_['targetEntity']);
                $value = $repo->generateEntity($item[$name]);
                $value->setLoaded(true);
                $value->setManager($this->_manager);
            }

            else if (Type::hasType($_['type'])) {
                $value = Type::getType($_['type'])->convertToPHPValue(b::param($name, null, $item), $p);
            }

            if (!$ref->hasProperty($name)) {
                continue;
            }

            $prop = $ref->getProperty($name);

            $prop->setAccessible(true);
            $prop->setValue($entity, $value);

        }

        $entity->setManager($this->_manager);
        $entity->setLoaded(true);

        // method exists
        if (method_exists($entity, 'curlAfter')) {
            $entity->curlAfter($map);
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
        list($url, $query, $headers) = $this->getRequestUri('find', func_get_args());

        $this->_curl->log('info', "[Curl.Respository.Find] $url?".http_build_query($query), ['headers' => $headers]);

        // make our request
        $resp = $this->_curl->get($url, $headers, ['query' => $query])->send();

        // if we don't ahve a
        if ($resp->getStatusCode() !== 200) {
            $this->_curl->log("WARNING", "[Curl.Respository.Find] {$resp->getStatusCode()}");
            return null;
        }

        // see if the enity wants to transform
        $data = $this->getTransform('find', $resp->json());

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
    public function findBy($query, $order = [], $limit = false, $offset = 0, &$total = 0) {

        // get our return url
        list($url, $query, $headers) = $this->getRequestUri('findBy', func_get_args());

        $this->_curl->log('info', "[Curl.Respository.FindBy] $url?".http_build_query($query), ['headers' => $headers]);

        // make our request
        $resp = $this->_curl->get($url, $headers, ['query' => $query])->send();

        // if we don't ahve a
        if ($resp->getStatusCode() !== 200) {
            $this->_curl->log("WARNING", "[Curl.Respository.FindBy] {$resp->getStatusCode()}");
            return [];
        }

        // see if the enity wants to transform
        $data = $this->getTransform('findBy', $resp->json());

        // items holder
        $items = [];

        $total = b::param('total', null, $data);

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
        list($url, $query, $headers) = $this->getRequestUri('findOneBy', func_get_args());

        $this->_curl->log('info', "[Curl.Respository.FindOneBy] $url?".http_build_query($query ?: []), ['headers' => $headers]);

        // make our request
        $resp = $this->_curl->get($url, $headers, ['query' => $query])->send();

        // if we don't ahve a
        if ($resp->getStatusCode() !== 200) {
            $this->_curl->log("WARNING", "[Curl.Respository.FindOneBy] {$resp->getStatusCode()}");
            return [];
        }

        // see if the enity wants to transform
        $data = $this->getTransform('findOneBy', $resp->json());

        if (!$data) {
            return [];
        }

        return $this->generateEntity($data);

    }


    /**
     * persist
     *
     * @param bolt\models\entity $entity
     *
     * @return bolt\models\entity
     */
    public function persist($entity) {
        $map = $this->_map();

        $id = $entity->getValue($map->getIdentifierFieldNames()[0], null);

        $type = $id === null ? 'create' : 'update';

        $data = $entity->normalize();

        // get our return url
        list($url, $query, $headers) = $this->getRequestUri($type, ['id' => $id, 'data' => $data]);

        // make our request
        if ($type == 'create') {
            $resp = $this->_curl->post($url, $headers, $query)->send();
        }
        else {
            $resp = $this->_curl->put($url, $headers, $query)->send();
        }

        // if we don't ahve a
        if ($resp->getStatusCode() !== 200) {
            $this->_curl->log("WARNING", "[Curl.Respository.Persist] {$type} {$resp->getStatusCode()}");
            return [];
        }

        // see if the enity wants to transform
        try {
            $data = $this->getTransform($type, $resp->json());
            $entity->set($data);
        }
        catch (\Exception $e) {
            $this->_curl->log("WARNING", "[Curl.Respository.Persist] Unable to format json response. {$resp->getStatusCode()} {$resp->getBody()}");
            return [];
        }

        return $entity;
    }


    public function flush() {
        // do nothing
    }

}

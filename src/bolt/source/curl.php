<?php

namespace bolt\source;

use \Guzzle\Http\Client;

class curl implements face {

    private $_client = false;

    public function __construct($config) {
        $base = 'http://teamcoco.com/api/{path}';

        $this->_client = new Client($base);


    }

    public function get($path, $query=[]) {
        $m = $this->_client->get($path);

        if ($query) {
            foreach ($query as $k => $v) {
                $m->getQuery->add($k, $v);
            }
        }

        return $m;
    }


    public function create() {

    }

    public function read() {

    }

    public function update() {

    }

    public function delete() {


    }

}

class curlSimple {

    private $_parent;

    public function __construct($parent) {
        $this->_parent = $parent;
    }

    public function get($path, $query=[], $headers=[]) {
        $m = $this->_parent->get($path, $query);

        foreach ($query as $k => $v) {
            $m->getQuery->add($k, $v);
        }

        $resp = $m->send();

    }

}
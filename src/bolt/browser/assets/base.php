<?php

namespace bolt\browser\assets;
use \b;

class base {

    protected $parent;

    protected $path;

    protected $groups = [];

    protected $content = false;

    public function __construct(\bolt\browser\assets $parent) {
        $this->parent = $parent;
    }

    public function __call($name, $args) {
        if (substr($name, 0, 3) == 'set') {
            $var = strtolower(substr($name, 3));
            $this->$var = $args[0];
            return $this;
        }
        return false;
    }

    public function addGroup($group){
        $this->groups[] = $group;
        return $this;
    }

    public function inGroup($group) {
        return in_array($group, $this->groups);
    }

}
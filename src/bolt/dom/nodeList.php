<?php

namespace bolt\dom;



class nodeList extends \bolt\helpers\collection {

    private $_dom;

    public function __construct(\bolt\dom $dom) {
        $this->_dom = $dom;
    }

    public function html() {
        $f = new fragment();
        foreach ($this as $node) {
            $f->append($node);
        }
        return $f->html();
    }

    public function __call($name, $args) {
        if (count($this) == 1) {
            return call_user_func_array([$this->first(), $name], $args);
        }
    }

}
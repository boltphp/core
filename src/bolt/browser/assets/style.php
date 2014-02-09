<?php

namespace bolt\browser\assets;
use \b;

class style extends base {

    protected $groups = ['head'];

    public function out() {

        if ($this->content) {
            return '<style type="text/css">'.$this->content.'</style>';
        }

        // find our file
        return $this->parent->stylesheet($this->path);
    }

}
<?php

namespace bolt\browser\assets;
use \b;

class script extends base {

    public function out() {

        if ($this->content) {
            return '<script>'.$this->content.'</script>';
        }

        // find our file
        return $this->parent->script($this->path);

    }

}
<?php

class dom_fragmentTest extends Test {



    public function setUp() {
        $this->f = new \bolt\dom\fragment();
    }

    // public function testGetChildren() {
    //     $this->f->html('hello <strong>strong</strong> test <b>bold</b>');
    //     $this->eq($this->f->children()->count(), 4);
    // }

}
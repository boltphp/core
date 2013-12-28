<?php

class StringTest extends Test {

    public function test_construct() {
        $this->assertInstanceOf('Stringy\Stringy', new \bolt\bucket\string('test'));
    }

}
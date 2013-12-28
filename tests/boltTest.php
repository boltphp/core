<?php

class boltTest extends Test {

    public function test_instance() {
        $this->assertInstanceOf('\bolt\base', b::instance());
    }

    public function test_path() {
        $this->assertEquals("/test/path", b::path('test', 'path'));
        $this->assertEquals("/test/path", b::path('test', '/path/'));
    }

    public function test_param() {
        $this->assertEquals('poop', b::param('poop', false, ['poop' => 'poop']));
        $this->assertEquals('poop', b::param('none', 'poop', []));
    }

}
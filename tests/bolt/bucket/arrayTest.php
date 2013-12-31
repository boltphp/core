<?php

class ArrayTest extends Test {

    public function setup() {
        $this->data = [
                'top' => 'poop',
                'nested' => [
                    'no key',
                    'key' => 'poop'
                ]
            ];
        $this->b = new \bolt\bucket\a($this->data);
    }

    public function test_normalize() {
        $this->assertEquals($this->data, $this->b->normalize());
    }

    public function test_getSet_simple() {
        $this->b->set('newnode', 'poop');
        $this->assertEquals('poop', (string)$this->b->get('newnode'));
    }

    public function test_getSet_dot() {
        $this->b->set('new.node', 'poop');
        $this->assertEquals('poop', (string)$this->b->get('new.node'));
        $this->b->set('new.node2.level', 'poop');
        $this->assertEquals('poop', (string)$this->b->get('new.node2.level'));
    }

    public function test_nested_getSet() {
        $nest = $this->b->get('nested');
        $this->assertInstanceOf('bolt\bucket\a', $nest);
        $nest->set('poop', 'nested');
        $this->assertEquals('nested', (string)$nest->get('poop'));
    }

}
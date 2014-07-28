<?php

class helpers_collectionTest extends Test {

    public function setUp() {
        $this->c = new \bolt\helpers\collection(['a','b','c','d']);
    }

    public function test_first() {        
        $this->eq('a', $this->c->first());
    }

    public function test_last() {
        $this->eq('d', $this->c->last());
    }

    public function test_eachClosure() {
        $this->c->each(function($item, $key, $data, $obj){
            $obj[$key] = $item.'1';
        });
        $this->eq(
            ['a1','b1','c1','d1'],
            $this->c->toArray()
        );
    }

    public function test_eachClass() {
        $c = new helpers_collectionTest_Test(['a','b','c','d']);
        $c->each('addOne');
        $this->eq(
            ['a1','b1','c1','d1'],
            $c->toArray()
        );   
    }

    public function test_filter() {
        $this->c->filter(function($item){
            return $item != 'a';
        });
        $this->eq(
            [1 => 'b', 2 => 'c', 3 => 'd'],
            $this->c->toArray()
        );      
    }

    public function test_splice() {
        $this->c->splice(1,2);
        $this->eq(
            ['a','d'],
            $this->c->toArray()
        );
    }

    public function test_spliceReplace() {
        $this->c->splice(1,2,['e']);
        $this->eq(
            ['a','e','d'],
            $this->c->toArray()
        );
    }

    public function test_slice() {
        $this->c->slice(1,2);
        $this->eq(
            [1 => 'b', 2 => 'c'],
            $this->c->toArray()
        );
    }

    public function test_sliceNoKeys() {
        $this->c->slice(1,2,false);
        $this->eq(
            ['b','c'],
            $this->c->toArray()
        );
    }

    public function test_map() {
        $this->c->map(function($item){
            return 'x';
        });
        $this->eq(
            ['x','x','x','x'],
            $this->c->toArray()
        );   
    }

}

class helpers_collectionTest_Test extends bolt\helpers\collection {

    public function addOne($item, $key, $data, $obj) {
        $obj[$key] = $item.'1';
    }

}
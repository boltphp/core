<?php

class helpers_baseTest extends Test {

    public function setUp() {
        $this->b = new bolt\helpers\base();
    }

    public function test_param() {
        $a = ['test' => 9];
        $this->eq(9, $this->b->param('test', null, $a));
        $this->eq(null, $this->b->param('nope', null, $a));
        $this->eq(99, $this->b->param('nope', 99, $a));
    }

    public function test_paramFilter(){
        $a = ['a' => 9, 'b' => 'a9'];
        $this->eq('9', $this->b->param('b', null, $a, FILTER_SANITIZE_NUMBER_INT));
        $this->eq(9, $this->b->param('a', null, $a, FILTER_SANITIZE_NUMBER_INT));
    }

    public function test_mergeArray() {
        $a = ['test1' => 1];
        $b = ['test2' => 2];
        $this->eq(['test1' => 1, 'test2' => 2], $this->b->mergeArray($a, $b));
        $a['b'] = $b;
        $this->eq(['test1' => 1, 'test2' => 2, 'b' => ['test2' => 2]], $this->b->mergeArray($a, $b));
    }

}
<?php

class helpers_events_eventTest extends Test {

    public function setUp() {
        $this->parent = new helpers_events_eventTest_Class();
        $this->args = ['test_arg' => 1];
        $this->l = new bolt\helpers\events\listener($this->parent, function(){}, 'test', $this->args);
        $this->data = ['test_data' => 1];
        $this->e = new bolt\helpers\events\event($this->l, $this->data);
    }

    public function test_magicGet() {
        $this->eq($this->parent, $this->e->parent);
        $this->eq($this->l, $this->e->listener);
        $this->eq($this->args, $this->e->args);
        $this->eq($this->data, $this->e->data);
        $this->eq('test', $this->e->type);
        $this->eq(1, $this->e->test_data);
        $this->eq(null, $this->e->poop);
    }

    public function test_data() {
        $this->eq(1, $this->e->data("test_data"));
        $this->eq(1, $this->e->data("test_data", 'poop'));
        $this->eq(null, $this->e->data("poop"));
        $this->eq(true, $this->e->data("poop", true));
        $this->eq(null, $this->e->data("test_arg"));
    }

    public function test_arg() {
        $this->eq(1, $this->e->arg("test_arg"));
        $this->eq(1, $this->e->arg("test_arg", 'poop'));
        $this->eq(null, $this->e->arg("poop"));
        $this->eq(true, $this->e->arg("poop", true));
        $this->eq(null, $this->e->arg("test_data"));
    }

}



class helpers_events_eventTest_Class {
    use \bolt\helpers\events;

}

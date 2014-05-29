<?php

class dom_fragmentTest extends Test {


    public function test() {
        $this->markTestIncomplete('soon...');
    }

    // public function setUp() {
    //     $this->f = new \bolt\dom\fragment();
    // }

    // public function test_inherits() {
    //     $this->eq(true, in_array('bolt\dom', class_parents($this->f)));
    // }

    // public function test_getSetHtmlSimple() {
    //     $this->eq('', $this->f->html());

    //     $html = '<b>test</b>';
    //     $this->eq($this->f, $this->f->html($html));
    //     $this->eq($html, $this->f->html());

    //     $html = '<i>test</i>';
    //     $this->eq($this->f, $this->f->html($html));
    //     $this->eq($html, $this->f->html());

    // }

    // public function test_getSetHtmlDeep() {

    //     $this->eq('', $this->f->html());

    //     $html = '<b>test <strong>x</strong> <div>2</div></b> test';
    //     $this->eq($this->f, $this->f->html($html));
    //     $this->eq($html, $this->f->html());

    // }

    // public function test_find() {
    //     $html = '<i>test</i>';
    //     $this->f->html($html);
    //     $i = $this->f->find('i');
    //     $this->assertInstanceOf('bolt\dom\nodeList', $i);
    //     $this->eq(1, $i->count());
    //     $this->eq('test', $i->first()->html());
    // }

    // public function test_append() {

    //     $el = $this->f->create('div', '1');

    //     $this->eq($this->f, $this->f->append($el));

    //     $this->eq("<div>1</div>", $this->f->html());

    // }

}
<?php

class boltTest extends Test {

    public function test_defaultHelpers() {
        $this->eq(
                ['\bolt\helpers\base',
                '\bolt\helpers\classes',
                '\bolt\helpers\fs'],
                bolt::$helpers
            );
    }

    public function test_init() {
        $this->assertInstanceOf('bolt\application', bolt::init());
    }

    public function test_instance() {
        $i1 = bolt::instance();
        $this->assertInstanceOf('bolt\base', $i1);
        $i2 = bolt::instance();
        $this->assertInstanceOf('bolt\base', $i2);
        $this->eq($i1, $i2);
    }

    public function test_bShortcut() {
        $this->assertTrue(in_array('bolt', class_parents('b')));
    }

}


<?php

class configTest extends Test {

    public function setUp() {
        $this->d = ['test_top' => '1', 'test_nested' => ['test2' => 2]];
        $this->a = new bolt\application();
        $this->c = new bolt\config($this->a, ['register' => [ ['test1', $this->d] ]]);
    }

    public function test_implements() {
        $imp = class_implements($this->c);
        $this->assertTrue(in_array('ArrayAccess', $imp));
        $this->assertTrue(in_array('IteratorAggregate', $imp));

    }

    public function test_registerInConstruct() {
        $c = new bolt\config($this->a, [
                'register' => [
                    ['test', ['test' => 1]]
                ]
            ]);
        $this->assertTrue(array_key_exists('test', $c->getRegistered()));
    }

    public function test_registerSingle() {
        $this->eq($this->c, $this->c->register('test', ['test' => 1]));
        $this->assertTrue(array_key_exists('test', $this->c->getRegistered()));
    }

    public function test_registerMultiple(){
        $this->eq($this->c, $this->c->register([
                ['test1', ['test1' => 1]],
                ['test2', ['test2' => 2]]
            ]));
        $reg = $this->c->getRegistered();
        $this->assertFalse(array_key_exists('test', $reg));
        $this->assertTrue(array_key_exists('test1', $reg));
        $this->assertTrue(array_key_exists('test2', $reg));
    }

    public function test_registerMergeEnvData() {
        $data = [
            'test' => 1,
            '_dev' => ['test' => 2],
            '_prod' => ['test' => 3]
        ];

        $this->c->register('test3', $data);
        $this->eq(2, $this->c['test3.test']);


        b::env('prod');

        $this->c->register('test4', $data);
        $this->eq(3, $this->c['test4.test']);

        b::env('eq');


        $this->c->register('test5', $data);
        $this->eq(1, $this->c['test5.test']);

    }

    public function test_getNamespace() {
        $this->eq($this->d, $this->c->get('test1'));
    }

    public function test_getNamespaceAndValue() {
        $this->eq($this->d['test_top'], $this->c->get('test1.test_top'));
    }

    public function test_getNested() {
        $this->eq($this->d['test_nested']['test2'], $this->c->get('test1.test_nested.test2'));
    }

    public function test_getDefault() {
        $this->eq(-1, $this->c->get('xxxx', -1));
    }

    public function test_setNamespace() {
        $this->eq($this->c, $this->c->set('test2', ['poop' => 1]));
        $this->eq(1, $this->c->get('test2.poop'));
    }

    public function test_setNamespaceValue() {
        $this->eq($this->c, $this->c->set('test2.poop1', ['poop' => 1]));
        $this->eq(1, $this->c->get('test2.poop1.poop'));
    }

    public function test_remove() {
        $reg = $this->c->getRegistered();
        $this->assertTrue(array_key_exists('test1', $reg));
        $this->eq($this->c, $this->c->remove('test1'));
        $reg = $this->c->getRegistered();
        $this->assertFalse(array_key_exists('test1', $reg));
    }

    public function test_exists(){
        $this->assertTrue($this->c->exists('test1'));
        $this->assertTrue($this->c->exists('test1.test_nested'));
        $this->assertFalse($this->c->exists('test1.xxx'));
    }

    public function test_magic() {
        $this->eq($this->d, $this->c->test1);
    }

    public function test_arrayAccess() {
        $this->eq($this->d, $this->c['test1']);
        $this->eq($this->d['test_top'], $this->c['test1.test_top']);
        $this->eq(false, $this->c['xxxx']);

        $this->assertTrue( isset($this->c['test1']) );

        $set = ['x' => 1];
        $this->eq($set, $this->c['test2'] = $set);
        $this->eq($set, $this->c['test2']);

        unset($this->c['test2']);

        $this->assertFalse(isset($this->c['test2']));

    }

    public function test_getIterator() {
        $this->assertInstanceOf('ArrayIterator', $this->c->getIterator());
    }

    public function test_readFile() {
        $c = new configTest_Class($this->a);

        // tmp file
        $tmp = sys_get_temp_dir()."/config_test.json";

        file_put_contents($tmp, json_encode($this->d));

        $this->eq($this->d, $c->test_readFile($tmp));

        unlink($tmp);

    }

}


class configTest_Class extends \bolt\config {

    public function test_readFile($file) {
        return $this->_readFile($file);
    }

}
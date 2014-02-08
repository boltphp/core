<?php

class pluginTest extends Test {

    public function setUp() {
        $this->p = new pluginTest_TestClass();
    }

    public function test_inherits() {
        $this->assertTrue(in_array('bolt\plugin', class_parents($this->p)));
        $this->assertTrue(in_array('ArrayAccess', class_implements($this->p)));
    }

    public function test_plugArray() {
        $this->p->plug([
                ['test1', 'pluginTest_TestPluginSingleton'],
                ['test2', 'pluginTest_TestPluginFactory']
            ]);
        $this->assertTrue($this->p->pluginExists('test1'));
        $this->assertTrue($this->p->pluginExists('test2'));
    }

    public function test_plugSingltonString() {
        $this->eq($this->p, $this->p->plug('test', 'pluginTest_TestPluginSingleton'));
        $this->assertTrue(array_key_exists('test', $this->p->getPlugins()));
        $this->eq('singleton', $this->p->getPlugins()['test']['type']);
        $this->assertInstanceOf('pluginTest_TestPluginSingleton', $this->p->plugin('test'));

    }

    public function test_pluginSingletonObject() {
        $i = new pluginTest_TestPluginSingleton();
        $this->eq($this->p, $this->p->plug('test', $i));
        $this->assertTrue(array_key_exists('test', $this->p->getPlugins()));
        $this->eq('singleton', $this->p->getPlugins()['test']['type']);
        $this->eq($i, $this->p->plugin('test'));
    }


    public function test_pluginSingletonWithFirstRun() {
        $i = new pluginTest_TestPluginSingletonWithFirstRun();
        $this->p->plug('test1', $i);
        $this->assertFalse($i->runFirstRun);
        $this->assertTrue($this->p->plugin('test1')->test());
        $this->assertTrue($i->runFirstRun);
    }

    public function test_pluginWithConstructParams() {
        $c = ['test' => 'poop'];
        $this->p->plug('test', 'pluginTest_TestWithConstructParams', $c);
        $this->eq($this->p, $this->p->plugin('test')->parent);
        $this->eq($c, $this->p->plugin('test')->config);
    }

    public function test_pluginFactory() {
        $this->eq($this->p, $this->p->plug('test', 'pluginTest_TestPluginFactory'));
        $this->assertTrue(array_key_exists('test', $this->p->getPlugins()));
        $this->eq('factory', $this->p->getPlugins()['test']['type']);
        $this->assertInstanceOf('StdClass', $this->p->plugin('test'));
    }


    public function test_unknownClass() {
        $this->setExpectedException('Exception');
        $this->p->plug('nope', uniqid());
    }

    public function test_getPlugins() {
        $this->assertTrue(is_array($this->p->getPlugins()));
    }

    public function test_pluginExists() {
        $this->p->plug('test', 'pluginTest_TestPluginSingleton');
        $this->assertTrue($this->p->pluginExists('test'));
        $this->assertFalse($this->p->pluginExists('not me'));
    }

    public function test_pluginDoesNotExist(){
        $this->setExpectedException('Exception');
        $this->p->plugin(uniqid());
    }

    public function test_unplug() {
        $i = new pluginTest_TestPluginSingleton();
        $this->p->plug('test', $i);
        $this->assertTrue( $this->p->pluginExists('test') );
        $this->p->unplug('test');
        $this->assertFalse( $this->p->pluginExists('test') );
    }

    public function test_unplugUknown() {
        $this->setExpectedException('Exception');
        $this->p->unplug(uniqid());
    }

    public function test_arrayAccess() {
        $i = new pluginTest_TestPluginSingleton();
        $this->p->plug('test', $i);
        $this->eq($i, $this->p['test']);
        $this->assertTrue(isset($this->p['test']));

        unset($this->p['test']);
        $this->assertFalse(isset($this->p['test']));

        $i = new pluginTest_TestPluginSingleton();
        $this->p['test'] = $i;
        $this->eq($i, $this->p['test']);

    }

}

class pluginTest_TestClass extends \bolt\plugin {

}

class pluginTest_TestPluginSingleton implements \bolt\plugin\singleton {

}

class pluginTest_TestPluginSingletonWithFirstRun implements \bolt\plugin\singleton {
    public $runFirstRun = false;

    public function firstRun() {
        $this->runFirstRun = true;
    }

    public function test() {
        return true;
    }

}

class pluginTest_TestWithConstructParams implements \bolt\plugin\singleton {

    public function __construct(pluginTest_TestClass $parent, $config, $poop = 'poop', $nope=null) {
        $this->parent = $parent;
        $this->config = $config;
    }

}

class pluginTest_TestPluginFactory implements \bolt\plugin\factory {

    public static function factory($config = []) {
        $c = new StdClass();
        return $c;
    }

}
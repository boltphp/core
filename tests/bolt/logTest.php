<?php

use Monolog\Logger;

class logTest extends Test {


    public function setUp() {
        $this->app = $this->getApp();
        $this->log = new bolt\log($this->app, ['name' => 'test']);
    }

    public function test_factory() {
        $log = bolt\log::factory($this->app, ['name' => 'fact']);
        $this->assertInstanceOf('bolt\log', $log);
    }

    public function test_badConstruct() {
        $this->setExpectedException('Exception');
        new \bolt\log($this->app, []);
    }

    public function test_getInstance() {
        $this->assertInstanceOf('Monolog\Logger', $this->log->getInstance());
    }

    public function test_getName() {
        $this->eq('test', $this->log->getName());
    }

    public function test_goodLevel() {
        $this->eq(Logger::ERROR, $this->log->level('ERROR'));
    }

    public function test_badLevel() {
        $this->eq(null, $this->log->level("NOT_A_LEVEL"));
    }

    public function test_goodMagicCall() {
        $this->eq(true, $this->log->debug('aa'));
    }

    public function test_badMagicCall(){
        $this->eq(null, $this->log->not_a_func());
    }

    public function test_handler() {
        $this->eq($this->log, $this->log->handler('errorlog'));
        $this->assertInstanceOf('Monolog\Handler\ErrorLogHandler', $this->log->popHandler());
    }

    public function test_badHandler() {
        $this->setExpectedException('Exception');
        $this->log->handler('NOT_A_HANDLER');
    }

    public function test_processor() {
        $this->eq($this->log, $this->log->processor('tag'));
        $this->assertInstanceOf('Monolog\Processor\TagProcessor', $this->log->popProcessor());
    }

    public function test_badProcessor() {
        $this->setExpectedException('Exception');
        $this->log->processor('NOT_A_PROCESSOR');
    }

}

<?php

interface TestClassInterface {


}

class TestClass implements TestClassInterface {

    public $publicProperty = true;
    protected $protectedProperty = true;
    private $privateProperty = true;

    public function publicMethod() {

    }

    protected function protectedMethod() {

    }

    private function privateMethod() {

    }

}

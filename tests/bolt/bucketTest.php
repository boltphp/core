<?php

class BucketTest extends Test {

    public function setup() {
        $this->bucket = new TestBucketClass();
    }

    public function test_createString() {
        $this->assertInstanceOf('bolt\bucket\string', \bolt\bucket::create('test'));
    }

}

class TestBucketClass extends \bolt\bucket {

}
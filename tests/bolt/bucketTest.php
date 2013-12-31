<?php

class BucketTest extends Test {

    public function setup() {
        $this->bucket = new TestBucketClass();
    }

    public function test_createString() {
        $this->assertInstanceOf('bolt\bucket\s', \bolt\bucket::create('test'));
    }

}

class TestBucketClass extends \bolt\bucket {

}
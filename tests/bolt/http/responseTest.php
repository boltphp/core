<?php

class responseTest extends Test {

    public function setUp(){
        $this->resp = new \bolt\http\response();
    }

    public function testGuid()  {
        $this->eq(true, !empty($this->resp->guid()));
        $r = new \bolt\http\response();
        $this->assertFalse($this->resp->guid() == $r->guid());
    }

    public function test_addHTMLFormat() {

        $r = $this->resp->format('html', 'test_html');

        $this->assertInstanceOf('bolt\http\response\format\html', $r);

        $this->eq('test_html', $r->getFormat('html')->getContent());

    }

    public function test_badFormat() {
        $this->setExpectedException('Exception');
        $this->resp->format("UNKNOWN", "test");
    }

    public function test_hasformat(){
        $this->eq(false, $this->resp->hasFormat('html'));
        $this->resp->format('html', 'xx');
        $this->eq(true, $this->resp->hasFormat('html'));
    }

    public function test_addMutiFormats() {
        $formats = [
            'html' => 'test_html',
            'json' => 'test_json'
        ];

        $r = $this->resp->format($formats);

        $this->eq($r, $this->resp);

        $this->eq('test_html', $r->getFormat('html')->getContent());
        $this->eq('test_json', $r->getFormat('json')->getContent());

    }

    public function test_customFormat() {
        $r = $this->resp->format('bolt_responseTestFormat', 'test');
        $this->assertInstanceOf('bolt_responseTestFormat', $r);
        $this->eq('test', $r->getFormat('bolt_responseTestFormat')->getContent());
    }

    public function test_setHeader() {
        $r = $this->resp->setHeader('x-test', 'test');
        $this->eq($r, $this->resp);
        $this->eq('test', $this->resp->headers->get('x-test'));
    }

    public function test_readytosend() {
        $this->eq(false, $this->resp->isReadyToSend());
        $this->eq($this->resp, $this->resp->readyToSend());
        $this->eq(true, $this->resp->isReadyToSend());
    }

    public function test_getsetlayout() {
        $this->eq(null, $this->resp->getLayout());
        $this->eq($this->resp, $this->resp->setLayout('test'));
        $this->eq('test', $this->resp->getLayout());
    }

    public function test_getformat() {
        $this->eq(null, $this->resp->getFormat('html'));
        $r = $this->resp->format('html', 'test_html');
        $this->assertInstanceOf('bolt\http\response\format\html', $r);
        $this->eq($r, $this->resp->getFormat('html'));
    }

    public function test_inherits() {
        $this->assertTrue( in_array('Symfony\Component\HttpFoundation\Response', class_parents($this->resp)) );
    }

    public function test_setGetGoodException() {
        $this->eq(null, $this->resp->getException());
        $e = new Exception('bad', 500);
        $this->eq($this->resp, $this->resp->setException($e));
        $this->eq($e, $this->resp->getException());
    }

    public function test_setBadException() {
        $this->setExpectedException('Exception');
        $o = new StdClass();
        $this->resp->setException($o);
    }

    public function test_hasException() {
        $this->eq(false, $this->resp->hasException());
        $this->resp->setException(new Exception(""));
        $this->eq(true, $this->resp->hasException());
    }

}

class bolt_responseTestFormat extends \bolt\http\response\format {


}

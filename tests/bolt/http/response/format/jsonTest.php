<?php

class format_jsonTest extends Test {

    public function setUp() {
        $r = new bolt\http\response;
        $this->f = new bolt\http\response\format\json($r);
    }

    public function test_contentType() {
        $this->eq('application/json', $this->f->getContentType());
    }

    public function test_formatValidJson() {
        $this->eq(json_encode(['test']), $this->f->format(['test']));
    }

    public function test_formatInvalidJson() {
        $this->setExpectedException('Exception');
        $this->f->format(tmpfile());
    }

}
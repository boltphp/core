<?php

class format_htmlTest extends Test {

    public function test_contentType() {
        $r = new bolt\http\response();
        $f = new bolt\http\response\format\html($r);
        $this->eq('text/html', $f->getContentType());
    }

}
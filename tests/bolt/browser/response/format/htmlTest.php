<?php

class format_htmlTest extends Test {

    public function test_contentType() {
        $r = new bolt\browser\response();
        $f = new bolt\browser\response\format\html($r);
        $this->eq('text/html', $f->getContentType());
    }

}
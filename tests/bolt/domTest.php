<?php

class domTest extends Test {

    public function setUp() {
        $this->d = new \bolt\dom();
    }

    public function test_doc() {
        $this->assertInstanceOf('DOMDocument', $this->d->doc());
    }

    public function test_getSetHTML() {
        $this->eq("\n", $this->d->html());

        $html = '<i>test</i>';

        $this->eq($this->d, $this->d->html($html));

        $this->eq('<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html><body>'.$html.'</body></html>
', $this->d->html());

        $html = '<b>test</b>';

        $this->eq($this->d, $this->d->html($html));

        $this->eq('<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html><body>'.$html.'</body></html>
', $this->d->html());

    }




}
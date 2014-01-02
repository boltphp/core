<?php

class assetsTest extends Test {

    public function setup() {
        $this->a = new bolt\browser\assets();
    }

    public function test_processString() {

        $str = '
            $file file1.css file2.css
            $glob dir/*.css
            $dir dir
            $filter filter_name
            $ignore bad
        ';

        $resp = $this->a->processString($str);

        var_dump($resp); die;

    }

}

<?php

class assetsTest extends Test {

    public function setup() {
        $this->dir = realpath(MOCK_DIR."/assets");
        $this->a = new bolt\browser\assets();
        $this->a->addPaths([$this->dir]);
    }

    public function test_parseString() {

        $str = '
            $file file1.css sub/file2.css
            $dir sub/sub1/
            $glob sub/sub1/*.css
            $filter less
        ';

        $exp = [
            'file' => [
                b::path($this->dir, 'file1.css'),
                b::path($this->dir, 'sub/file2.css')
            ],
            'dir' => [
                b::path($this->dir, 'sub/sub1/')
            ],
            'glob' => ['sub/sub1/*.css'],
            'filter' => ['less']
        ];

        $this->assertEquals($exp, $this->a->parseString($str));

    }

    public function test_getCombinedTree() {
        $found = [
            'file' => [
                b::path($this->dir, 'file1.css')
            ],
            'dir' => [
                b::path($this->dir, 'sub')
            ]
        ];

        $ext = 'css';

        $exp = [
            $found['file'][0] => [],
            b::path($this->dir, 'file1.css') => [],
            b::path($this->dir, 'sub/file2.css') => [],
            b::path($this->dir, 'sub/sub1/file.css') => []

        ];

        $this->assertEquals($exp, $this->a->getCombinedTree($found, $ext));

    }

    public function test_filter() {
        $this->a->filter('less', 'lessphp');
        $this->a->filter('poop', 'poop');
        $this->assertEquals(['poop' => [], 'less' => ["\\Assetic\\Filter\\lessphpFilter"]], $this->a->getFilters());
    }

    public function test_processFile() {

        // we need out less filter
        $this->a->filter('less', 'lessphp');

        $file = b::path($this->dir, 'less/file.less');

        $exp = "body header {\n  background: red;\n}\n".
               "footer {\n  background: green;\n}";


        $this->assertEquals($exp, $this->a->processFile($file));
    }

}

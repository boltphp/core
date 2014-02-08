<?php

namespace bolt\browser\response\format;
use \b;

class html extends \bolt\browser\response\format {

    public $contentType = 'text/html';


    public function useLayout() {
        return true;
    }


}
<?php

namespace bolt\dom;
use \b;

class fragment extends document {

    public function __construct($charset = 'UTF-8', $html = null) {
        parent::__construct($charset);      
        if ($html) {
            $this->setHTML($html);  
        }
    }

    public function setHTML($html) {
        @$this->loadHTML("<div id='{$this->guid}'>{$html}</div>", LIBXML_HTML_NOIMPLIED + LIBXML_HTML_NODEFDTD + LIBXML_NOERROR + LIBXML_NOWARNING + LIBXML_NOXMLDECL);
        return $this;   
    }

    public function getHTML($el = null) {
        return $this->find("#{$this->guid}")->nodeValue;
    }

    public function saveHTML($el = null) {
        $el = $this->find("#{$this->guid}");
        return parent::saveHTML($el);
    }    

    public function children() {
        return $this->find("#{$this->guid} *");
    }

}

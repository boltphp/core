<?php

namespace bolt\dom;
use \b;

class fragment  { //extends \bolt\dom {

    // protected $hasRoot = true;

    // protected function rootId() {
    //     return "{$this->guid()}root";
    // }

    // public function rootNode() {
    //     return $this->doc()->getElementById($this->rootId());
    // }

    // public function html($html = null) {
    //     if ($html !== null) {
    //         @$this->doc()->loadHTML("<div id='{$this->rootId()}'>{$html}</div>", LIBXML_HTML_NOIMPLIED + LIBXML_HTML_NODEFDTD + LIBXML_NOERROR + LIBXML_NOWARNING + LIBXML_NOXMLDECL);
    //         return $this;
    //     }
    //     else {

    //         $ref = $this->cleanDomNodes();
    //         $root = $ref->getElementById($this->rootId());
    //         $parts = [];
    //         if ($root AND $root->hasChildNodes()) {
    //             foreach ($root->childNodes as $node) {
    //                 $parts[] = $ref->saveHTML($node);
    //             }
    //         }
    //         return trim(implode("", $parts));
    //     }
    // }

    // public function root($tag = null) {
    //     if ($tag !== null) {
    //         $this->html("<{$tag} id='{$this->rootId()}'></{$tag}>");
    //         return $this;
    //     }
    //     else {
    //         return $this->find("#{$this->rootId()}", true, false);
    //     }
    // }

    // public function append($what) {
    //     $this->root()->append($what);
    //     return $this;
    // }

    // public function children() {
    //     return $this->find("> *");
    // }

}

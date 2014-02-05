<?php

namespace bolt\browser;
use \b;


use Symfony\Component\CssSelector\CssSelector;

use \DOMDocument;
use \DOMXPath;


class dom implements \ArrayAccess {

    protected $_doc;

    private $_ref = [];
    private $_guid;

    public function __construct($html=false, $charset='UTF-8') {

        $this->_doc = new DOMDocument('1.0', $charset);
        $this->_doc->validateOnParse = true;
        $this->_doc->preserveWhiteSpace = false;
        $this->_doc->resolveExternals = false;
        $this->_doc->formatOutput = b::env() === 'dev';

        if ($html) {
            $this->_doc->loadHTML($html);
        }

        $this->_guid = b::guid('domref');

    }

    public function guid() {
        return $this->_guid;
    }

    public function doc() {
        return $this->_doc;
    }

    public function addRef($el) {
        $this->_ref[$el->guid()] = $el;
    }


    public function importNode($node, $deep=false) {
        return $this->_doc->importNode($node, $deep);
    }

    public function find($query, $returnRef=true) {
        $xpath = CssSelector::toXPath($query);
        $prefixes = $this->findNamespacePrefixes($xpath);

        $x = new DOMXPath($this->_doc);

        $nl = new dom\nodeList($this);

        foreach ($x->query($xpath) as $node) {
            $dr = $node->getAttribute('data-domref');

            if ($returnRef AND $dr AND array_key_exists($dr, $this->_ref)) {
                $nl->attach($this->_ref[$dr]);
            }
            else {
                $nl->attach(new dom\element($this, $node));
            }
        }

        return $nl;
    }

    private function findNamespacePrefixes($xpath) {
        if (preg_match_all('/(?P<prefix>[a-z_][a-z_0-9\-\.]*):[^"\/]/i', $xpath, $matches)) {
            return array_unique($matches['prefix']);
        }
        return array();
    }

    public function html() {
        $ref = clone $this->_doc;


        $xpath = new DOMXPath($ref);

        foreach ($xpath->query(CssSelector::toXPath('*[data-domref]')) as $node) {
            $node->removeAttribute('data-domref');
        }

        return $ref->saveHTML();
    }

    public function offsetExists($name) {
        return count($this->find($name)) > 0;
    }

    public function offsetGet($name) {
        return $this->find($name);
    }

    public function offsetSet($name, $value) {
        $el = $this->find($name)->first();

        if ($el->hasChildNodes()) {
            foreach ($el->childNodes as $child) {
                $child->parentNode->removeChild($child);
            }
        }

        if (stripos($value, '<') !== false AND stripos($value, '>') !== false) {
            $guid = b::guid("_x_dom");

            $_ = new \DOMDocument();
            $_->loadHTML("<div id='{$guid}'>".$value."</div>");

            $children = $_->getElementById($guid)->childNodes;

            foreach ($children as $child) {
                $el->appendChild($this->_doc->importNode($child, true));
            }

        }
        else {
            $el->appendChild(new \DOMText($value));
        }

        return $this;
    }

    public function offsetUnset($name) {
        $el = $this->find($name);
        $el->parentNode->removeChild($el);
    }

    public function __toString() {
        return $this->html();
    }

    public function create($tag, $value="", $attr=[]) {
        $el = $this->_doc->createElement($tag, $value);
        $_ = new dom\element($this, $el);
        $_->attr($attr);
        return $_;
    }


}
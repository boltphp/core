<?php

namespace bolt;
use \b;

use Symfony\Component\CssSelector\CssSelector;

use \DOMDocument;
use \DOMXPath;

class dom implements \ArrayAccess {

    protected $hasRoot = false;

    private $_doc;

    private $_ref = [];

    private $_guid;

    final public function __construct($html = "", $charset = 'UTF-8') {
        $this->_doc = new DOMDocument('1.0', $charset);
        $this->_doc->validateOnParse = false;
        $this->_doc->preserveWhiteSpace = false;
        $this->_doc->resolveExternals = false;
        $this->_doc->formatOutput = b::env() === 'dev';

        $this->_guid = b::guid('domref');

        $this->html($html);

        $this->init();

    }


    public function init() {}


    public function guid() {
        return $this->_guid;
    }

    public function doc() {
        return $this->_doc;
    }

    public function html($html = null) {
        if ($html) {
            @$this->_doc->loadHTML((string)$html, LIBXML_NOERROR + LIBXML_NONET + LIBXML_NOWARNING + LIBXML_NOXMLDECL);
            return $this;
        }
        else {
            return $this->cleanDomNodes()->saveHTML();
        }
    }

    public function addRef($el) {
        $this->_ref[$el->guid()] = $el;
        return $this;
    }

    public function import($what, $deep = false) {
        if (is_a($what, 'bolt\dom\node')) {
            $what = $what->node();
        }

        return $this->_doc->importNode($what, $deep);
    }

    public function append($what) {
        if (is_a($what, 'bolt\dom\node')) {
            $this->_doc->documentElement->appendChild($what->node());
        }
        else if (is_array($what, 'bolt\dom\nodeList')) {
            foreach ($what as $node) {
                $this->append($node);
            }
            return $this;
        }
        else if (is_array($what, 'bolt\dom\fragement')) {
            $this->append($what->children());
        }

        return $this;
    }

    public function create($tag, $value = null, $attr = []) {
        $el = $this->_doc->createElement($tag, html_entity_decode($value, ENT_QUOTES, 'utf-8'));
        $_ = new dom\node($this, $el);
        $_->attr($attr);
        return $_;
    }

    public function find($query, $returnRef = true, $useRoot = true) {
        if ($this->hasRoot && $useRoot) {
            $query = "#{$this->rootId()} $query";
        }

        $xpath = CssSelector::toXPath(trim($query));
        $prefixes = $this->_findNamespacePrefixes($xpath);

        $x = new DOMXPath($this->_doc);

        $nl = new dom\nodeList($this);

        foreach ($x->query($xpath) as $node) {
            $dr = $node->getAttribute('data-domref');

            if ($returnRef && $dr && array_key_exists($dr, $this->_ref)) {
                $nl->push($this->_ref[$dr]);
            }
            else {
                $nl->push(new dom\node($this, $node));
            }
        }

        return $nl;
    }


    /**
     * check if a node exists using a CSS selector
     *
     * @param string $name CSS selector
     *
     * @return bool
     */
    public function offsetExists($name) {
        return count($this->find($name)) > 0;
    }


    /**
     * get a node using a CSS selector
     *
     * @param string $name css selector
     *
     * @return mixed
     */
    public function offsetGet($name) {
        return $this->find($name);
    }


    /**
     * create a node or replace an exists node
     *
     * @param string $name CSS selector
     * @param mixed $value
     *
     * @return self
     */
    public function offsetSet($name, $value) {
        $el = $this->find($name)->first();

        if (!$el) {
            return false;
        }

        $el->html($value);

        return $this;
    }


    /**
     * remove a node using a CSS selector
     *
     * @param string $name CSS selector
     *
     * @return void
     */
    public function offsetUnset($name) {
        $el = $this->find($name);
        if ($el) {
            $el->remove();
        }
    }

    public function __toString() {
        return (string)$this->html();
    }


    protected function cleanDomNodes() {
        $ref = clone $this->_doc;
        $xpath = new DOMXPath($ref);

        foreach ($xpath->query('//*[@*]') as $node) {
            $node->removeAttribute('data-domref');
            $node->removeAttribute('data-fragmentref');
        }

        return $ref;
    }

    /**
     * find any namespaces in an xpath
     *
     * @param string $xpath
     *
     * @return array
     */
    private function _findNamespacePrefixes($xpath) {
        if (preg_match_all('/(?P<prefix>[a-z_][a-z_0-9\-\.]*):[^"\/]/i', $xpath, $matches)) {
            return array_unique($matches['prefix']);
        }
        return array();
    }


}

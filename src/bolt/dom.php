<?php

namespace bolt;
use \b;

use Symfony\Component\CssSelector\CssSelector;

use \DOMDocument;
use \DOMXPath;


/**
 * DOM HTML parser
 */
class dom implements \ArrayAccess {

    /**
     * @var DOMDocument
     */
    protected $_doc;

    /**
     * @var array
     */
    private $_ref = [];

    /**
     * @var string
     */
    private $_guid;


    /**
     * Constructor
     *
     * @param string $html starting HTML
     * @param string $charset
     */
    public function __construct($html=false, $charset='UTF-8') {

        $this->_doc = new DOMDocument('1.0', $charset);
        $this->_doc->validateOnParse = true;
        $this->_doc->preserveWhiteSpace = false;
        $this->_doc->resolveExternals = false;
        $this->_doc->formatOutput = b::env() === 'dev';

        if ($html) {
            @$this->_doc->loadHTML($html);
        }

        $this->_guid = b::guid('domref');

    }


    /**
     * return the global unique id of this dom object
     *
     * @return string
     */
    public function guid() {
        return $this->_guid;
    }


    /**
     * return the root document
     *
     * @return DOMDocument
     */
    public function doc() {
        return $this->_doc;
    }


    /**
     * add an element refrance to this DOM
     *
     * @param bolt\dom\element $el
     *
     * @return self
     */
    public function addRef(dom\element $el) {
        $this->_ref[$el->guid()] = $el;
        return $this;
    }


    /**
     * import a node into this document
     *
     * @param mixed $node
     * @param bool $deep
     *
     * @return
     */
    public function importNode($node, $deep=false) {
        return $this->_doc->importNode($node, $deep);
    }


    /**
     * query the DOM (using CSS selector) to find a node
     *
     * @param string $query CSS selector
     * @param bool $returnRef return a refrance to the elemet
     *
     * @return bolt\dom\nodeList
     */
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


    /**
     * find any namespaces in an xpath
     *
     * @param string $xpath
     *
     * @return array
     */
    private function findNamespacePrefixes($xpath) {
        if (preg_match_all('/(?P<prefix>[a-z_][a-z_0-9\-\.]*):[^"\/]/i', $xpath, $matches)) {
            return array_unique($matches['prefix']);
        }
        return array();
    }


    /**
     * return the HTML of this document
     *
     * @return string
     */
    public function html() {
        $ref = clone $this->_doc;


        $xpath = new DOMXPath($ref);

        foreach ($xpath->query(CssSelector::toXPath('*[data-domref]')) as $node) {
            $node->removeAttribute('data-domref');
        }

        return $ref->saveHTML();
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


    /**
     * remove a node using a CSS selector
     *
     * @param string $name CSS selector
     *
     * @return void
     */
    public function offsetUnset($name) {
        $el = $this->find($name);
        $el->parentNode->removeChild($el);
    }


    /**
     * return a string rep of DOM
     *
     * @return string
     */
    public function __toString() {
        return $this->html();
    }


    /**
     * create a new element
     *
     * @param string $name name of tag
     * @param mixed $value
     * @param array $attr attributes
     *
     * @return bolt\dom\element
     */
    public function create($tag, $value="", $attr=[]) {
        $el = $this->_doc->createElement($tag, $value);
        $_ = new dom\element($this, $el);
        $_->attr($attr);
        return $_;
    }


}
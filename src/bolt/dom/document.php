<?php

namespace bolt\dom;
use \b;

use Symfony\Component\CssSelector\CssSelector;

use DOMImplementation,
    DOMDocument,
    DOMDocumentType,
    DOMXPath;

class document extends DOMDocument implements \ArrayAccess {

    public $guid = false;

    private $_refrances = [];

    /**
     * Constructor
     *
     * @param string $charset 'UTF-8'
     */
    public function __construct($charset = 'UTF-8') {
        parent::__construct($charset);

        $this->validateOnParse = false;
        $this->preserveWhiteSpace = false;
        $this->resolveExternals = false;
        $this->substituteEntities = false;
        $this->formatOutput = false;
        $this->strictErrorChecking = false;

        $this->guid = b::guid("domref");

    }

    /**
     * get or set html document HTML
     *
     * @param  string|null $html
     *
     * @return mixed
     */
    public function html($html = null) {
        if ($html !== null) {
            @$this->loadHTML($html, LIBXML_COMPACT + LIBXML_NOERROR + LIBXML_NOWARNING + LIBXML_NOXMLDECL);
            return $this;
        }
        else {
            return $this->saveHTML();
        }
    }


    /**
     * output the html of the document
     *
     * @return string
     */
    public function saveHTML($element = null) {
        if (is_a($element, 'bolt\dom\element')) {
            $element = $element->element;
        }
        if (is_a($element, 'DOMElement')) {
            return parent::saveHTML($this->_cleanElement($element));
        }

        $this->_cleanElement();
        return str_replace('<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">', '<!DOCTYPE html>', parent::saveHTML());
    }

    public function getHTML($element = null) {
        if ($element) {
            return $this->saveHTML($element);
        }
        else {
            return $this->html();
        }
    }

    /**
     * clean the document of any domref attributes
     *
     * @return [type] [description]
     */
    private function _cleanElement($el = null) {
        $xpath = new DOMXPath($this);

        if ($el) {
            $el->removeAttribute('data-domref');
        }

        foreach ($xpath->query('//*[@*]', $el) as $node) {
            $node->removeAttribute('data-domref');
        }

        return $el;
    }


    /**
     * find an element with CSS selector
     *
     * @param  string $selector
     *
     * @return bolt\dom\collection
     */
    public function find($selector) {
        $xpath = CssSelector::toXPath(trim($selector));
        $prefixes = $this->_findNamespacePrefixes($xpath);
        $x = new DOMXPath($this);

        $collection = new collection($this);

        foreach ($x->query($xpath) as $node) {
            $collection->push($node);
        }

        return $collection;
    }


    /**
     * find a selector
     * @param  string $selector
     *
     * @return boolean
     */
    public function offsetExists($selector) {
        return count($this->find($selector)) > 0;
    }


    /**
     * get a offset
     *
     * @see  self::find
     * @param  string $selector
     *
     * @return bolt\dom\collection
     */
    public function offsetGet($selector) {
        return $this->find($selector);
    }


    /**
     * set the contents of an element
     *
     * @see  self::html
     * @param  string $selector
     * @param  mixed $value
     *
     * @return self
     */
    public function offsetSet($selector, $value) {
        $el = $this->find($selector)->first();

        if (!$el) {return false;}

        $el->html($value);

        return $this;
    }


    /**
     * remove a node if it exists
     *
     * @param  strong $selector
     *
     * @return mixed
     */
    public function offsetUnset($selector) {
        return $this->find($selector)->remove();
    }


    /**
     * convert document to a string
     *
     * @see  self::html
     *
     * @return string
     */
    public function __toString() {
        return $this->saveHTML();
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
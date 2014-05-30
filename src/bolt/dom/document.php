<?php

namespace bolt\dom;
use \b;

use Symfony\Component\CssSelector\CssSelector;

use DOMImplementation,
    DOMDocument,
    DOMDocumentType,
    DOMXPath;

class document extends DOMDocument implements \ArrayAccess {
    use traits\queryable;

    public $guid = false;

    private $_refrances = [];

    /**
     * Constructor
     *
     * @param string $charset 'UTF-8'
     */
    public function __construct($charset = 'UTF-8', $html = null) {
        parent::__construct($charset);

        $this->validateOnParse = false;
        $this->preserveWhiteSpace = false;
        $this->resolveExternals = false;
        $this->substituteEntities = false;
        $this->formatOutput = false;
        $this->strictErrorChecking = false;

        $this->guid = b::guid("domref");

        if ($html) {
            $this->html($html);
        }

    }

    public function destroyChild(element $el) {
        if (isset($this->_refrances[$el->guid])) {
            unset($this->_refrances[$el->guid]);
        }
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
            return $this->setHTML($html);
        }
        else {
            return $this->getHTML();
        }
    }


    /**
     * create an element child for this document
     * 
     * @param  string $name 
     * @param  string $value
     * @param  array $attr 
     * 
     * @return bolt\dom\element
     */
    public function createElement($name, $value = null, $attr = []) {
        return new element($name, $value, $attr, $this);
    }


    /**
     * create a native dom node
     * 
     * @param  string $name 
     * @param  string $value
     * 
     * @return DOMElement
     */
    public function createElementNative($name, $value) {
        return parent::createElement($name, $value);
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
            return parent::saveHTML(self::cleanElement($element, true));
        }        

        if ($this->documentElement) {
            self::cleanElement($this->documentElement);
        }

        return str_replace(
            '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">', 
            '<!DOCTYPE html>', 
            parent::saveHTML()
        );
    }


    /**
     * get HTML for the document or given element
     * 
     * @param  DOMNode|bolt\dom\element $element
     * 
     * @return string
     */
    public function getHTML($element = null) {    
        return $this->saveHTML($element);
    }


    /**
     * set HTML for the document
     * 
     * @param string $html
     *
     * @return self
     */
    public function setHTML($html) {
        @$this->loadHTML($html, LIBXML_COMPACT + LIBXML_NOERROR + LIBXML_NOWARNING + LIBXML_NOXMLDECL);
        return $this;
    }


    /**
     * import an element into this document
     * 
     * @param  DOMNode|bolt\dom\node $node
     * @param  boolean $deep
     * 
     * @return mixed
     */
    public function import($node, $deep = true) {
        if (is_a($node, 'bolt\dom\element')) {
            $this->_refrances[$node->guid] = $node;
            $node->ownerDocument = $this;
            $node->element = $this->importNode($node->element, $deep);
            return $node;
        }            
        return $this->importNode($node, $deep);
    }


    /**
     * clean the document of any domref attributes
     *
     * @return mixed
     */
    public static function cleanElement($el = null, $useClone = false) {
        $el = is_a($el, 'bolt\dom\element') ? $el->element : $el;

        if ($useClone) {
            $el = clone $el;
        }

        $xpath = new DOMXPath($el->ownerDocument);

        if ($el) {
            $el->removeAttribute('data-domref');
        }

        foreach ($xpath->query('*[@*]', $el) as $node) {
            $node->removeAttribute('data-domref');
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
    public function find($selector, element $element = null) {
        $xpath = CssSelector::toXPath(trim($selector));
        $prefixes = $this->_findNamespacePrefixes($xpath);
        $x = new DOMXPath($this);

        $el = $element ? $element->element : null;

        $collection = new collection($this);

        foreach ($x->query($xpath, $el) as $node) {
            if ($node->hasAttribute('data-domref') && ($ref = $node->getAttribute('data-domref')) && isset($this->_refrances[$ref])) {
                $node = $this->_refrances[$ref];
            }
            else {
                $node = new element($node, null, null, $this);
            }
            $collection->push($node);
        }

        return $collection;
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
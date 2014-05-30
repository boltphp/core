<?php

namespace bolt\dom;
use \b;

use Symfony\Component\CssSelector\CssSelector;

use DOMImplementation,
    DOMDocument,
    DOMDocumentType,
    DOMXPath;

use HTML5;

class document extends DOMDocument implements \ArrayAccess {
    use traits\queryable;

    private $_refid;

    private $_refrances = [];

    /**
     * Constructor
     *
     * @param string $charset 'UTF-8'
     */
    public function __construct($charset = 'UTF-8', $html = null) {
        parent::__construct('1.0', $charset);

        $this->validateOnParse = false;
        $this->preserveWhiteSpace = false;
        $this->resolveExternals = false;
        $this->substituteEntities = false;
        $this->formatOutput = false;
        $this->strictErrorChecking = false;

        $this->_refid = b::guid("docref");

        if ($html) {
            $this->html($html);
        }

    }

    public function __get($name) {
        switch($name) {
            case 'refid':
                return $this->_refid;
        };
        return null;
    }

    public function destroyChild(element $el) {
        if (isset($this->_refrances[$el->refid])) {
            unset($this->_refrances[$el->refid]);
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
     * alias for createElement
     *
     * @see  self::createElement
     *
     * @return  bolt\dom\element
     */
    public function create() {
        return call_user_func_array([$this, 'createElement'], func_get_args());
    }


    /**
     * create a native dom node
     *
     * @param  string $name
     * @param  string $value
     *
     * @return DOMElement
     */
    public function createElementNative($name, $value = null) {
        return parent::createElement($name, $value);
    }


    /**
     * append something to this document
     *
     */
    public function append($child) {
        if (is_a($child, 'bolt\dom\element')) {
            $this->import($child);
            $child = $child->element;
        }
        $this->appendChild($child);
        return $this;
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

        return "<!DOCTYPE html>\n".parent::saveHTML();

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
        $dom = HTML5::loadHTML($html);
        parent::appendChild(parent::importNode($dom->documentElement, true));

        // var_dump($dom); die;

        // libxml_use_internal_errors(true);
        // @$this->loadHTML($html, LIBXML_COMPACT + LIBXML_NOERROR + LIBXML_NOWARNING + LIBXML_NOXMLDECL + LIBXML_NONET);
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
            if ($node->ownerDocument->refid === $this->refid) {
                return $node; // already a child of this document
            }
            $this->_refrances[$node->refid] = $node;
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
    public function find($selector, $element = null) {
        $xpath = CssSelector::toXPath(trim($selector));
        $prefixes = $this->_findNamespacePrefixes($xpath);

        $el = null;

        if (is_a($element, 'bolt\dom\element')) {
            $el = $element->element;
        }
        else if (is_a($element, 'bolt\dom\document')) {
            $el = $element->documentElement;
        }

        return $this->xpath($xpath, $el);

    }

    public function xpath($xpath, $el = null) {
        $x = new DOMXPath($this);
        $collection = new collection($this);
        foreach ($x->query($xpath, $el) as $node) {

            if (is_a($node, 'DOMElement') && $node->hasAttribute('data-domref') && ($ref = $node->getAttribute('data-domref')) && isset($this->_refrances[$ref])) {
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
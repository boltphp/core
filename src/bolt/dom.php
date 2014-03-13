<?php

namespace bolt;
use \b;

use Symfony\Component\CssSelector\CssSelector;

use \DOMDocument;
use \DOMXPath;

class dom implements \ArrayAccess {

    private $_doc;

    private $_ref = [];

    private $_guid;

    public function __construct($html = "", $charset = 'UTF-8') {
        $this->_doc = new DOMDocument('1.0', $charset);
        $this->_doc->validateOnParse = false;
        $this->_doc->preserveWhiteSpace = false;
        $this->_doc->resolveExternals = false;
        $this->_doc->formatOutput = b::env() === 'dev';

        $this->_guid = b::guid('domref');


        $this->html($html);
    }


    public function guid() {
        return $this->_guid;
    }

    public function doc() {
        return $this->_doc;
    }

    public function html($html = null) {
        if ($html) {
            $this->_doc->loadHTML($html);
            return $this;
        }
        else {
            return $this->cleanDomNodes()->saveHTML();
        }
    }

    public function addRef(dom\element $el) {
        $this->_ref[$el->guid()] = $el;
        return $this;
    }

    public function import($what, $deep = false) {
        if (is_a($what, 'bolt\dom\element')) {
            $what = $what->node();
        }

        return $this->_doc->importNode($what, $deep);
    }

    public function append($what) {
        if (is_a($what, 'bolt\dom\element')) {
            $this->_doc->documentElement->appendChild($what->node());
        }

        return $this;
    }

    public function create($tag, $value = null, $attr = []) {
        $el = $this->_doc->createElement($tag, html_entity_decode($value, ENT_QUOTES, 'utf-8'));
        $_ = new dom\element($this, $el);
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
                $nl->push(new dom\element($this, $node));
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


// /**
//  * DOM HTML parser
//  */
// class dom implements \ArrayAccess {

//     /**
//      * @var DOMDocument
//      */
//     protected $_doc;

//     /**
//      * @var array
//      */
//     private $_ref = [];

//     /**
//      * @var string
//      */
//     private $_guid;


//     /**
//      * Constructor
//      *
//      * @param string $html starting HTML
//      * @param string $charset
//      */
//     public function __construct($html=false, $charset='UTF-8') {

//         $this->_doc = new DOMDocument('1.0', $charset);
//         $this->_doc->validateOnParse = false;
//         $this->_doc->preserveWhiteSpace = false;
//         $this->_doc->resolveExternals = false;
//         $this->_doc->formatOutput = b::env() === 'dev';

//         if ($html) {
//             @$this->_doc->loadHTML($html, LIBXML_HTML_NOIMPLIED & LIBXML_NOERROR & LIBXML_ERR_NONE);
//         }

//         $this->_guid = b::guid('domref');

//     }


//     /**
//      * return the global unique id of this dom object
//      *
//      * @return string
//      */
//     public function guid() {
//         return $this->_guid;
//     }


//     /**
//      * return the root document
//      *
//      * @return DOMDocument
//      */
//     public function doc() {
//         return $this->_doc;
//     }


//     /**
//      * add an element refrance to this DOM
//      *
//      * @param bolt\dom\element $el
//      *
//      * @return self
//      */
//     public function addRef($el) {
//         $this->_ref[$el->guid()] = $el;
//         return $this;
//     }


//     /**
//      * import a node into this document
//      *
//      * @param mixed $node
//      * @param bool $deep
//      *
//      * @return
//      */
//     public function importNode($node, $deep=false) {
//         if (is_a($node, 'bolt\dom\element')) {
//             $node = $node->node();
//         }
//         return $this->_doc->importNode($node, $deep);
//     }


//     /**
//      * query the DOM (using CSS selector) to find a node
//      *
//      * @param string $query CSS selector
//      * @param bool $returnRef return a refrance to the elemet
//      *
//      * @return bolt\dom\nodeList
//      */
//     public function find($query, $returnRef=true) {
//         $xpath = CssSelector::toXPath($query);
//         $prefixes = $this->findNamespacePrefixes($xpath);

//         $x = new DOMXPath($this->_doc);

//         $nl = new dom\nodeList($this);

//         foreach ($x->query($xpath) as $node) {
//             $dr = $node->getAttribute('data-domref');

//             if ($returnRef && $dr && array_key_exists($dr, $this->_ref)) {
//                 $nl->attach($this->_ref[$dr]);
//             }
//             else {
//                 $nl->attach(new dom\element($this, $node));
//             }
//         }

//         return $nl;
//     }


//     /**
//      * find any namespaces in an xpath
//      *
//      * @param string $xpath
//      *
//      * @return array
//      */
//     private function findNamespacePrefixes($xpath) {
//         if (preg_match_all('/(?P<prefix>[a-z_][a-z_0-9\-\.]*):[^"\/]/i', $xpath, $matches)) {
//             return array_unique($matches['prefix']);
//         }
//         return array();
//     }


//     /**
//      * return the HTML of this document
//      *
//      * @return string
//      */
//     public function html() {
//         $ref = clone $this->_doc;


//         $xpath = new DOMXPath($ref);

//         foreach ($xpath->query(CssSelector::toXPath('*[data-domref]')) as $node) {
//             $node->removeAttribute('data-domref');
//         }
//         foreach ($xpath->query(CssSelector::toXPath('*[data-fragmentref]')) as $node) {
//             $node->removeAttribute('data-fragmentref');
//         }

//         //

//         return $ref->saveHTML();
//     }


//     /**
//      * append
//      */
//      public function append($what){
//         $this->_doc->documentElement->appendChild($what->node());
//      }


//     /**
//      * check if a node exists using a CSS selector
//      *
//      * @param string $name CSS selector
//      *
//      * @return bool
//      */
//     public function offsetExists($name) {
//         return count($this->find($name)) > 0;
//     }


//     /**
//      * get a node using a CSS selector
//      *
//      * @param string $name css selector
//      *
//      * @return mixed
//      */
//     public function offsetGet($name) {
//         return $this->find($name);
//     }


//     /**
//      * create a node or replace an exists node
//      *
//      * @param string $name CSS selector
//      * @param mixed $value
//      *
//      * @return self
//      */
//     public function offsetSet($name, $value) {
//         $el = $this->find($name)->first();

//         if (!$el) {
//             return false;
//         }

//         if ($el->hasChildNodes()) {
//             foreach ($el->childNodes as $child) {
//                 $child->parentNode->removeChild($child);
//             }
//         }

//         $value = html_entity_decode($value, ENT_QUOTES, 'utf-8');

//         if (stripos($value, '<') !== false && stripos($value, '>') !== false) {
//             $guid = b::guid("_x_dom");

//             $_ = new \DOMDocument();
//             @$_->loadHTML("<div id='{$guid}'>".$value."</div>");

//             $children = $_->getElementById($guid)->childNodes;

//             foreach ($children as $child) {
//                 $el->appendChild($this->_doc->importNode($child, true));
//             }

//         }
//         else {
//             $el->appendChild(new \DOMText($value));
//         }

//         return $this;
//     }


//     /**
//      * remove a node using a CSS selector
//      *
//      * @param string $name CSS selector
//      *
//      * @return void
//      */
//     public function offsetUnset($name) {
//         $el = $this->find($name);
//         $el->parentNode->removeChild($el);
//     }


//     /**
//      * return a string rep of DOM
//      *
//      * @return string
//      */
//     public function __toString() {
//         return $this->html();
//     }


//     /**
//      * create a new element
//      *
//      * @param string $name name of tag
//      * @param mixed $value
//      * @param array $attr attributes
//      *
//      * @return bolt\dom\element
//      */
//     public function create($tag, $value="", $attr=[]) {
//         $el = $this->_doc->createElement($tag, html_entity_decode($value, ENT_QUOTES, 'utf-8'));
//         $_ = new dom\element($this, $el);
//         $_->attr($attr);
//         return $_;
//     }


// }
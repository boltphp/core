<?php

namespace bolt\dom;
use \b;

use DOMElement,
    DOMCdataSection,
    DOMText,
    DOMAttr;

class element implements \ArrayAccess {
    use traits\queryable;

    /**
     * document that ownes this element
     * 
     * @var bolt\dom\document
     */
    public $ownerDocument;

    /**
     * refrance to DOMElement
     * 
     * @var DOMElement
     */
    public $element;

    /**
     * unique id for this element
     * 
     * @var string
     */
    private $_guid;

    /**
     * name of the root tag
     * 
     * @var string
     */
    public $tagName = 'div';

    /**
     * default value of the root tag
     * 
     * @var string
     */
    public $value;

    /**
     * default attributes
     * 
     * @var array
     */
    public $attr = [];

    /**
     * global attributes
     *
     * @var array
     */
    private $_globalAttr = [
        'accesskey' => null,
        'class' => null,
        'contenteditable' => null,
        'contextmenu' => null,
        'dir' => null,
        'draggable' => null,
        'dropzone' => null,
        'hidden' => null,
        'id' => null,
        'itemid' => null,
        'itemprop' => null,
        'itemref' => null,
        'itemscope' => null,
        'itemtype' => null,
        'lang' => null,
        'spellcheck' => null,
        'style' => null,
        'tabindex' => null,
        'title' => null
    ];


    /**
     * Constructr
     * 
     * @param string|DOMElement $tag     
     * @param mixed $value   
     * @param array $attr    
     * @param bolt\dom\document $document
     * 
     */
    final public function __construct($tag = null, $value = null, $attr = null, document $document = null) {
        $this->ownerDocument = $document ?: new document();

        $this->attr = $attr ? array_merge($this->_globalAttr, $this->attr, $attr) : $this->attr;
        $value = $value ?: $this->value;

        if (is_a($tag, 'DOMElement')) {
            $this->element = $tag;
            $this->tagName = $tag->tagName;
        }
        else {
            $this->tagName = $tag ?: $this->tagName;
            $this->element = $this->ownerDocument->createElementNative($this->tagName, "");
        }

        $this->_guid = b::guid("noderef");
        $this->attr('data-domref', $this->guid);

        $this->ownerDocument->import($this);

        if (is_array($attr)) {
            $this->attr($attr);
        }
        if ($value) {
            $this->html($value);
        }

        $this->init();

    }


    /**
     * Destructor
     */
    public function __destruct() {
        $this->ownerDocument->destroyChild($this);
    }


    /**
     * called after the element has been created
     * 
     * @return void
     */
    public function init() {}


    /**
     * magic get method
     * 
     * @param  string $name
     * 
     * @return mixed
     */
    public function __get($name) {
        switch ($name) {
            case 'guid':
                return $this->_guid;

            case 'outerHTML':
                return $this->ownerDocument->getHTML($this);

            case 'innerHTML':
            case 'html':
                return $this->html();

        };

        if (property_exists($this->element, $name)) {
            return $this->element->$name;
        }

        return $this->attr($name);
    }


    /**
     * set a value to an attribute
     *
     * @see  self::attr
     * @param string $name 
     * @param mixed $value
     *
     * @return self
     */
    public function __set($name, $value) {
        return $this->attr($name, $value);
    }


    /**
     * set/get default attribute or passthrough to 
     * self::$element if method exists
     * 
     * @param  string $name 
     * @param  array $args 
     * 
     * @return mixed       
     */
    public function __call($name, $args) {
        if (($attr = strtolower($name)) && array_key_exists($attr, $this->attr)) {
            array_unshift($args, $attr);
            return call_user_func_array([$this, 'attr'], $args);
        }        
        else if (method_exists($this->element, $name)) {
            return call_user_func_array([$this->element, $name], $args);
        }
        return null;
    }


    /**
     * create a new element in this document
     * 
     * @param  string|DOMElement $tag  
     * @param  mixed $value
     * @param  array $attr 
     * 
     * @return bolt\dom\element
     */
    public function create($tag, $value = null, $attr = []) {
        return new static($tag, $value, $attr, $this->ownerDocument);
    }


    /**
     * append a child node
     *
     * @see  self::appendChild
     * @param  mixed $child
     * 
     * @return self
     */
    public function append($child) {
        $r = $this->appendChild($child);
        return $this;
    }


    /**
     * append a child node
     * 
     * @param  mixed $child 
     * 
     * @return bolt\dom\element
     */
    public function appendChild($child) {
        if (is_a($child, 'DOMElement')) {
            $child = new static($child, null, null, $this->ownerDocument);
        }        
        if (is_a($child, 'bolt\dom\element') && $child->ownerDocument->guid != $this->guid) {
            $this->ownerDocument->import($child);
        }        
        $this->element->appendChild($child->element);
        return $child;
    }


    /**
     * remove this node
     * 
     * @return self
     */
    public function remove() {        
        if ($this->parentNode) {            
            $this->parentNode->removeChild($this->element);
        }    
        return $this;
    }


    /**
     * return all children
     * 
     * @return bolt\dom\collection
     */
    public function children() {
        return count($this->childNodes) == 0 ? new collection($this->ownerDocument) : $this->find("*");
    }


    /**
     * remove all child nodes
     * 
     * @return self
     */
    public function clear() {
        $this->children()->each('remove');
        return $this;
    }


    /**
     * get or set the innerHTML of the element
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
     * set element HTML
     *
     * @param string html
     *
     * @return self
     */
    public function setHTML($html) {
        $this->clear();
        $html = html_entity_decode($html, ENT_NOQUOTES, 'utf-8');
        if ($this->tagName == 'script') {
            $this->element->appendChild(new \DOMCdataSection($html));            
        }        
        else if (stripos($html, '<') !== false || stripos($html, '>') !== false) {
            $fragment = new fragment($html);
            foreach ($fragment->children() as $node) {                
                $this->element->appendChild(
                    $this->ownerDocument->importNode($node->element, true)
                );
            }
        }
        else {
            $this->element->appendChild(new DOMText($html));
        }
        return $this;
    }


    /**
     * get element HTML
     *
     * @return string
     */
    public function getHTML() {
        $html = "";
        foreach ($this->element->childNodes as $node) {
            switch ($node->nodeType) {
                case XML_TEXT_NODE:
                    $html .= $node->nodeValue; break;
                default:
                    $html .= $this->ownerDocument->saveHTML($node);
            };                
        }
        return $html;   
    }


    /**
     * query for a child element
     * 
     * @param  string $selector
     * @return bolt\dom\collection
     */
    public function find($selector) {        
        return $this->ownerDocument->find($selector, $this);
    }


    /**
     * get or set an attribute on the element
     * 
     * @param  string|array $name
     * @param  string|null $value 
     * 
     * @return mixed
     */
    public function attr($name, $value = null) {
        if (is_array($name)) {
            foreach ($name as $n => $v) {
                $this->attr($n, $v);                                    
            }
            return $this;
        }

        if ($value !== null) {            
            if (is_numeric($name) || $value === true) {
                $this->element->appendChild(new DOMAttr($value === true ? $name : $value));
            }
            else {
                $this->setAttribute($name, html_entity_decode($value, ENT_QUOTES, 'utf-8'));
            }            
            return $this;
        }
        else {
            return $this->getAttribute($name);
        }
    }


    /**
     * insert a node before this node in
     * the self::$owernDocument
     * 
     * @param  mixed $node
     * 
     * @return self
     */
    public function insertBefore($node) {
        if (is_a($node, 'bolt\dom\element') && $node->ownerDocument->guid != $this->ownerDocument->guid) {
            $this->ownerDocument->import($node);
            $node = $node->element;
        }
        $this->parentNode->insertBefore($node, $this->element);
        return $this;
    }


    /**
     * add a class to the class attribute
     * 
     * @param string|array $class
     *
     * @return self
     */
    public function addClass($class) {
        $cur = explode(" ", $this->attr('class'));
        $add = is_string($class) ? explode(" ", $class) : $class;
        $this->attr('class', trim(implode(" ", array_merge($cur, $add))));
        return $this;
    }


    /**
     * set a property for the style attribute
     * 
     * @param string|array $prop 
     * @param string $value
     *
     * @return self
     */
    public function setStyle($prop, $value=null) {
        if (is_array($prop)) {
            foreach($prop as $p => $v) {
                $this->setStyle($p, $v);
            }
            return $this;
        }
        $cur = explode(';', $this->attr('style'));

        $props = [];

        foreach($cur as $part) {
            if (empty($part)) {continue;}
            list($p, $v) = explode(':', $part);
            $props[trim($p)] = trim($v);
        }

        switch($prop) {
            case 'background-image':
                if (substr($value, 0, 3) != 'url') {
                    $value = "url({$value})";
                }
                break;

        };

        $props[trim($prop)] = trim($value);
        $_ = [];

        foreach ($props as $prop => $value) {
            $_[] = implode(':', [$prop, $value]);
        }

        $this->attr('style', implode(';', $_));

        return $this;
    }
}
<?php

namespace bolt\dom;
use \b;

use DOMElement,
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
        'accesskey',
        'class',
        'contenteditable',
        'contextmenu',
        'dir',
        'draggable',
        'dropzone',
        'hidden',
        'id',
        'itemid',
        'itemprop',
        'itemref',
        'itemscope',
        'itemtype',
        'lang',
        'spellcheck',
        'style',
        'tabindex',
        'title'
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
        if (substr($name, 0, 3) == 'get' && ($attr = strtolower(substr($name, 3))) && array_key_exists($attr, $this->attr)) {
            return $this->attr($attr);
        }
        else if (substr($name, 0, 3) == 'set' && ($attr = strtolower(substr($name, 3))) && array_key_exists($attr, $this->attr)) {
            return $this->attr($attr, $args[0]);
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
        else {
            $this->element->nodeValue = "";
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
            $this->clear();
            $fragment = new fragment($html);
            foreach ($fragment->children() as $node) {                
                $this->element->appendChild(
                    $this->ownerDocument->importNode($node->element, true)
                );
            }
            return $this;
        }
        else {
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
                if (is_numeric($n)) {
                    $this->appendChild(new DOMAttr($v));
                }
                else {
                    $this->setAttribute($n, html_entity_decode($v, ENT_QUOTES, 'utf-8'));
                }
            }
            return $this;
        }

        if ($value !== null) {
            $this->setAttribute($name, $value);
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


}
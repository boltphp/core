<?php

namespace bolt\dom;
use \b;

use Symfony\Component\CssSelector\CssSelector;

class element implements \ArrayAccess {

    private $_guid;
    private $_dom;
    private $_node;

    public function __construct($dom, $node=false) {
        $this->_dom = $dom;
        $this->_node = $node;
        $this->_guid = b::guid("domref");
        $this->attr('data-domref', $this->_guid);
    }

    public function reset($dom, $node)  {
        $this->_dom = $dom;
        $this->_node = $node;
    }

    public function create($tag, $value="", $attr=[]) {
        $el = $this->_dom->doc()->createElement($tag, $value);
        $_ = new element($this->_dom, $el);
        $_->attr($attr);
        return $_;
    }

    public function guid() {
        return $this->_guid;
    }

    public function createAndAppend() {
        $el = call_user_func_array([$this, 'create'], func_get_args());
        $this->append($el);
        return $el;
    }

    public function node() {
        return $this->_node;
    }

    public function __get($name) {
        return $this->_node->$name;
    }

    public function __call($name, $args) {
        if (method_exists($this->_node, $name)) {
            return call_user_func_array([$this->_node, $name], $args);
        }
    }

    public function html() {
        if (is_a($this->_node, '\DOMText') OR !$this->_node->hasChildNodes()) {
            return $this->_node->nodeValue;
        }

        $ref = clone $this->_dom->doc();

        $xpath = new \DOMXPath($ref);

        $el = $xpath->query(CssSelector::toXPath('[data-domref="'.$this->_guid.'"]'))->item(0);

        if (!$el) {
            return $this->_node->nodeValue;
        }

        foreach ($xpath->query(CssSelector::toXPath('*[data-domref]')) as $node) {
            $node->removeAttribute('data-domref');
        }
        foreach ($xpath->query(CssSelector::toXPath('*[data-fragmentref]')) as $node) {
            $node->removeAttribute('data-fragmentref');
        }


        return $ref->saveHTML($el);
    }

    public function append($what) {


        if (is_array($what)) {
            foreach ($what as $item) {
                $this->append($item);
            }
        }
        else if (is_a($what, '\bolt\dom\fragment')) {
            $this->_dom->addRef($what);
            $newNode = $this->_node->appendChild($this->_dom->import($what->root(), true));
            $what->reset($this->_dom, $newNode);
        }
        else if (is_a($what, '\bolt\dom\element')) {
            $this->_dom->addRef($what);
            $newNode = $this->_node->appendChild($this->_dom->import($what, true));
            $what->reset($this->_dom, $newNode);
        }
        else if (is_a($what, '\bolt\dom\nodeList')) {
            foreach ($what as $node) {
                $this->append($node);
            }
        }
        else {
            throw new \Exception("Unable to append object of type ".get_class($what));
        }

        return $this;
    }

    public function attr($name, $value=null) {
        if (is_array($name)) {
            foreach ($name as $n => $v) {
                if (is_numeric($n)) {
                    $this->_node->appendChild(new \DOMAttr($v));
                }
                else {
                    $this->_node->setAttribute($n, $v);
                }
            }
            return $this;
        }
        if (is_a($this->_node, 'DOMText')) {
            return;
        }

        if ($value !== null) {
            $this->_node->setAttribute($name, $value);
            return $this;
        }
        else {
            return $this->_node->getAttribute($name);
        }
    }

    public function addClass($class) {
        $cur = explode(" ", $this->attr('class'));
        $add = is_string($class) ? explode(" ", $class) : $class;
        $this->attr('class', trim(implode(" ", array_merge($cur, $add))));
        return $this;
    }

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

    public function children() {
        $nl = new \bolt\dom\nodeList($this->_dom);

        foreach ($this->_node->childNodes as $node) {
            if (trim($node->nodeValue) == "") {continue;}
            $nl->attach(new element($this->_dom, $node));
        }


        return $nl;
    }

    public function remove(){
        $this->_node->parentNode->removeChild($this->_node);
    }

    public function offsetExists($name) {
        return $this->_dom->offsetExists("[data-domref='{$this->_guid}'] $name");
    }

    public function offsetGet($name) {
        return $this->_dom->offsetGet("[data-domref='{$this->_guid}'] $name");
    }

    public function offsetSet($name, $value) {
        return $this->_dom->offsetSet("[data-domref='{$this->_guid}'] $name", $value);
    }

    public function offsetUnset($name) {
        return $this->_dom->offsetUnset("[data-domref='{$this->_guid}'] $name");
    }

}
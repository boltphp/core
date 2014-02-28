<?php

namespace bolt\render;
use \b;

use \DOMDocument, \DOMAttr, \DOMCDATASection;

/**
 * render a dom string
 */
class xml extends base {


    /**
     * compile the dom doc to serializeable object
     */
    public function compile() {


    }


    /**
     * render an html string into a dom doc
     *
     * @param string array $data
     * @param array $vars
     *
     * @return xmlGenerator
     */
    public function render($data, $vars = []) {
        $xml = new xmlGenerator($data);
        return $xml;
    }


}


class xmlGenerator {

    private $_data = [];

    public function __construct($data) {

        // new dom document
        $this->dom = new DOMDocument('1.0', 'utf-8');
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = true;

        $this->_data = $data;

    }

    public function getData() {
        return $this->_data;
    }

    public function render() {
        array_walk($this->_data, array($this,'_mapItemToDom'), $this->dom);
        return trim($this->dom->saveXML());
    }

    public function __toString() {
        return trim((string)$this->render());
    }

    /**
     * PRIVATE: map the data array to xml
     * @method  _mapItemToDom
     * @param   {variable}      item
     * @param   {string}        key
     * @param   {ref:object}    root node
     * @return  {variable}
     */
    private function _mapItemToDom($item,$key,&$root) {


        // attribute
        if ( is_array($item) AND $key === '@' ) {

            // foreach set as attribute
            foreach ( $item as $k => $v ) {
                $root->setAttributeNode(new DOMAttr($k,$v));
            }

        }

        // items
        else if ( is_array($item) ) {

            // is it an int
            if ( is_int($key) AND array_key_exists('_item',$item) ) {
                $key = $item['_item'];
            }

            // create el
            $el = $this->dom->createElement($key);

            // value
            if (array_key_exists('_value', $item)) {

                // value
                $el->nodeValue = $item['_value'];

                // foreach set as attribute
                if (array_key_exists('@', $item)) {
                    foreach ( $item['@'] as $k => $v ) {
                        $el->setAttributeNode(new DOMAttr($k,$v));
                    }
                }

                // append to root
                $root->appendChild($el);

            }
            else {

                // create new el
                $el = $this->dom->createElement($key);

                // append to dom
                $root->appendChild($el);

                // walk it
                array_walk($item,array($this,'_mapItemToDom'),$el);

            }

        }

        // not an item
        else if ( $key != '_item' ) {

            // use cdata
            $html = false;

            // check key for astric
            if ( $key{0} == '*' ) {
                $html = 'true';
                $key = substr($key,1);
            }

            // create new el
            if ( $html ) {

                // create el
                $el = $this->dom->createElement($key);

                // append cdata section
                $el->appendChild(new DOMCDATASection($item));

            }
            else {

                // is null
                if ( is_null($item) ) {
                    $item = "";
                }

                // el
                $el = $this->dom->createElement($key, htmlentities($item,ENT_QUOTES,'UTF-8',false));

            }

            // append to root
            $root->appendChild($el);

        }


    }

}

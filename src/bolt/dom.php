<?php

namespace bolt;
use \b;


class dom implements plugin\singleton {

    /**
     * create a document
     *
     * @param string charset
     * @param string html
     *
     * @return bolt\dom\document
     */
    public function document($charset = null, $html = null) {
        return self::createDocument($charset, $html);
    }


    /**
     * create a fragment
     *
     * @param  string charset
     * @param  string html
     *
     * @return bolt\dom\fragment
     */
    public function fragment($charset = null, $html = null) {
        return self::createFragment($charset, $html);
    }


    /**
     * create an element
     *
     * @param  string|DOMNode $tag
     * @param  string $value
     * @param  array $attr
     * @param  bolt\dom\document $document
     *
     * @return bolt\dom\element
     */
    public function element($tag, $value = null, $attr = null, dom\document $document = null) {
        return self::createElement($tag, $value, $attr, $document);
    }


    /**
     * @see self::document
     * @return bolt\dom\document
     */
    public static function createDocument($charset = null, $html = null) {
        return new dom\document($charset, $html);
    }


    /**
     * @see self::fragment
     * @return bolt\dom\fragment
     */
    public static function createFragment($charset = null, $html = null) {
        return new dom\fragment($charset, $html);
    }


    /**
     * @see self::element
     * @return bolt\dom\element
     */
    public static function createElement($tag, $value = null, $attr = [], dom\document $document = null) {
        if (is_string($tag) && ($class = "bolt\\dom\\element\\$tag") && class_exists($class, true)) {
            return new $class(null, $value, $attr, $document);
        }
        return new dom\element($tag, $value, $attr, $document);
    }

}

<?php

namespace bolt\render;
use \b;

use Handlebars\Handlebars as HBR,
    Handlebars\Template;

/**
 * handlare renderer
 */
class handlebars extends base {

    /**
     * @var Handlebars\Handlebars
     */
    private $_instance;


    /**
     * @see  bolt\render\base::canCompile
     */
    public static function canCompile() {
        return true;
    }

    public function getDelimiters() {
        return b::param('delimiters', '<% %>', $this->config);
    }

    /**
     * Constructor
     */
    protected function getInstance() {
        if (!$this->_instance) {            
            $this->_instance = new HBR([
                    'delimiters' => $this->getDelimiters(),
                ]);
            $this->_instance->addHelper('=', function($template, $context, $args, $source) {
                $ctx = $context->get('context');
                $func = function($args) {
                    return eval('return '.trim($args, '; ').';');
                };
                return call_user_func($func->bindto($ctx, $ctx), $args);
            });
        }
        return $this->_instance;
    }


    /**
     * compile to freezable class
     */
    public function compile($str) {
        $i = $this->getInstance();
        $tokens = $i->getTokenizer()->scan($str, $this->getDelimiters());
        $tree = $i->getParser()->parse($tokens);

        return [
            'tokens' => $tokens,
            'tree' => $tree
        ];
    }


    /**
     * render a handlebar template
     *
     * @param string $str handlebar template
     * @param array $vars
     *
     * @return string
     */
    public function render($str, array $vars = []) {
        return $this->getInstance()->render($str, $vars);
    }


    /**
     * render a compiled template
     * 
     * @param  array $compiled
     * @param  array $vars
     * 
     * @return string
     */
    public function renderCompiled($compiled, array $vars = []) {
        $t = new Template($this->getInstance(), $compiled['tree'], $compiled['tokens']);
        return $t->render($vars);
    }

}
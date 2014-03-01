<?php

namespace bolt\render;
use \b;

use Handlebars\Handlebars as HBR;

/**
 * handlare renderer
 */
class handlebars extends base {

    /**
     * @var Handlebars\Handlebars
     */
    private $_instance;

    public static function canCompile() {
        return true;
    }

    /**
     * Constructor
     */
    protected function getInstance() {
        if (!$this->_instance) {
            $this->_instance = new HBR([
                    'delimiters' => "<% %>",
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
        $tokens = $i->getTokenizer()->scan($str, '<% %>');
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
    public function render($str, $vars = []) {
        if (is_array($str)) {
            var_dump($str); die;
        }

        return $this->getInstance()->render($str, $vars);
    }


}
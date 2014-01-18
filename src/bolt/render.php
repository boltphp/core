<?php

namespace bolt;
use \b;

class render {

    public static $engines = [];
    public static $instance = [];

    public static function setEngine($ext, $ref) {
        if (is_string($ref)) { $ref = b::getReflectionClass($ref); }
        self::$engines[$ext] = $ref;
    }

    public static function hasEngine($ext) {
        return array_key_exists($ext, self::$engines);
    }

    public static function getEngine($ext) {
        if (!array_key_exists($ext, self::$instance)) {
            $class = self::$engines[$ext]->name;
            self::$instance[$ext] = new $class;
        }
        return self::$instance[$ext];
    }

    public static function string($str, $config) {
        $str = $config['engine']->render($str, $config);

        return $str;
    }

    public static function file($file, $config) {
        // get the file
        $name = pathinfo($file)['basename'];
        $ext = explode('.', $name); array_shift($ext);

        // get the file
        $content = file_get_contents($file);

        // loop through each extension and
        foreach ($ext as $name) {
            if (self::hasEngine($name)) {
                $content = self::string($content, [
                        'engine' => self::getEngine($name),
                        'vars' => $config['vars'],
                        'context' => $config['context']
                    ]);
            }
        }

        return $content;
    }

}
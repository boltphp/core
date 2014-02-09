<?php

require "../vendor/autoload.php";

use Sami\Sami;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in(__DIR__.'/../src')
;

return new Sami($iterator, array(
    'title'               => 'Bolt Dir',
    'theme'               => 'enhanced',
    'build_dir'           => __DIR__.'/api/',
    'cache_dir'           => __DIR__.'/api/cache',
    'include_parent_data' => false,
    'default_opened_level' => 2
));
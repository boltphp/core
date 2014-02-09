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
    'build_dir'           => __DIR__.'/../build/zf2',
    'cache_dir'           => __DIR__.'/../cache/zf2',
    'include_parent_data' => false,
));
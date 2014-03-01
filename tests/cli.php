#!/usr/bin/php
<?php

require_once __DIR__."/../vendor/autoload.php";
require_once __DIR__."/../src/bolt.php";

$app = bolt::init();

$app->plug('cli', 'bolt\cli');

bolt\client::bind($app);

$code = $app->run();

exit($code);
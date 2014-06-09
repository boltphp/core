<?php
/**
 * bolt.php
 *
 * @copyright  2010 - 2014
 * @author     Travis Kuhl (travis@kuhl.co)
 * @link       http://bolthq.com
 * @license    http://opensource.org/licenses/Apache-2.0 Apache 2.0
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

Phar::mapPhar('bolt.phar');

// define our prefixes
$prefix = "phar://bolt.phar";
$boltPrefix = "$prefix/src";
$vendorPrefix = "$prefix/vendor";

// class loader
require_once "$vendorPrefix/symfony/class-loader/Symfony/Component/ClassLoader/ClassLoader.php";

// include universal class loader
$classLoader = new Symfony\Component\ClassLoader\ClassLoader();
$classLoader->addPrefixes(array(
    'bolt\\' => $boltPrefix,
    'Symfony\\Component\\Yaml\\' => $vendorPrefix . '/symfony/yaml',
    'Symfony\\Component\\Routing\\' => $vendorPrefix . '/symfony/routing',
    'Symfony\\Component\\PropertyAccess\\' => $vendorPrefix . '/symfony/property-access',
    'Symfony\\Component\\Process\\' => $vendorPrefix . '/symfony/process',
    'Symfony\\Component\\HttpFoundation\\' => $vendorPrefix . '/symfony/http-foundation',
    'Symfony\\Component\\Finder\\' => $vendorPrefix . '/symfony/finder',
    'Symfony\\Component\\Filesystem\\' => $vendorPrefix . '/symfony/filesystem',
    'Symfony\\Component\\EventDispatcher\\' => $vendorPrefix . '/symfony/event-dispatcher',
    'Symfony\\Component\\DomCrawler\\' => $vendorPrefix . '/symfony/dom-crawler',
    'Symfony\\Component\\CssSelector\\' => $vendorPrefix . '/symfony/css-selector',
    'Symfony\\Component\\Console\\' => $vendorPrefix . '/symfony/console',
    'Symfony\\Component\\ClassLoader\\' => $vendorPrefix . '/symfony/class-loader',
    'Seld\\JsonLint' => $vendorPrefix . '/seld/jsonlint/src',
    'Sami' => $vendorPrefix . '/sami/sami',
    'Pimple' => $vendorPrefix . '/pimple/pimple/lib',
    'Patchwork' => $vendorPrefix . '/patchwork/utf8/class',
    'PHPParser' => $vendorPrefix . '/nikic/php-parser/lib',
    'Normalizer' => $vendorPrefix . '/patchwork/utf8/class',
    'Michelf' => $vendorPrefix . '/michelf/php-markdown',
    'JsonSchema' => $vendorPrefix . '/justinrainbow/json-schema/src',
    'Handlebars' => $vendorPrefix . '/boltphp/handlebars.php/src',
    'Guzzle\\Tests' => $vendorPrefix . '/guzzle/guzzle/tests',
    'Guzzle' => $vendorPrefix . '/guzzle/guzzle/src',
    'Doctrine\\ORM\\' => $vendorPrefix . '/doctrine/orm/lib',
    'Doctrine\\DBAL\\' => $vendorPrefix . '/doctrine/dbal/lib',
    'Doctrine\\Common\\Lexer\\' => $vendorPrefix . '/doctrine/lexer/lib',
    'Doctrine\\Common\\Inflector\\' => $vendorPrefix . '/doctrine/inflector/lib',
    'Doctrine\\Common\\Collections\\' => $vendorPrefix . '/doctrine/collections/lib',
    'Doctrine\\Common\\Cache\\' => $vendorPrefix . '/doctrine/cache/lib',
    'Doctrine\\Common\\Annotations\\' => $vendorPrefix . '/doctrine/annotations/lib',
    'Doctrine\\Common\\' => $vendorPrefix . '/doctrine/common/lib',
    'Assetic' => $vendorPrefix . '/kriswallsmith/assetic/src',
    'Psr\\Log\\' => $vendorPrefix . '/psr/log',
    'Monolog\\' => $vendorPrefix . '/monolog/monolog/src',
    'HTML5' => $vendorPrefix . '/masterminds/html5/src',
));
$classLoader->register();

// we define this by default
// to include our b shortcut
require "$boltPrefix/bolt.php";

__HALT_COMPILER();

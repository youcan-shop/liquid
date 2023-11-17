<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

require __DIR__ . '/../vendor/autoload.php';

use YouCan\Liquid\Liquid;
use YouCan\Liquid\Template;

Liquid::set('INCLUDE_SUFFIX', 'tpl');
Liquid::set('INCLUDE_PREFIX', '');

$liquid = new Template();
$liquid->parse('{{ hello }} {{ goback }}');

echo $liquid->render(['hello' => 'hello world', 'goback' => '<a href=".">index</a>']);

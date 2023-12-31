<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace YouCan\Liquid\Tag;

use YouCan\Liquid\TestCase;

class TagBlockTest extends TestCase
{
    /**
     */
    public function testSyntaxError()
    {
        $this->expectException(\YouCan\Liquid\Exception\ParseException::class);

        $this->assertTemplateResult('', '{% block %}');
    }

    public function testCreateBlock()
    {
        $this->assertTemplateResult('block content', '{% block foo %}block content{% endblock %}');
    }
}

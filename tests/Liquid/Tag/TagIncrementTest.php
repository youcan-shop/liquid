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

class TagIncrementTest extends TestCase
{
    /**
     */
    public function testSyntaxError()
    {
        $this->expectException(\YouCan\Liquid\Exception\ParseException::class);

        $this->assertTemplateResult('', '{% increment %}');
    }

    public function testIncrementNonExistingVariable()
    {
        $this->assertTemplateResult('01', '{% increment no_such_var %}{{ no_such_var }}');
        $this->assertTemplateResult('0 1 2', '{% increment var %} {% increment var %} {{ var }}');
    }

    public function testIncrementSeparateFromAssign()
    {
        $this->assertTemplateResult('0141', '{% assign var = 41 %}{% increment var %}{% increment var %}{{ var }}');
    }

    public function testIncrementNestedVariable()
    {
        $this->assertTemplateResult('041', '{% for var in vars %}{% increment var %}{{ var }}{% endfor %}', ['vars' => [41]]);
    }

    public function testIncrementSharesDecrementCounter()
    {
        $this->assertTemplateResult('-1 -2 -2 -1', '{% decrement var %} {% decrement var %} {% increment var %} {{ var }}');
    }
}
